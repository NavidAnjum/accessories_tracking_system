/*
 * ATS "Email PI (Outlook)" — shared front-end.
 *
 * Opens the desktop Outlook helper (custom protocol atsmail://) which renders the
 * CURRENT PI print page to PDF and opens a new Outlook email with it attached.
 * Requires the per-PC helper to be installed (see /outlook-helper/README.txt).
 *
 * The helper renders whatever URL we pass. We hand it THIS page's print URL with
 * &embed=1 so the print chrome is hidden and it renders like the PDF.
 */
(function () {
    // Build a safe file name from the PI number (matches the *FileName helpers).
    function safeName(s) {
        return String(s || 'PI')
            .replace(/[\/\\:*?"<>|]+/g, '-')
            .replace(/\s+/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '') || 'PI';
    }

    // Best-effort read of the PI number + customer already shown on the print page.
    function currentPiMeta() {
        const num =
            document.getElementById('spiNum')?.textContent ||
            document.getElementById('mpiNum')?.textContent ||
            document.getElementById('mspiNum')?.textContent ||
            (document.title || 'PI');
        // customer sits in the TO block on the print pages
        const toEl = document.getElementById('spiTo') || document.getElementById('mpiTo') || document.getElementById('mspiTo');
        let customer = '';
        if (toEl) customer = (toEl.textContent || '').split('\n')[0].trim();
        return { num: (num || 'PI').trim(), customer: customer.trim() };
    }

    // Render URL for THIS PI = current page URL, forced into embed (no chrome) mode.
    function currentPrintUrl() {
        const u = new URL(window.location.href);
        const orderId = (window.getCurrentOrderId && window.getCurrentOrderId())
            || u.searchParams.get('order_id')
            || sessionStorage.getItem('ats_current_order_id')
            || '';
        if (orderId) u.searchParams.set('order_id', orderId);
        const singleSelection = document.getElementById('spiPoSel')?.value || '';
        if (singleSelection) u.searchParams.set('pi_sel', singleSelection);
        u.searchParams.set('embed', '1');   // hide app chrome so it prints clean
        u.searchParams.set('email_render', '1');
        u.searchParams.delete('excel');     // never the excel variant
        return u.toString();
    }

    async function trackCommercialPdfCreation() {
        const orderId = (window.getCurrentOrderId && window.getCurrentOrderId())
            || new URLSearchParams(window.location.search).get('order_id')
            || '';
        if (!orderId) return;
        try {
            await fetch((window.APP_BASE || '') + '/api/pi_pdf_created.php', {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({order_id: orderId})
            });
        } catch (_) { /* timestamp tracking must never block the document */ }
    }

    window.atsPrintPi = async function atsPrintPi() {
        const content = document.querySelector('#spiContent, #mspiContent, #mpiContent');
        if (!content || window.getComputedStyle(content).display === 'none') {
            alert('The PI is still loading. Please wait until the invoice is visible, then try again.');
            return;
        }
        const pendingImages = Array.from(content.querySelectorAll('img')).filter(img => !img.complete);
        if (pendingImages.length) {
            await Promise.race([
                Promise.all(pendingImages.map(img => new Promise(resolve => {
                    img.addEventListener('load', resolve, {once:true});
                    img.addEventListener('error', resolve, {once:true});
                }))),
                new Promise(resolve => setTimeout(resolve, 3000))
            ]);
        }
        if (document.fonts?.ready) {
            await Promise.race([document.fonts.ready, new Promise(resolve => setTimeout(resolve, 2000))]);
        }
        await trackCommercialPdfCreation();
        document.documentElement.classList.add('ats-print-layout');
        if (typeof window.atsRerenderPiForLayout === 'function') {
            window.atsRerenderPiForLayout();
            await new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve)));
        }
        window.print();
    };

    window.addEventListener('afterprint', function () {
        document.documentElement.classList.remove('ats-print-layout');
        if (typeof window.atsRerenderPiForLayout === 'function') {
            requestAnimationFrame(() => window.atsRerenderPiForLayout());
        }
    });

    window.emailThisPi = async function emailThisPi() {
        const content = document.querySelector('#spiContent, #mspiContent, #mpiContent');
        if (!content || window.getComputedStyle(content).display === 'none') {
            alert('The PI is still loading. Please wait until the invoice is visible, then try again.');
            return;
        }
        const meta = currentPiMeta();
        const fileNm  = safeName((meta.customer ? meta.customer + '-' : '') + meta.num);
        const subject = 'Proforma Invoice ' + meta.num + (meta.customer ? ' — ' + meta.customer : '');
        const body    = 'Dear Sir/Madam,\r\n\r\nPlease find attached our Proforma Invoice ' +
                        meta.num + '.\r\n\r\nBest regards,\r\nZaber & Zubair Accessories Ltd.';

        let url;
        try {
            const response = await fetch((window.APP_BASE || '') + '/api/pi_render_token.php', { method: 'POST' });
            const data = await response.json();
            if (!response.ok || !data.token) throw new Error(data.error || 'Could not create render token.');
            const renderUrl = new URL(currentPrintUrl());
            renderUrl.searchParams.set('render_token', data.token);
            url = renderUrl.toString();
        } catch (_) {
            alert('Could not prepare the PI for email. Please try again.');
            return;
        }
        await trackCommercialPdfCreation();
        const link = 'atsmail://open'
            + '?url='     + encodeURIComponent(url)
            + '&subject=' + encodeURIComponent(subject)
            + '&body='    + encodeURIComponent(body)
            + '&file='    + encodeURIComponent(fileNm)
            + '&to=';     // recipient left blank — the user fills it in Outlook

        // Chrome will show its own "Open Windows Command Processor?" prompt for the
        // custom protocol — we can't change that text, so show a friendly toast that
        // explains it's needed to open Outlook with the PI attached.
        showEmailHint();

        // Fire the protocol. If the helper isn't installed, nothing opens — we tell them.
        let launched = false;
        const onBlur = () => { launched = true; };
        window.addEventListener('blur', onBlur, { once: true });
        try {
            window.location.href = link;
        } catch (_) {}
        setTimeout(() => {
            window.removeEventListener('blur', onBlur);
            if (!launched) {
                alert('Could not open Outlook.\n\nThe "Email PI" helper may not be installed on this PC.\n' +
                      'Ask IT to run the one-time setup in /outlook-helper (README.txt),\n' +
                      'or use "Print / Save PDF" and attach it manually.');
            }
        }, 1500);
    };

    // Small non-blocking toast shown while Chrome's protocol prompt appears.
    function showEmailHint() {
        let t = document.getElementById('atsEmailHint');
        if (!t) {
            t = document.createElement('div');
            t.id = 'atsEmailHint';
            t.style.cssText = 'position:fixed;top:16px;left:50%;transform:translateX(-50%);z-index:100000;' +
                'background:#0f6cbd;color:#fff;padding:12px 18px;border-radius:10px;font-size:14px;font-weight:600;' +
                'box-shadow:0 8px 30px rgba(0,0,0,.25);max-width:520px;text-align:center;line-height:1.4;';
            document.body.appendChild(t);
        }
        t.innerHTML = '📧 Opening Outlook with the PI attached…<br>' +
            '<span style="font-weight:400;font-size:12.5px;">If a Windows prompt appears, click <b>Open</b> — it is needed to attach the PI and open your email.</span>';
        t.style.display = 'block';
        clearTimeout(t._hideTimer);
        t._hideTimer = setTimeout(() => { t.style.display = 'none'; }, 6000);
    }
})();

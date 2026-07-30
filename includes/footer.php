<?php // footer.php — shared scripts and modals ?>
    </main>

    </div>

    <!-- ── Signature Modal ─────────────────────────────────────────────────── -->
    <div class="modal-shell hidden" id="sigModal">
        <div style="background:#fff;border-radius:18px;width:min(500px,100%);overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.22);display:flex;flex-direction:column;">

            <!-- Header -->
            <div style="display:flex;align-items:center;gap:14px;padding:20px 22px 16px;border-bottom:1.5px solid #f1f5f9;">
                <div style="width:42px;height:42px;background:linear-gradient(135deg,#4f46e5,#818cf8);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                </div>
                <div style="flex:1;">
                    <div style="font-size:10px;font-weight:800;letter-spacing:.1em;color:#6366f1;text-transform:uppercase;margin-bottom:2px;">Signature</div>
                    <div style="font-size:17px;font-weight:700;color:#1e1e2e;">Add Signature</div>
                </div>
                <button type="button" id="sigCancelBtn" aria-label="Close"
                    style="width:32px;height:32px;border:none;background:#f1f5f9;border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#64748b;flex-shrink:0;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            <!-- Tabs -->
            <div style="display:flex;gap:8px;padding:16px 22px 0;">
                <button type="button" id="sigTabDraw"
                    style="display:flex;align-items:center;gap:6px;border:1.5px solid #4f46e5;background:#4f46e5;color:#fff;padding:8px 18px;font-size:13px;font-weight:600;border-radius:10px;cursor:pointer;box-shadow:0 3px 10px rgba(99,102,241,.3);">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                    Draw
                </button>
                <button type="button" id="sigTabUpload"
                    style="display:flex;align-items:center;gap:6px;border:1.5px solid #e2e8f0;background:#f8fafc;color:#64748b;padding:8px 18px;font-size:13px;font-weight:600;border-radius:10px;cursor:pointer;">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    Upload Image
                </button>
            </div>

            <!-- Draw panel -->
            <div id="sigPanelDraw" style="padding:16px 22px 0;">
                <div style="border:1.5px solid #e0e3ff;border-radius:12px;overflow:hidden;">
                    <canvas id="sigCanvas" width="480" height="160"
                        style="cursor:crosshair;touch-action:none;width:100%;height:160px;display:block;background:linear-gradient(rgba(99,102,241,.03) 1px,transparent 1px) 0 0/20px 20px,linear-gradient(90deg,rgba(99,102,241,.03) 1px,transparent 1px) 0 0/20px 20px,#fff;"></canvas>
                    <div style="text-align:center;font-size:11px;color:#cbd5e1;padding:6px 0;background:#f8faff;border-top:1px solid #eef0ff;letter-spacing:.04em;">Draw your signature above</div>
                </div>
                <div style="display:flex;justify-content:flex-end;margin-top:10px;">
                    <button type="button" id="sigClearBtn"
                        style="display:flex;align-items:center;gap:5px;border:1.5px solid #e2e8f0;background:#fff;padding:6px 14px;font-size:12px;font-weight:600;color:#64748b;border-radius:8px;cursor:pointer;">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                        Clear
                    </button>
                </div>
            </div>

            <!-- Upload panel -->
            <div id="sigPanelUpload" style="padding:16px 22px 0;display:none;">
                <input type="file" id="sigFileInput" accept="image/*" style="display:none;">
                <div id="sigUploadZone" onclick="document.getElementById('sigFileInput').click()"
                    style="border:2px dashed #c7d0ff;border-radius:12px;padding:30px 20px;text-align:center;cursor:pointer;background:#fafbff;transition:border-color .15s;">
                    <div style="width:52px;height:52px;background:#eef2ff;border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    </div>
                    <div class="sig-upload-text" style="font-size:14px;font-weight:600;color:#1e1e2e;margin-bottom:4px;">Click to browse or drag &amp; drop</div>
                    <div class="sig-upload-sub" style="font-size:12px;color:#94a3b8;">PNG, JPG, GIF accepted</div>
                    <img id="sigUploadPreview" style="max-width:100%;max-height:130px;margin-top:14px;display:none;border-radius:8px;box-shadow:0 2px 12px #0001;">
                    <div id="sigUploadLabel" style="margin-top:8px;font-size:12px;color:#4f46e5;font-weight:600;display:none;"></div>
                </div>
            </div>

            <!-- Footer -->
            <div style="display:flex;justify-content:flex-end;gap:10px;padding:18px 22px 22px;margin-top:16px;border-top:1.5px solid #f1f5f9;">
                <button type="button" id="sigCancelBtn2"
                    style="border:1.5px solid #e2e8f0;background:#fff;padding:10px 22px;font-size:13px;font-weight:600;color:#64748b;border-radius:10px;cursor:pointer;">
                    Cancel
                </button>
                <button type="button" id="sigConfirmBtn"
                    style="display:flex;align-items:center;gap:7px;border:none;background:linear-gradient(135deg,#4f46e5,#6366f1);padding:10px 22px;font-size:13px;font-weight:700;color:#fff;border-radius:10px;cursor:pointer;box-shadow:0 4px 14px rgba(99,102,241,.35);">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Apply Signature
                </button>
            </div>
        </div>
    </div>

    <div class="modal-shell hidden" id="buyerModal">
        <div class="modal-card">
            <div class="section-head modal-head">
                <div class="section-title">
                    <span class="section-tag">Buyer Setup</span>
                    <h2>Create Buyer</h2>
                </div>
            </div>
            <div class="form-grid">
                <div class="field span-12">
                    <label for="newBuyerCustomer">Customer <span style="font-weight:400;color:#888">(optional — leave blank if buyer has no customer)</span></label>
                    <input id="newBuyerCustomer" placeholder="e.g. LIZ Fashion Industry Ltd">
                </div>
                <div class="field span-4">
                    <label for="newBuyerCode">Buyer Code</label>
                    <input id="newBuyerCode" value="NEW-BUYER">
                </div>
                <div class="field span-8">
                    <label for="newBuyerName">Buyer Name</label>
                    <input id="newBuyerName" value="New Buyer Ltd">
                </div>
                <div class="field span-12">
                    <label for="newBuyerAddress">Address</label>
                    <textarea id="newBuyerAddress">Buyer address will be saved into the master table and used by the intake form.</textarea>
                </div>
            </div>
            <div class="page-actions">
                <div class="page-actions-left">
                    <button type="button" class="ghost-btn" id="closeBuyerModal">Cancel</button>
                </div>
                <div class="page-actions-right">
                    <button type="button" class="primary-btn" id="saveBuyerModal">Save Buyer</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-shell hidden" id="itemModal">
        <div class="modal-card">
            <div class="section-head modal-head">
                <div class="section-title">
                    <span class="section-tag">Item Setup</span>
                    <h2>Add Item To Master</h2>
                </div>
            </div>
            <div class="form-grid">
                <div class="field span-6">
                    <label for="newItemProductLine">Product Line</label>
                    <select id="newItemProductLine">
                        <option value="">— Select Product Line —</option>
                        <option>Binding</option>
                        <option>Carton</option>
                        <option>Drawstring</option>
                        <option>Elastic</option>
                        <option>Gum Tape</option>
                        <option>Offset</option>
                        <option>PVC</option>
                        <option>Paper Tube</option>
                        <option>Pick &amp; Pack</option>
                        <option>Poly</option>
                        <option>Printed Label</option>
                        <option>Sewing Thread</option>
                        <option>Store</option>
                        <option>Twill Tape</option>
                    </select>
                </div>
                <div class="field span-6">
                    <label for="newItemName">Item Name</label>
                    <input id="newItemName" list="itemNameList" placeholder="Type or select item name" autocomplete="off">
                    <datalist id="itemNameList"></datalist>
                </div>
                <div class="field span-4">
                    <label for="newItemPrice">Base Price</label>
                    <input id="newItemPrice" type="number" step="0.0001" min="0" placeholder="0.0000">
                </div>
                <!-- Paper Combination — visible for Carton; selecting it auto-fills Grade -->
                <div class="field span-8 hidden" id="newItemCombWrap">
                    <label for="newItemComb">Paper Combination</label>
                    <select id="newItemComb">
                        <option value="">— Select Combination —</option>
                    </select>
                </div>
                <!-- Grade: auto-filled (Carton) or free-text (others) — always last -->
                <div class="field span-6" id="newItemGradeWrap">
                    <label for="newItemGrade">Grade</label>
                    <select id="newItemGrade" class="hidden" style="background:#f8fafc;">
                        <option value="">— auto-filled from combination —</option>
                    </select>
                    <input id="newItemGradeText" placeholder="e.g. Grade 3 (optional)" style="display:none;">
                </div>
            </div>
            <div class="page-actions">
                <div class="page-actions-left">
                    <button type="button" class="ghost-btn" id="closeItemModal">Cancel</button>
                </div>
                <div class="page-actions-right">
                    <button type="button" class="primary-btn" id="saveItemModal">Save Item</button>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= BASE_PATH ?>/item-data.js"></script>
    <script src="<?= BASE_PATH ?>/js/common.js"></script>
    <script src="<?= BASE_PATH ?>/script.js"></script>
    <script src="<?= BASE_PATH ?>/js/html2canvas.min.js"></script>

<script>
// ── Order ID Bar — global functions ────────────────────────────────────────
(function () {
    const OID_KEY = 'ats_current_order_id';

    function setOrderDisplay(orderId, order) {
        const display = document.getElementById('oidDisplay');
        const statusRow = document.getElementById('oidStatusRow');
        if (!display) return;

        display.textContent = orderId;

        if (order && statusRow) {
            document.getElementById('oidCustomer').textContent = order.customer_name || 'No customer yet';
            document.getElementById('oidStep').textContent     = 'Step: ' + (order.current_step || '—').replace(/-/g,' ').replace(/\b\w/g,c=>c.toUpperCase());
            document.getElementById('oidDate').textContent     = order.created_at ? new Date(order.created_at).toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'}) : '';
            statusRow.style.display = 'flex';
        }

        // Store in sessionStorage so tabs share it
        sessionStorage.setItem(OID_KEY, orderId);
    }

    function oidSearch() {
        const q = (document.getElementById('oidInput')?.value || '').trim();
        if (!q) return;
        loadOrderById(q, true);
    }

    function loadOrderById(id, isManual) {
        fetch(window.APP_BASE + '/api/order_lookup.php?id=' + encodeURIComponent(id))
            .then(r => r.json())
            .then(res => {
                if (!res.found) {
                    sessionStorage.removeItem(OID_KEY);
                    const display = document.getElementById('oidDisplay');
                    if (display) display.textContent = 'No order loaded';
                    const statusRow = document.getElementById('oidStatusRow');
                    if (statusRow) statusRow.style.display = 'none';
                    if (isManual) alert('Order not found: ' + id);
                    return;
                }
                setOrderDisplay(res.order.order_id, res.order);
                if (document.getElementById('oidInput'))
                    document.getElementById('oidInput').value = '';

                // Let the current page handle its own data
                if (typeof window.onOrderLoad === 'function') {
                    window.onOrderLoad(res);
                }
            })
            .catch(() => alert('Could not reach server.'));
    }

    function oidNewOrder() {
        if (sessionStorage.getItem(OID_KEY)) {
            if (!confirm('Start a new order? The current order ID will be cleared from this session.')) return;
        }
        fetch(window.APP_BASE + '/api/order_lookup.php', { method: 'POST' })
            .then(r => r.json())
            .then(res => {
                if (!res.ok) { alert('Failed to create order.'); return; }
                setOrderDisplay(res.order_id, null);
                if (typeof window.onNewOrder === 'function') {
                    window.onNewOrder(res.order_id);
                }
                alert('✓ New order created: ' + res.order_id);
            })
            .catch(() => alert('Could not reach server.'));
    }

    // Restore from session on page load, or auto-load most recent order
    window.addEventListener('DOMContentLoaded', function () {
        const stored = sessionStorage.getItem(OID_KEY);
        if (stored && document.getElementById('oidDisplay')) {
            loadOrderById(stored);
        } else if (!stored && document.getElementById('oidDisplay')) {
            // Auto-load the most recently saved order for this user
            fetch(window.APP_BASE + '/api/orders.php?last=1')
                .then(r => r.json())
                .then(row => { if (row?.order_id) loadOrderById(row.order_id); })
                .catch(() => {});
        }

        // Next-page buttons: auto-save current page data then navigate
        document.querySelectorAll('.js-next-page').forEach(btn => {
            btn.addEventListener('click', async function () {
                const next     = this.dataset.nextPage;
                if (!next) return;
                const orderId  = window.getCurrentOrderId();
                const pageName = document.body.dataset.page;
                // Save if there's an order and a page identity (skip display-only pages like packing/delivery)
                if (orderId && pageName) {
                    const skipSave = ['packing','delivery','truck','origin','beneficiary'];
                    if (!skipSave.includes(pageName)) {
                        try {
                            await fetch(window.APP_BASE + '/api/save_page.php', {
                                method:  'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body:    JSON.stringify({ order_id: orderId, page_name: pageName, ...collectPageFields() }),
                            });
                        } catch (_) {}
                    }
                }
                window.location.href = window.APP_BASE + '/pages/' + next + '.php';
            });
        });

        // Handle prev-page navigation buttons (data-prev-page="costing-review" etc.)
        document.querySelectorAll('.js-prev-page').forEach(btn => {
            btn.addEventListener('click', function () {
                const prev = this.dataset.prevPage;
                if (prev) window.location.href = window.APP_BASE + '/pages/' + prev + '.php';
            });
        });
    });

    // Expose globally
    window.oidSearch    = oidSearch;
    window.loadOrderById = loadOrderById;
    window.getCurrentOrderId = () => sessionStorage.getItem(OID_KEY) || '';
    window.oidNewOrder  = function () {
        if (sessionStorage.getItem(OID_KEY)) {
            if (!confirm('Start a new order? The current order ID will be cleared from this session.')) return;
        }
        fetch(window.APP_BASE + '/api/order_lookup.php', { method: 'POST' })
            .then(r => r.json())
            .then(res => {
                if (!res.ok) { alert('Failed to create order.'); return; }

                // Set sessionStorage and display immediately (synchronous) so step
                // navigation works even if the subsequent loadOrderById fetch is slow
                setOrderDisplay(res.order_id, null);
                const statusRow = document.getElementById('oidStatusRow');
                if (statusRow) {
                    document.getElementById('oidCustomer').textContent = '';
                    document.getElementById('oidStep').textContent = 'Step: Marketing Intake';
                    document.getElementById('oidDate').textContent = '';
                    statusRow.style.display = 'flex';
                }

                if (typeof window.onNewOrder === 'function') {
                    window.onNewOrder(res.order_id);
                }
                loadOrderById(res.order_id);
                alert('New order created: ' + res.order_id);
            })
            .catch(() => alert('Could not reach server.'));
    };

    // ── Cache PI fields and apply data-pi-bind on any page ───────────────────
    function applyPiBindings() {
        const vals = {
            buyerAddress:  sessionStorage.getItem('ats_buyer_address')  || '',
            consigneeBank: sessionStorage.getItem('ats_consignee_bank') || '',
            advisingBank:  sessionStorage.getItem('ats_advising_bank')  || '',
        };
        document.querySelectorAll('[data-pi-bind]').forEach(el => {
            const val = vals[el.dataset.piBind];
            if (!val) return;
            if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
                if (!el.value) el.value = val;
            } else {
                if (!el.textContent.trim() || el.textContent.trim() === '—') el.textContent = val;
            }
        });
    }

    // Apply on page load from cached session (before order re-fetches)
    window.addEventListener('DOMContentLoaded', applyPiBindings);

    // ── Auto-restore page data after order load ──────────────────────────────
    const _origOnOrderLoad = window.onOrderLoad;
    window.onOrderLoad = function(res) {
        // Cache PI fields from the sales page snapshot
        const salesSnap = res.pages?.sales || {};
        if (salesSnap.buyerAddress)  sessionStorage.setItem('ats_buyer_address',  salesSnap.buyerAddress);
        if (salesSnap.consigneeBank) sessionStorage.setItem('ats_consignee_bank', salesSnap.consigneeBank);
        if (salesSnap.advisingBank)  sessionStorage.setItem('ats_advising_bank',  salesSnap.advisingBank);
        applyPiBindings();

        if (typeof _origOnOrderLoad === 'function') _origOnOrderLoad(res);

        document.dispatchEvent(new CustomEvent('ats:orderloaded'));

        const pageName = document.body.dataset.page;
        if (pageName && res.pages && res.pages[pageName]) {
            restorePageFields(res.pages[pageName]);
        }

        // Inject shared order-items panel on doc pages that don't have their own
        const _sipSkip = ['marketing-intake','costing-review','sales','marketing','dashboard','login','po-overview','po-status'];
        if (!_sipSkip.includes(pageName)) _renderSharedItemsPanel(res);
    };
})();

// ── Shared collapsible Order Items panel (shown on all doc pages) ────────────
function atsResolveDisplayPos(res) {
    const sales  = res.pages?.sales || {};
    const mkt    = res.pages?.['marketing-intake'] || {};
    const allPis = res.pis || [];
    const individuals = allPis.filter(p => !p.is_master);
    const masters = allPis.filter(p => p.is_master);
    const piType = sales.piType || '';
    const selectedNums = new Set((sales.selectedPiNumbers || []).filter(Boolean));
    const labelMap = { single: 'Single PI', summary: 'Summary PI', master: 'Master PI' };

    if (Array.isArray(sales.pos) && sales.pos.length) {
        return {
            pos: sales.pos,
            label: labelMap[piType] || 'Sales Items',
            customer: sales.customer || '',
            piNum: sales.piNum || '',
            piDate: sales.piDate || ''
        };
    }

    if (piType === 'master' && Array.isArray(sales.masterPiSelection) && sales.masterPiSelection.length) {
        const pos = sales.masterPiSelection.map((group, idx) => ({
            poNum: group.poNum || ('Selected ' + (idx + 1)),
            buyer: group.sharedBuyer || sales.buyer || '',
            items: group.items || []
        }));
        return {
            pos,
            label: 'Master PI',
            customer: sales.customer || '',
            piNum: sales.piNum || '',
            piDate: sales.piDate || ''
        };
    }

    if (piType === 'single') {
        const chosen = individuals.find(pi => pi.pi_number === sales.piNum) || individuals[0] || masters[0];
        if (chosen?.pos?.length) {
            return {
                pos: chosen.pos,
                label: 'Single PI',
                customer: chosen.customer || sales.customer || '',
                piNum: chosen.pi_number || sales.piNum || '',
                piDate: chosen.pi_date || sales.piDate || ''
            };
        }
    }

    if (piType === 'summary') {
        const chosen = selectedNums.size ? individuals.filter(pi => selectedNums.has(pi.pi_number)) : individuals;
        const pos = chosen.flatMap(pi => pi.pos || []);
        if (pos.length) {
            return {
                pos,
                label: 'Summary PI',
                customer: chosen[0]?.customer || sales.customer || '',
                piNum: sales.piNum || '',
                piDate: sales.piDate || ''
            };
        }
    }

    const bestPi = masters[0] || individuals[0];
    if (bestPi?.pos?.length) {
        return {
            pos: bestPi.pos,
            label: bestPi.is_master ? 'Master PI' : 'PI',
            customer: bestPi.customer || '',
            piNum: bestPi.pi_number || '',
            piDate: bestPi.pi_date || ''
        };
    }

    if (mkt?.pos?.length) {
        return {
            pos: mkt.pos,
            label: 'Marketing Intake Items',
            customer: mkt.customer || '',
            piNum: '',
            piDate: ''
        };
    }

    return { pos: [], label: '', customer: '', piNum: '', piDate: '' };
}
window.atsResolveDisplayPos = atsResolveDisplayPos;

function _renderSharedItemsPanel(res) {
    document.getElementById('sharedOrderItemsPanel')?.remove();

    const sales  = res.pages?.sales;
    const mkt    = res.pages?.['marketing-intake'];
    const allPis = res.pis || [];

    // Priority: Master PI → any saved PI → sales data → marketing intake
    const masterPi     = allPis.find(p => p.is_master);
    const standalonePi = allPis.find(p => !p.is_master);
    const bestPi       = masterPi || standalonePi;

    let displayPos = [];
    let label      = '';
    let customer   = '';
    let piNum      = '';

    if (bestPi?.pos?.length) {
        displayPos = bestPi.pos;
        label      = bestPi.is_master ? 'Master PI' : 'PI';
        piNum      = bestPi.pi_number || '';
        customer   = bestPi.customer  || '';
    } else if (sales?.pos?.length) {
        displayPos = sales.pos;
        label      = 'Sales Items';
        customer   = sales.customer || '';
    } else if (mkt?.pos?.length) {
        displayPos = mkt.pos;
        label      = 'Marketing Intake Items';
        customer   = mkt.customer || '';
    }

    if (!displayPos.length) return;

    const anchor = document.querySelector('section.form-card, .form-card');
    if (!anchor) return;

    const esc = v => String(v||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');

    const poHtml = displayPos.map((po, idx) => {
        // PI pos have items[], sales pos have items[], mkt pos have rows[]
        const items = po.items || po.rows || [];
        const poNum = po.poNum || po.po_number || '—';
        const buyer = po.buyer || po.endBuyer || customer;
        const pid   = 'sip_po_' + idx;

        const rowsHtml = items.map((item, i) => {
            // PI/sales item: {desc, ply, qty, price, total}
            // Intake row:    {itemName, prodLine, artSize, qty, unitPrc}
            const desc  = item.desc || item.itemName || '—';
            const sub   = item.ply  || item.prodLine || '';
            const size  = item.artSize || '';
            const qty   = parseFloat(item.qty  || 0);
            const price = parseFloat(item.price || item.unitPrc || 0);
            const total = parseFloat(item.total || (qty * price)) || 0;
            return `<tr>
                <td style="color:#64748b;text-align:center;padding:5px 8px;">${i+1}</td>
                <td style="padding:5px 8px;">${esc(desc)}${sub?'<br><span style="font-size:10px;color:#94a3b8;">'+esc(sub)+'</span>':''}</td>
                <td style="text-align:center;padding:5px 8px;">${esc(size||'—')}</td>
                <td style="text-align:right;padding:5px 8px;">${qty.toLocaleString()}</td>
                <td style="text-align:right;padding:5px 8px;">$${price.toFixed(4)}</td>
                <td style="text-align:right;padding:5px 8px;font-weight:700;color:#4f46e5;">$${total.toFixed(2)}</td>
            </tr>`;
        }).join('');

        return `
        <div style="border:1.5px solid #dbe4ff;border-radius:10px;margin-bottom:8px;overflow:hidden;">
            <div onclick="var b=document.getElementById('${pid}'),open=b.style.display!=='none';b.style.display=open?'none':'block';this.querySelector('.sip-arr').textContent=open?'▼':'▲';"
                 style="display:flex;align-items:center;gap:10px;padding:9px 14px;background:#f1f5ff;cursor:pointer;user-select:none;">
                <span style="background:#6366f1;color:#fff;font-size:10px;font-weight:800;padding:2px 8px;border-radius:999px;">PO ${idx+1}</span>
                <strong style="font-size:13px;color:#1e1e2e;">${esc(poNum)}</strong>
                <span style="font-size:12px;color:#64748b;flex:1;">${esc(buyer)}</span>
                <span style="font-size:12px;color:#6366f1;font-weight:700;">${items.length} item${items.length!==1?'s':''}</span>
                <span class="sip-arr" style="font-size:12px;color:#94a3b8;">▼</span>
            </div>
            <div id="${pid}" style="display:none;">
                <table style="width:100%;border-collapse:collapse;font-size:12px;">
                    <thead><tr style="background:#f8fafc;border-bottom:1.5px solid #e8eaff;">
                        <th style="padding:6px 8px;text-align:center;width:30px;color:#64748b;font-weight:700;">#</th>
                        <th style="padding:6px 8px;text-align:left;color:#64748b;font-weight:700;">Item / Description</th>
                        <th style="padding:6px 8px;text-align:center;color:#64748b;font-weight:700;">Art / Size</th>
                        <th style="padding:6px 8px;text-align:right;color:#64748b;font-weight:700;">Qty</th>
                        <th style="padding:6px 8px;text-align:right;color:#64748b;font-weight:700;">Price</th>
                        <th style="padding:6px 8px;text-align:right;color:#64748b;font-weight:700;">Amount</th>
                    </tr></thead>
                    <tbody>${rowsHtml||'<tr><td colspan="6" style="text-align:center;padding:10px;color:#94a3b8;">No items</td></tr>'}</tbody>
                </table>
            </div>
        </div>`;
    }).join('');

    const panel = document.createElement('div');
    panel.id = 'sharedOrderItemsPanel';
    panel.style.cssText = 'background:#fff;border:1.5px solid #e0e3ff;border-radius:14px;overflow:hidden;margin-bottom:18px;';
    panel.innerHTML = `
        <div id="sipHdr" onclick="var b=document.getElementById('sipBody'),open=b.style.display!=='none';b.style.display=open?'none':'block';document.getElementById('sipArrow').textContent=open?'▼':'▲';"
             style="display:flex;align-items:center;justify-content:space-between;padding:12px 18px;background:linear-gradient(135deg,#1e1e2e,#2d2d44);cursor:pointer;user-select:none;">
            <span style="color:#fff;font-weight:800;font-size:14px;">📦 ${esc(label)}${piNum?' · '+esc(piNum):''} — ${displayPos.length} PO(s)</span>
            <span id="sipArrow" style="color:#94a3b8;font-size:13px;">▼</span>
        </div>
        <div id="sipBody" style="display:none;padding:14px 16px;">${poHtml}</div>`;

    anchor.parentNode.insertBefore(panel, anchor);
}

// ── Universal page save / restore ───────────────────────────────────────────
function collectPageFields() {
    const data = {};
    document.querySelectorAll('[id]').forEach(el => {
        if (!el.id) return;
        if (el.tagName === 'INPUT') {
            if (el.type === 'checkbox') data[el.id] = el.checked;
            else if (el.type !== 'button' && el.type !== 'submit') data[el.id] = el.value;
        } else if (el.tagName === 'TEXTAREA' || el.tagName === 'SELECT') {
            data[el.id] = el.value;
        }
    });
    return data;
}

function restorePageFields(data) {
    if (!data) return;
    Object.entries(data).forEach(([id, val]) => {
        const el = document.getElementById(id);
        if (!el) return;
        if (el.tagName === 'INPUT') {
            if (el.type === 'checkbox') el.checked = !!val;
            else if (el.type !== 'button' && el.type !== 'submit') el.value = val ?? '';
        } else if (el.tagName === 'TEXTAREA' || el.tagName === 'SELECT') {
            el.value = val ?? '';
        }
    });
}

function atsSafeFileName(name) {
    return String(name || 'document')
        .replace(/[\\\/:*?"<>|]+/g, ' ')
        .replace(/\s+/g, ' ')
        .trim()
        .replace(/ /g, '_');
}

function atsStripHiddenNodes(root) {
    root.querySelectorAll('*').forEach(el => {
        const s = el.style && el.style.display;
        if (s === 'none') el.remove();
    });
}

function atsInlineComputedStyles(sourceEl, cloneEl) {
    if (!sourceEl || !cloneEl || sourceEl.nodeType !== 1 || cloneEl.nodeType !== 1) return;
    const computed = window.getComputedStyle(sourceEl);
    let styleText = '';
    for (const prop of computed) {
        styleText += `${prop}:${computed.getPropertyValue(prop)};`;
    }
    cloneEl.setAttribute('style', styleText);

    const srcChildren = Array.from(sourceEl.children || []);
    const cloneChildren = Array.from(cloneEl.children || []);
    for (let i = 0; i < srcChildren.length; i++) {
        atsInlineComputedStyles(srcChildren[i], cloneChildren[i]);
    }
}

function atsMeasureElement(sourceEl) {
    const width = Math.max(
        Math.ceil(sourceEl.scrollWidth || 0),
        Math.ceil(sourceEl.offsetWidth || 0),
        Math.ceil(sourceEl.getBoundingClientRect().width || 0)
    );
    const height = Math.max(
        Math.ceil(sourceEl.scrollHeight || 0),
        Math.ceil(sourceEl.offsetHeight || 0),
        Math.ceil(sourceEl.getBoundingClientRect().height || 0)
    );
    return { width, height };
}

function atsBuildSvgMarkupForElement(sourceEl) {
    const { width, height } = atsMeasureElement(sourceEl);
    const clone = sourceEl.cloneNode(true);
    atsStripHiddenNodes(clone);
    atsInlineComputedStyles(sourceEl, clone);
    clone.setAttribute('xmlns', 'http://www.w3.org/1999/xhtml');

    const wrapper = document.createElement('div');
    wrapper.setAttribute('xmlns', 'http://www.w3.org/1999/xhtml');
    wrapper.style.width = width + 'px';
    wrapper.style.height = height + 'px';
    wrapper.style.background = '#ffffff';
    wrapper.style.boxSizing = 'border-box';
    wrapper.appendChild(clone);

    const xhtml = new XMLSerializer().serializeToString(wrapper);
    const svg = `<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="${width}" height="${height}" viewBox="0 0 ${width} ${height}">
    <foreignObject width="100%" height="100%">${xhtml}</foreignObject>
</svg>`;
    return { svg, width, height };
}

function atsSvgToDataUrl(svg) {
    return 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svg);
}

async function atsElementToPngDataUrl(sourceEl) {
    if (document.fonts && document.fonts.ready) {
        try { await document.fonts.ready; } catch (_) {}
    }

    if (typeof html2canvas === 'function') {
        try {
            const canvas = await html2canvas(sourceEl, {
                scale: 2,
                useCORS: true,
                allowTaint: true,
                backgroundColor: '#ffffff',
                logging: false,
                scrollX: 0,
                scrollY: 0,
                windowWidth: document.documentElement.scrollWidth,
                windowHeight: document.documentElement.scrollHeight
            });
            return {
                src: canvas.toDataURL('image/png'),
                width:  Math.round(canvas.width  / 2),
                height: Math.round(canvas.height / 2),
                type: 'png'
            };
        } catch (err) {
            console.error('html2canvas failed, falling back', err);
        }
    }

    // Fallback: SVG foreignObject (may render blank in Chrome)
    const { svg, width, height } = atsBuildSvgMarkupForElement(sourceEl);
    const svgUrl = atsSvgToDataUrl(svg);

    try {
        const img = new Image();
        img.decoding = 'sync';
        const loadPromise = new Promise((resolve, reject) => {
            img.onload = resolve;
            img.onerror = reject;
        });
        img.src = svgUrl;
        await loadPromise;

        const scale = Math.max(2, Math.min(window.devicePixelRatio || 1, 3));
        const canvas = document.createElement('canvas');
        canvas.width = Math.max(1, Math.round(width * scale));
        canvas.height = Math.max(1, Math.round(height * scale));
        const ctx = canvas.getContext('2d');
        ctx.scale(scale, scale);
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, width, height);
        ctx.drawImage(img, 0, 0, width, height);
        return {
            src: canvas.toDataURL('image/png'),
            width,
            height,
            type: 'png'
        };
    } catch (err) {
        console.error('PNG render failed', err);
        return null;
    }
}

async function atsDownloadExcelFromElement(opts = {}) {
    const target = opts.selector
        ? document.querySelector(opts.selector)
        : (opts.elementId ? document.getElementById(opts.elementId) : null);
    if (!target) {
        alert('Excel source not found.');
        return;
    }

    const title = opts.title || document.title || 'Document';
    const pageSelector = opts.pageSelector || '.doc-page,.boe-page,.ci-page,.spi-doc,.mspi-doc,.mpi-doc,.pi-wrap';
    const pageNodes = Array.from(target.querySelectorAll(pageSelector));
    const exportNodes = pageNodes.length ? pageNodes : [target];

    try {
        const images = [];
        for (const node of exportNodes) {
            const image = await atsElementToPngDataUrl(node);
            if (!image || image.type !== 'png') {
                throw new Error('Could not render document page as PNG for Excel export.');
            }
            images.push(image);
        }
        const response = await fetch(window.APP_BASE + '/api/export_excel.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                title,
                filename: opts.filename || title,
                pages: images
            })
        });
        if (!response.ok) {
            const text = await response.text().catch(() => '');
            throw new Error(text || ('Excel export failed with status ' + response.status));
        }
        const blob = await response.blob();
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = atsSafeFileName((opts.filename || title) + '.xlsx');
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        setTimeout(() => URL.revokeObjectURL(url), 1000);
    } catch (err) {
        console.error('Excel export failed', err);
        alert('Excel export failed. Please try again after the document finishes loading.');
    }
}

function atsShouldAutoExcel() {
    return new URLSearchParams(window.location.search).get('excel') === '1';
}

// ── Signature Widget ────────────────────────────────────────────────────────
(function () {
    const SIG_PREFIX = 'ats_sig_';
    let _target = null, _mode = 'draw', _uploadData = null;

    function initSlots() {
        const orderId = (window.getCurrentOrderId ? window.getCurrentOrderId() : '') || 'noorder';
        const page    = document.body.dataset.page || 'p';
        document.querySelectorAll('.signature-image').forEach(function (el, idx) {
            // Update key every call so order changes refresh the stored sig
            const sigKey  = orderId + '_' + page + '_' + idx;
            el.dataset.sigKey  = sigKey;
            el.style.cursor    = 'pointer';
            el.style.position  = 'relative';
            el.title = 'Click to add / change signature';
            // Add click listener only once
            if (!el.dataset.sigInited) {
                el.dataset.sigInited = '1';
                el.addEventListener('click', function () { openModal(el); });
            }
            // Always restore from the current order's key
            const saved = localStorage.getItem(SIG_PREFIX + sigKey);
            if (saved) _apply(el, saved); else _placeholder(el);
        });
    }

    function _placeholder(el) {
        el.style.backgroundImage = 'none';
        el.innerHTML = '<div style="width:100%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;border:1.5px dashed #c7d0ff;border-radius:8px;background:#fafbff;box-sizing:border-box;transition:border-color .15s,background .15s;">'
            + '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>'
            + '<span style="font-size:10px;color:#94a3b8;font-weight:600;letter-spacing:0.04em;">SIGN HERE</span>'
            + '</div>';
    }

    function _apply(el, dataUrl) {
        el.style.backgroundImage = 'none';
        el.innerHTML = '<img src="'+dataUrl+'" style="width:100%;height:100%;object-fit:contain;border-radius:4px;" alt="Signature">'
            + '<button type="button" onclick="event.stopPropagation();_sigClear(\''+el.dataset.sigKey+'\')" '
            + 'style="position:absolute;top:2px;right:2px;background:#ef4444;color:#fff;border:none;border-radius:3px;font-size:10px;padding:0 4px;cursor:pointer;line-height:16px;">×</button>';
    }

    window._sigClear = function (key) {
        localStorage.removeItem(SIG_PREFIX + key);
        const el = document.querySelector('[data-sig-key="'+key+'"]');
        if (el) _placeholder(el);
    };

    function openModal(el) {
        _target = el; _uploadData = null; _mode = 'draw';
        _switchTab('draw');
        clearCanvas();
        const input = document.getElementById('sigFileInput');
        if (input) input.value = '';
        const prev = document.getElementById('sigUploadPreview');
        if (prev) { prev.style.display = 'none'; prev.src = ''; }
        const lbl = document.getElementById('sigUploadLabel');
        if (lbl) { lbl.textContent = ''; lbl.style.display = 'none'; }
        const sub = document.querySelector('.sig-upload-sub');
        const txt = document.querySelector('.sig-upload-text');
        if (sub) sub.style.display = ''; if (txt) txt.textContent = 'Click to browse or drag & drop';
        document.getElementById('sigModal')?.classList.remove('hidden');
    }

    function _switchTab(tab) {
        _mode = tab;
        document.getElementById('sigPanelDraw').style.display   = tab === 'draw'   ? 'block' : 'none';
        document.getElementById('sigPanelUpload').style.display = tab === 'upload' ? 'block' : 'none';
        const tDraw   = document.getElementById('sigTabDraw');
        const tUpload = document.getElementById('sigTabUpload');
        if (tDraw) {
            tDraw.style.background   = tab === 'draw' ? '#4f46e5' : '#f8fafc';
            tDraw.style.color        = tab === 'draw' ? '#fff'     : '#64748b';
            tDraw.style.borderColor  = tab === 'draw' ? '#4f46e5' : '#e2e8f0';
            tDraw.style.boxShadow    = tab === 'draw' ? '0 3px 10px rgba(99,102,241,.3)' : 'none';
        }
        if (tUpload) {
            tUpload.style.background  = tab === 'upload' ? '#4f46e5' : '#f8fafc';
            tUpload.style.color       = tab === 'upload' ? '#fff'     : '#64748b';
            tUpload.style.borderColor = tab === 'upload' ? '#4f46e5' : '#e2e8f0';
            tUpload.style.boxShadow   = tab === 'upload' ? '0 3px 10px rgba(99,102,241,.3)' : 'none';
        }
    }

    function clearCanvas() {
        const c = document.getElementById('sigCanvas');
        if (c) c.getContext('2d').clearRect(0, 0, c.width, c.height);
    }

    function initCanvas() {
        const c = document.getElementById('sigCanvas');
        if (!c || c._wi) return;
        c._wi = true;
        const ctx = c.getContext('2d');
        ctx.strokeStyle = '#1e1e2e'; ctx.lineWidth = 2; ctx.lineCap = 'round'; ctx.lineJoin = 'round';
        let down = false;
        const pos = e => { const r = c.getBoundingClientRect(), sx = c.width/r.width, sy = c.height/r.height, s = e.touches?e.touches[0]:e; return { x:(s.clientX-r.left)*sx, y:(s.clientY-r.top)*sy }; };
        c.addEventListener('mousedown',  e => { down=true; const p=pos(e); ctx.beginPath(); ctx.moveTo(p.x,p.y); });
        c.addEventListener('mousemove',  e => { if(!down)return; const p=pos(e); ctx.lineTo(p.x,p.y); ctx.stroke(); });
        c.addEventListener('mouseup',    () => down=false);
        c.addEventListener('mouseleave', () => down=false);
        c.addEventListener('touchstart', e => { e.preventDefault(); down=true; const p=pos(e); ctx.beginPath(); ctx.moveTo(p.x,p.y); }, {passive:false});
        c.addEventListener('touchmove',  e => { e.preventDefault(); if(!down)return; const p=pos(e); ctx.lineTo(p.x,p.y); ctx.stroke(); }, {passive:false});
        c.addEventListener('touchend',   () => down=false);
    }

    function initUpload() {
        const input   = document.getElementById('sigFileInput');
        const zone    = document.getElementById('sigUploadZone');
        const preview = document.getElementById('sigUploadPreview');
        const lbl     = document.getElementById('sigUploadLabel');
        if (!input || input._wi) return;
        input._wi = true;

        function loadFile(f) {
            if (!f || !f.type.startsWith('image/')) return;
            const r = new FileReader();
            r.onload = e => {
                _uploadData = e.target.result;
                preview.src = _uploadData; preview.style.display = 'block';
                if (lbl) { lbl.textContent = f.name; lbl.style.display = 'block'; }
                const sub = document.querySelector('.sig-upload-sub');
                const txt = document.querySelector('.sig-upload-text');
                if (sub) sub.style.display = 'none';
                if (txt) txt.textContent = 'Image selected';
            };
            r.readAsDataURL(f);
        }

        input.addEventListener('change', () => loadFile(input.files[0]));

        if (zone) {
            zone.addEventListener('dragover',  e => { e.preventDefault(); zone.style.borderColor='#6366f1'; });
            zone.addEventListener('dragleave', () => zone.style.borderColor='#c7d0ff');
            zone.addEventListener('drop', e => {
                e.preventDefault(); zone.style.borderColor='#c7d0ff';
                loadFile(e.dataTransfer.files[0]);
            });
        }
    }

    function confirmSig() {
        let dataUrl = null;
        if (_mode === 'draw') {
            const c = document.getElementById('sigCanvas');
            const d = c.getContext('2d').getImageData(0,0,c.width,c.height).data;
            if (!d.some(v => v !== 0)) { alert('Please draw a signature first.'); return; }
            dataUrl = c.toDataURL('image/png');
        } else {
            if (!_uploadData) { alert('Please select an image first.'); return; }
            dataUrl = _uploadData;
        }
        if (_target) {
            _apply(_target, dataUrl);
            localStorage.setItem(SIG_PREFIX + _target.dataset.sigKey, dataUrl);
        }
        document.getElementById('sigModal')?.classList.add('hidden');
    }

    document.addEventListener('DOMContentLoaded', function () {
        initSlots();
        initCanvas();
        initUpload();
        document.getElementById('sigTabDraw')?.addEventListener('click',    () => _switchTab('draw'));
        document.getElementById('sigTabUpload')?.addEventListener('click',  () => _switchTab('upload'));
        document.getElementById('sigClearBtn')?.addEventListener('click',   clearCanvas);
        const _closeSig = () => document.getElementById('sigModal')?.classList.add('hidden');
        document.getElementById('sigCancelBtn')?.addEventListener('click',  _closeSig);
        document.getElementById('sigCancelBtn2')?.addEventListener('click', _closeSig);
        document.getElementById('sigConfirmBtn')?.addEventListener('click', confirmSig);
    });

    // Re-init after order loads (DOMContentLoaded runs initSlots; this catches late slots)
    document.addEventListener('ats:orderloaded', function () { setTimeout(initSlots, 150); });
})();

async function saveCurrentPage() {
    const orderId  = window.getCurrentOrderId();
    const pageName = document.body.dataset.page;
    if (!orderId)  { alert('No order loaded. Load an order first.'); return; }
    if (!pageName) { return; }

    const btn = document.getElementById('universalSaveBtn');
    if (btn) { btn.textContent = '⏳ Submitting…'; btn.disabled = true; }

    try {
        const res = await fetch(APP_BASE + '/api/save_page.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ order_id: orderId, page_name: pageName, ...collectPageFields() }),
        });
        const json = await res.json();
        if (btn) { btn.textContent = '✓ Submitted'; btn.disabled = false; }
        setTimeout(() => { if (btn) btn.textContent = '📤 Submit'; }, 2000);
        if (json.error) console.warn('Save error:', json.error);
    } catch(e) {
        if (btn) { btn.textContent = '✗ Error'; btn.disabled = false; }
        setTimeout(() => { if (btn) btn.textContent = '📤 Submit'; }, 2000);
    }
}



function getUniversalSubmitHandler(pageName) {
    const pageHandlers = {
        'marketing-intake': () => typeof saveIntake === 'function' ? saveIntake() : saveCurrentPage(),
        'sales':            () => typeof savePi === 'function' ? savePi() : saveCurrentPage(),
    };

    return pageHandlers[pageName] || saveCurrentPage;
}

// Auto-inject Submit button into each workflow page action bar
document.addEventListener('DOMContentLoaded', function () {
    const actionsLeft = document.querySelector('.page-actions-left');
    const pageName    = document.body.dataset.page;
    if (!actionsLeft || !pageName) return;
    // Skip pages that already have their own dedicated save/submit buttons
    if (['dashboard', 'marketing-intake', 'sales'].includes(pageName)) return;
    if (document.getElementById('universalSaveBtn')) return;

    const btn = document.createElement('button');
    btn.type        = 'button';
    btn.className   = 'primary-btn';
    btn.id          = 'universalSaveBtn';
    btn.textContent = '📤 Submit';
    btn.onclick     = getUniversalSubmitHandler(pageName);
    btn.textContent = 'Submit';
    actionsLeft.appendChild(btn);
});
</script>

</body>
</html>

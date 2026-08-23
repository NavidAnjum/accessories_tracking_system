<?php
if (!function_exists('zzal_print_brand_header')) {
    function zzal_print_brand_header(): string
    {
        return '
        <div class="zzal-print-brand zzal-print-brand--header" style="margin:0 0 12px;padding:0;color:#111;font-family:Arial,Helvetica,sans-serif;display:grid;grid-template-columns:58px 1fr;align-items:center;column-gap:14px;row-gap:6px;">
            <div class="zzal-print-brand__logo" aria-hidden="true" style="width:48px;height:48px;border:2px solid #2d9cdb;color:#2d9cdb;display:block;font-weight:700;text-align:center;letter-spacing:.02em;position:relative;font-family:Georgia,&quot;Times New Roman&quot;,serif;overflow:hidden;">
                <span class="zzal-print-brand__z" style="position:absolute;top:2px;left:8px;font-size:28px;line-height:.8;color:#2d9cdb;font-style:italic;font-weight:700;">7</span>
                <span class="zzal-print-brand__z2" style="position:absolute;top:9px;left:17px;font-size:28px;line-height:.8;color:#7b5a68;font-style:italic;font-weight:700;">Z</span>
                <span class="zzal-print-brand__al" style="position:absolute;top:16px;right:5px;font-size:8px;font-weight:700;color:#7b5a68;">AL</span>
                <span class="zzal-print-brand__zzal" style="position:absolute;left:0;right:0;bottom:0;width:100%;font-size:12px;line-height:1;color:#fff;background:#2d9cdb;padding:1px 0 2px;letter-spacing:.04em;">ZZAL</span>
            </div>
            <div class="zzal-print-brand__title-wrap" style="flex:1;">
                <div class="zzal-print-brand__title" style="font-size:26px;font-weight:700;text-align:left;letter-spacing:.01em;line-height:1;text-transform:uppercase;color:#7b5a68;font-family:Georgia,&quot;Times New Roman&quot;,serif;">Zaber &amp; Zubair Accessories Ltd.</div>
            </div>
            <div class="zzal-print-brand__header-line" style="grid-column:1 / -1;height:2px;background:#2d9cdb;box-shadow:0 1px 0 #b9deef inset,0 -1px 0 #1f84ba inset;"></div>
        </div>';
    }
}

if (!function_exists('zzal_print_brand_footer')) {
    function zzal_print_brand_footer(): string
    {
        return '
        <div class="zzal-print-brand zzal-print-brand--footer" style="margin-top:auto;padding:0;color:#111;font-family:Arial,Helvetica,sans-serif;">
            <div class="zzal-print-brand__divider" style="height:1px;background:#111;margin:0 auto 8px;"></div>
            <div class="zzal-print-brand__footer-line" style="text-align:center;font-size:9px;line-height:1.4;margin:0 0 2px;">
                <strong>Corporate Office :</strong> Adamjee Court (4th &amp; 5th Floor), 115-120, Motijheel C/A, Dhaka-1000, Bangladesh.
            </div>
            <div class="zzal-print-brand__footer-line" style="text-align:center;font-size:9px;line-height:1.4;margin:0 0 2px;">
                <strong>Phone :</strong> +880-2-7176207-8, 7176356, 7176348, &nbsp;<strong>Fax :</strong> +880-2-9564252, 9565282, 7167293. &nbsp;<strong>Web :</strong> www.znzfab.com
            </div>
            <div class="zzal-print-brand__footer-line" style="text-align:center;font-size:9px;line-height:1.4;margin:0 0 2px;">
                <strong>Factory :</strong> Mawna, Sreepur, Gazipur. &nbsp;<strong>E-mail :</strong> znzal@znzfab.com
            </div>
        </div>';
    }
}

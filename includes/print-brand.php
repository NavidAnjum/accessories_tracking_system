<?php
if (!function_exists('zzal_print_brand_header')) {
    function zzal_print_brand_header(): string
    {
        return '
        <div class="zzal-print-brand zzal-print-brand--header">
            <div class="zzal-print-brand__logo" aria-hidden="true">
                <span class="zzal-print-brand__z">7</span>
                <span class="zzal-print-brand__z2">Z</span>
                <span class="zzal-print-brand__al">AL</span>
                <span class="zzal-print-brand__zzal">ZZAL</span>
            </div>
            <div class="zzal-print-brand__title-wrap">
                <div class="zzal-print-brand__title">Zaber &amp; Zubair Accessories Ltd.</div>
            </div>
            <div class="zzal-print-brand__header-line"></div>
        </div>';
    }
}

if (!function_exists('zzal_print_brand_footer')) {
    function zzal_print_brand_footer(): string
    {
        return '
        <div class="zzal-print-brand zzal-print-brand--footer">
            <div class="zzal-print-brand__divider"></div>
            <div class="zzal-print-brand__footer-line">
                <strong>Corporate Office :</strong> Adamjee Court (4th &amp; 5th Floor), 115-120, Motijheel C/A, Dhaka-1000, Bangladesh.
            </div>
            <div class="zzal-print-brand__footer-line">
                <strong>Phone :</strong> +880-2-7176207-8, 7176356, 7176348, &nbsp;<strong>Fax :</strong> +880-2-9564252, 9565282, 7167293. &nbsp;<strong>Web :</strong> www.znzfab.com
            </div>
            <div class="zzal-print-brand__footer-line">
                <strong>Factory :</strong> Mawna, Sreepur, Gazipur. &nbsp;<strong>E-mail :</strong> znzal@znzfab.com
            </div>
        </div>';
    }
}

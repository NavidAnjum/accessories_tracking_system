<?php
$pageTitle   = 'Marketing Intake';
$activePage  = 'marketing-intake';
$navSection  = 'order';
include __DIR__ . '/../includes/header.php';
?>

<style>
/* ── Marketing Intake ── */
.mi-summary-bar {
    display: flex;
    align-items: center;
    gap: 24px;
    flex-wrap: wrap;
    background: #fff;
    border: 1.5px solid #e0e3ff;
    border-radius: 14px;
    padding: 14px 22px;
    margin-bottom: 18px;
    box-shadow: 0 2px 8px rgba(99,102,241,.05);
}
.mi-sum-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #94a3b8; margin-bottom: 3px; }
.mi-sum-value { font-size: 18px; font-weight: 800; color: #4f46e5; }
.mi-sum-divider { width: 1px; height: 36px; background: #e0e3ff; flex-shrink: 0; }
.mi-sum-right { margin-left: auto; display: flex; align-items: center; gap: 10px; }

/* Intake header */
.mi-intake-hdr {
    background: #fff;
    border: 1.5px solid #e0e3ff;
    border-radius: 14px;
    padding: 18px 22px;
    margin-bottom: 18px;
    box-shadow: 0 2px 8px rgba(99,102,241,.05);
}
.mi-intake-hdr-top {
    display: flex; align-items: center;
    justify-content: space-between;
    margin-bottom: 14px; flex-wrap: wrap; gap: 8px;
}

/* PO block */
.po-block {
    background: #fff;
    border: 2px solid #e0e3ff;
    border-radius: 14px;
    margin-bottom: 18px;
    overflow: hidden;
    transition: border-color .18s, box-shadow .18s;
}
.po-block:focus-within { border-color: #6366f1; box-shadow: 0 4px 20px rgba(99,102,241,.10); }

.po-block-hdr {
    display: flex; align-items: center;
    justify-content: space-between;
    padding: 12px 18px;
    background: linear-gradient(90deg, #f8f9ff 0%, #fff 100%);
    border-bottom: 1.5px solid #e0e3ff;
    gap: 10px; flex-wrap: wrap;
    cursor: pointer; user-select: none;
}
.po-block-hdr:hover { background: #f0f0ff; }

.po-num-chip {
    background: linear-gradient(135deg,#6366f1,#4f46e5);
    color: #fff; border-radius: 8px;
    padding: 4px 12px; font-size: 12px; font-weight: 800;
}
.po-block-label { font-size: 13px; font-weight: 700; color: #1e1e2e; }
.po-badge {
    font-size: 12px; font-weight: 700;
    color: #6366f1; background: #ede9fe;
    padding: 3px 12px; border-radius: 999px;
}
.po-chevron { color: #94a3b8; font-size: 13px; transition: transform .2s; }
.po-chevron.closed { transform: rotate(-90deg); }

.po-body { padding: 18px 20px 16px; }

/* Item table */
.po-table { width: 100%; border-collapse: collapse; font-size: 12px; margin-top: 10px; }
.po-table th {
    background: #f1f5f9; padding: 8px 9px; text-align: left;
    font-size: 10px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .05em; color: #64748b;
    border-bottom: 1.5px solid #e2e8f0; white-space: nowrap;
}
.po-table td { padding: 5px 5px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.po-table td input {
    width: 100%; padding: 5px 8px; border: 1.5px solid #e2e8f0;
    border-radius: 6px; font-size: 12px; outline: none;
    box-sizing: border-box; transition: border-color .15s;
}
.po-table td input:focus { border-color: #6366f1; }
.po-table tfoot td {
    background: #f8f9ff; font-weight: 700; font-size: 12px;
    padding: 8px 9px; border-top: 2px solid #e0e3ff;
}
.po-del-row {
    background: none; border: none; color: #fca5a5;
    cursor: pointer; font-size: 16px; padding: 2px 6px;
    border-radius: 6px; transition: all .15s;
}
.po-del-row:hover { background: #fee2e2; color: #c0392b; }
</style>

<!-- Step-locked banner (shown when order has already moved past this step) -->
<div id="stepLockedBanner" style="display:none;background:#fef3c7;border:1.5px solid #f59e0b;border-radius:12px;padding:14px 20px;margin-bottom:18px;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
    <div>
        <strong style="color:#92400e;" id="stepLockedBannerTitle">⚠ This order has already moved past Marketing Intake.</strong>
        <div style="font-size:12px;color:#a16207;margin-top:3px;">You can still edit and re-save intake data. Current step: <span id="stepLockedBannerStep"></span></div>
    </div>
    <a href="javascript:void(0)" id="stepLockedBannerLink" style="font-size:12px;font-weight:700;color:#4f46e5;text-decoration:none;white-space:nowrap;">Go to current step →</a>
</div>

<!-- Summary bar -->
<div class="mi-summary-bar">
    <div>
        <div class="mi-sum-label">Total POs</div>
        <div class="mi-sum-value" id="sumPoCount">0</div>
    </div>
    <div class="mi-sum-divider"></div>
    <div>
        <div class="mi-sum-label">Total Qty</div>
        <div class="mi-sum-value" id="sumTotalQty">0</div>
    </div>
    <div class="mi-sum-divider"></div>
    <div>
        <div class="mi-sum-label">Total Value</div>
        <div class="mi-sum-value" id="sumTotalVal">$0.00</div>
    </div>
    <div class="mi-sum-right">
        <span style="font-size:12px;color:#94a3b8;">Status:</span>
        <span style="font-size:13px;font-weight:700;color:#f59e0b;" id="intakeStatus">Draft</span>
    </div>
</div>

<!-- Shared header fields -->
<div class="mi-intake-hdr">
    <div class="mi-intake-hdr-top">
        <div>
            <div class="eyebrow">Marketing Intake</div>
            <h2 style="margin:0;font-size:17px;">Order Header — Shared Across All POs</h2>
        </div>
    </div>
    <div class="form-grid">
        <div class="field span-4">
            <label>Customer</label>
            <select id="intakeCustomer">
                <option value="">— Select Customer —</option>
            </select>
        </div>
        <div class="field span-4">
            <label>Sales Person</label>
            <input id="intakeSalesperson" placeholder="Sales person name" value="<?= htmlspecialchars($__user['name'] ?? '') ?>">
        </div>
        <div class="field span-4">
            <label>Intake Date</label>
            <input id="intakeDate" type="date" readonly style="background:#f8f9ff;cursor:default;">
        </div>
        <div class="field span-8">
            <label>Sub / Work Order Description</label>
            <input id="intakeSubject" placeholder="e.g. Work order for BellyBand (As Per Approved Sample)">
        </div>
        <div class="field span-4">
            <label>Paper Quality</label>
            <input id="intakePaperQuality" placeholder="e.g. 350 GSM">
        </div>
    </div>
</div>

<!-- PO Blocks container -->
<div id="poBlocksContainer"></div>

<!-- Add PO -->
<div style="text-align:center;margin-bottom:20px;">
    <button class="ghost-btn" onclick="addPoBlock()"
            style="padding:10px 32px;font-size:14px;border-style:dashed;border-width:2px;">
        + Add Another PO
    </button>
</div>

<!-- Page actions -->
<div class="page-actions">
    <div class="page-actions-left">
        <button type="button" class="ghost-btn" onclick="saveIntake()">💾 Save Intake</button>
        <button type="button" class="ghost-btn" onclick="clearAll()">Clear</button>
    </div>
    <div class="page-actions-right">
        <button type="button" class="primary-btn" onclick="sendToCosting()">
            Send To Costing →
        </button>
    </div>
</div>

<script>
let poCount = 0;
let rowCtrs = {};

// ── Per-product-line spec column config ─────────────────────────────────────
const INTAKE_SPECS = {
    'Carton':         { s1:{ type:'carton-fields' },          s2:{},                              s3:{},                      s4:{} },
    'Label':          { s1:{ ph:'Printing Status' },         s2:{ ph:'No. of Colors' },          s3:{ ph:'Cutting Type' },   s4:{ ph:'Ribbon Type' } },
    'Offset':         { s1:{ ph:'No. of Colors' },           s2:{ ph:'GSM' },                    s3:{ ph:'Lamination' },     s4:{} },
    'Printed Label':  { s1:{ ph:'No. of Colors' },           s2:{ ph:'GSM' },                    s3:{ ph:'Lamination' },     s4:{} },
    'Poly':           { s1:{ ph:'Thickness (Micron)' },      s2:{ ph:'Gusset' },                 s3:{},                      s4:{} },
    'PVC':            { s1:{ ph:'Thickness (Micron)' },      s2:{ ph:'Gusset / Flap' },          s3:{},                      s4:{} },
    'Paper Tube':     { s1:{ ph:'Inner Dia' },               s2:{ ph:'Outer Dia' },              s3:{},                      s4:{} },
    'Gum Tape':       { s1:{ ph:'Thickness (Micron)' },      s2:{ ph:'MTR / Roll' },             s3:{},                      s4:{} },
    'Binding':        { s1:{ ph:'Width / Spec' },            s2:{},                              s3:{},                      s4:{} },
    'Twill Tape':     { s1:{ ph:'Width' },                   s2:{ ph:'Thread Type' },            s3:{},                      s4:{} },
    'Elastic':        { s1:{ ph:'Width' },                   s2:{ ph:'Denier' },                 s3:{},                      s4:{} },
    'Drawstring':     { s1:{ ph:'Width' },                   s2:{ ph:'Tipping Type' },           s3:{},                      s4:{} },
    'Sewing Thread':  { s1:{ ph:'Count (e.g. 40/2)' },       s2:{ ph:'MTR / Cone' },             s3:{},                      s4:{} },
    'Hanger':         { s1:{ ph:'Thickness' },               s2:{ ph:'Neck Height' },            s3:{},                      s4:{} },
};
const INTAKE_SPEC_DEFAULT = { s1:{ ph:'Spec' }, s2:{ ph:'Detail' }, s3:{}, s4:{} };

const SPEC_HEADERS = {
    'Carton':         ['','','',''],
    'Label':          ['','','',''],
    'Offset':         ['','','',''],
    'Poly':           ['','','',''],
    'PVC':            ['','','',''],
    'Paper Tube':     ['','','',''],
    'Gum Tape':       ['','','',''],
    'Twill Tape':     ['','','',''],
    'Elastic':        ['','','',''],
    'Drawstring':     ['','','',''],
    'Sewing Thread':  ['','','',''],
    'Hanger':         ['','','',''],
    'Printed Label':  ['No. of Colors',     'GSM',           'Lamination',       ''],
    'Binding':        ['Width / Spec',      '',              '',                 ''],
};

// ── Per-product detail row configs (field definitions + Item Desc formula) ───
const s_ = (opts) => ({ type:'select', opts });
const n_ = (ph)   => ({ type:'number', ph, step:'0.01' });
const t_ = (ph)   => ({ type:'text',   ph });
const PRINT_OPTS  = ['Printed','Non Printed'];
const LPRINT_OPTS = ['One Side Printed','Two Side Printed','Non Printed'];
const UOM_CM      = ['CM','MM','Inch'];
const UOM_YDS     = ['Yds','Mtr','Cone'];

const PN_OPTS = {
    'Label':         ['Brand Label','Wash Care Label','Size Label','Printed Label','Line label','Oekotex Label','Paper Label','Satin Label','Warning Label','Shade Label','Sticker Label','Country Label','Batch Code Label','Tracking label','Law Label'],
    'Offset':        ['Invercote Paper Band Roll','Plastic Band Roll','Band Roll','Insert','Hanger','Insert Baner','Insert Belly Band Sticker','Carton Sticker','Barcode Sticker','Insert (Front+Back)','Hang Tag','Duplex Board','Paper Band Roll','Sticker','Inlay Card','Adhesive Paper A4','Round Sticker','Belly Band','Shipping Mark','Size Sticker','Set Barcode','Box','Tissue Paper','Poly Sticker','Sticker Paper (A4 Size)','File','Transparent Stcker','Insert Belly Band','Safety First Sticker','Booklet','Sticker Potty','Photo Sticker','Country Paper Size','Header Card','Bale Paper','Skin Paper','Paper Tag','Carton Info Page','Box Sticker','Catalog Book','Ngrip Paper'],
    'Poly':          ['PE Poly','HDPE Poly','PP Poly','Roll Poly'],
    'PVC':           ['PVC Bag','Card Poly','PVC POLY BAG','ZIPPER BAG','PVC HANGER BAG','PE Zipper Bag','PVC Hanger Bag'],
    'Paper Tube':    ['PAPER TUBE','PAPER CONE'],
    'Drawstring':    ['Round Drawstring','Flat Drawstring'],
    'Elastic':       ['Elastic','Adjustable Elastic'],
    'Sewing Thread': ['Sewing Thread'],
    'Twill Tape':    ['Heringbone Tape','Canvas Tape','Grossgain Tape','Twill Tape','Canvas Belt'],
    'Gum Tape':      ['Gum Tape'],
    'Hanger':        ['M-Clip','Band Clip','J-Hook','Plastic Hanger','J-Hook Heritage'],
};

const PRODUCT_DETAIL_CONFIGS = {
    'Label': {
        bg: '#f5fff0', border: '#b7e4c7',
        fields: [
            { id:'pn',     label:'Product Name',    w:140, ...s_(PN_OPTS['Label']) },
            { id:'print',  label:'Printing Status', w:130, ...s_(LPRINT_OPTS) },
            { id:'colors', label:'No. Of Colors',   w:80,  ...n_('e.g. 4') },
            { id:'cut',    label:'Cutting Type',    w:130, ...t_('e.g. Ultrasonic Cutting') },
            { id:'fold',   label:'Folding Type',    w:100, ...t_('Folding Type') },
            { id:'ribbon', label:'Ribbon Type',     w:120, ...t_('e.g. Satin Ribbon') },
            { id:'len',    label:'Length',          w:70,  ...n_('e.g. 75') },
            { id:'wid',    label:'Width',           w:70,  ...n_('e.g. 25') },
            { id:'uom',    label:'UOM',             w:70,  ...s_(['MM','CM','Inch']) },
        ],
        artSize: v => v.len && v.wid ? `${v.len}x${v.wid} ${v.uom}` : '',
        desc: v => [v.len&&v.wid?`${v.len}X${v.wid}`:'', v.uom, v.pn, v.print, v.ribbon].filter(Boolean).join(' '),
        seg2: v => [v.pn, v.len&&v.wid?`${v.len}x${v.wid}`:'', v.uom, v.cut, v.ribbon, v.print].filter(Boolean).join(' '),
    },
    'Offset': {
        bg: '#fffbf0', border: '#f0d080',
        fields: [
            { id:'pn',      label:'Product Name',       w:160, ...s_(PN_OPTS['Offset']) },
            { id:'print',   label:'Printing Status',    w:100, ...s_(PRINT_OPTS) },
            { id:'colors',  label:'No. Of Colors',      w:80,  ...n_('e.g. 4') },
            { id:'len',     label:'Length',             w:70,  ...n_('e.g. 17') },
            { id:'wid',     label:'Width',              w:70,  ...n_('e.g. 10') },
            { id:'uom',     label:'UOM',                w:70,  ...s_(UOM_CM) },
            { id:'lam',     label:'Lamination Type',    w:130, ...t_('e.g. Matt 60% Glossy 40%') },
            { id:'varnish', label:'Varnish/Coated Type',w:130, ...t_('Varnish type') },
            { id:'cut',     label:'Cutting Type',       w:130, ...t_('e.g. Die Cutting') },
            { id:'paper',   label:'Paper Name',         w:150, ...t_('e.g. Solid Bleach Board') },
            { id:'gsm',     label:'GSM',                w:75,  ...n_('e.g. 220') },
        ],
        artSize: v => v.len && v.wid ? `${v.len}x${v.wid} ${v.uom}` : '',
        desc: v => [v.print, v.pn, v.len&&v.wid?`${v.len}X${v.wid}`:'', v.uom].filter(Boolean).join(' '),
        seg2: v => [v.pn, v.len&&v.wid?`${v.len}x${v.wid}`:'', v.uom, v.cut, v.gsm?`${v.gsm} GSM`:'', v.paper].filter(Boolean).join(' '),
    },
    'Poly': {
        bg: '#f0f8ff', border: '#a8d4f5',
        fields: [
            { id:'pn',      label:'Product Name',       w:120, ...s_(PN_OPTS['Poly']) },
            { id:'recycle', label:'Recycle %',          w:80,  ...t_('e.g. 100%') },
            { id:'rm',      label:'RM Type',            w:90,  ...t_('e.g. Virgin') },
            { id:'print',   label:'Printing Status',    w:100, ...s_(PRINT_OPTS) },
            { id:'seal',    label:'Sealing Type',       w:120, ...t_('e.g. Side Sealing') },
            { id:'len',     label:'Length',             w:70,  ...n_('e.g. 24') },
            { id:'wid',     label:'Width',              w:70,  ...n_('e.g. 18') },
            { id:'gusset',  label:'Gusset',             w:75,  ...n_('e.g. 2.75') },
            { id:'flap',    label:'Flap',               w:65,  ...n_('e.g. 6') },
            { id:'uom',     label:'UOM',                w:70,  ...s_(UOM_CM) },
            { id:'thick',   label:'Thickness (Micron)', w:100, ...n_('e.g. 10') },
        ],
        artSize: v => v.len && v.wid ? `${v.len}x${v.wid}${v.gusset?`+${v.gusset}`:''}${v.flap?`+${v.flap}`:''}  ${v.uom}` : '',
        desc: v => {
            const d = `${v.len||''}X${v.wid||''}${v.gusset?`+${v.gusset}`:''}${v.flap?`+${v.flap}`:''}`;
            return [v.recycle, v.rm, v.pn, v.print, d!='X'?d:'', v.uom].filter(Boolean).join(' ');
        },
        seg2: v => {
            const d = `${v.len||''}x${v.wid||''}${v.gusset?`+${v.gusset}`:''}${v.flap?`+${v.flap}`:''}`;
            return [v.pn, v.print, d!='x'?d:'', v.uom, v.seal, v.thick?`${v.thick} Micron`:''].filter(Boolean).join(' ');
        },
    },
    'PVC': {
        bg: '#fff5f5', border: '#f5a8a8',
        fields: [
            { id:'pn',     label:'Product Name',           w:120, ...s_(PN_OPTS['PVC']) },
            { id:'print',  label:'Printing Status',        w:100, ...s_(PRINT_OPTS) },
            { id:'req',    label:'Additional Requirement', w:150, ...t_('e.g. Without Pocket') },
            { id:'len',    label:'Length',                 w:70,  ...n_('e.g. 34') },
            { id:'wid',    label:'Width',                  w:70,  ...n_('e.g. 21.5') },
            { id:'hgt',    label:'Height',                 w:70,  ...n_('Height') },
            { id:'gusset', label:'Gusset',                 w:75,  ...n_('e.g. 4.5') },
            { id:'flap',   label:'Flap',                   w:65,  ...n_('Flap') },
            { id:'uom',    label:'UOM',                    w:70,  ...s_(UOM_CM) },
            { id:'thick',  label:'Thickness (Micron)',     w:100, ...n_('e.g. 9') },
        ],
        artSize: v => v.len && v.wid ? `${v.len}x${v.wid}${v.gusset?`+${v.gusset}+`:''} ${v.uom}` : '',
        desc: v => {
            const d = `${v.len||''}X${v.wid||''}${v.gusset?`X+${v.gusset}+`:''}`;
            return [v.print, v.pn, v.thick?`${v.thick} MM`:'', d!='X'?d:'', v.uom, v.req].filter(Boolean).join(' ');
        },
        seg2: v => {
            const d = `${v.len||''}x${v.wid||''}${v.gusset?`x+${v.gusset}+`:''}`;
            return [v.pn, v.print, d!='x'?d:'', v.uom, v.req, v.thick?`${v.thick} MM`:''].filter(Boolean).join(' ');
        },
    },
    'Paper Tube': {
        bg: '#f5f0ff', border: '#c4a8f5',
        fields: [
            { id:'pn',     label:'Product Name',   w:130, ...s_(PN_OPTS['Paper Tube']) },
            { id:'len',    label:'Length',          w:70,  ...n_('e.g. 59') },
            { id:'inner',  label:'Inner Dia',       w:80,  ...n_('e.g. 34') },
            { id:'inner2', label:'Inner Dia-2',     w:85,  ...n_('Inner Dia 2') },
            { id:'outer',  label:'Outer Dia',       w:80,  ...n_('Outer Dia') },
            { id:'uom',    label:'UOM',             w:70,  ...s_(['Inch','CM','MM']) },
            { id:'weight', label:'Weight (Gram)',   w:100, ...n_('e.g. 350') },
        ],
        artSize: v => v.len ? `${v.len}x${v.inner||''}${v.inner2?`x${v.inner2}`:''} ${v.uom}` : '',
        desc: v => [`${v.len||''}X${v.inner||''}${v.inner2?`X${v.inner2}`:''}`.replace(/^X+|X+$/g,''), v.uom, v.pn].filter(Boolean).join(' '),
        seg2: v => [v.pn, `${v.len||''}x${v.inner||''}`.replace(/x$/,''), v.uom, v.weight?`${v.weight} Gram`:''  ].filter(Boolean).join(' '),
    },
    'Drawstring': {
        bg: '#fff8f0', border: '#f5c8a8',
        fields: [
            { id:'pn',     label:'Product Name',   w:140, ...s_(PN_OPTS['Drawstring']) },
            { id:'thread', label:'Thread Type',    w:140, ...t_('e.g. 100% Cotton') },
            { id:'rubber', label:'Rubber Type',    w:110, ...t_('Rubber Type') },
            { id:'wid',    label:'Width',          w:70,  ...n_('e.g. 1') },
            { id:'wuom',   label:'Width UOM',      w:85,  ...s_(['MM','CM','Inch']) },
            { id:'len',    label:'Length',         w:70,  ...n_('Length') },
            { id:'luom',   label:'Length UOM',     w:85,  ...s_(['MM','CM','Inch','Yds','Mtr']) },
            { id:'tip',    label:'Tipping Type',   w:120, ...t_('e.g. Metal Tipping') },
        ],
        artSize: v => v.wid ? `${v.wid} ${v.wuom}` : '',
        desc: v => [v.thread, v.wid?`${v.wid} ${v.wuom}`:'', v.pn].filter(Boolean).join(' '),
        seg2: v => [v.pn, v.thread, v.wid?`${v.wid} ${v.wuom}`:'', v.tip].filter(Boolean).join(' '),
    },
    'Elastic': {
        bg: '#f0fff8', border: '#a8f5d4',
        fields: [
            { id:'pn',     label:'Product Name',   w:120, ...s_(PN_OPTS['Elastic']) },
            { id:'thread', label:'Thread Type',    w:150, ...t_('e.g. 100% Polyester') },
            { id:'rubber', label:'Rubber Type',    w:130, ...t_('e.g. Double Rubber') },
            { id:'wid',    label:'Width',          w:70,  ...n_('e.g. 8.46') },
            { id:'uom',    label:'UOM',            w:70,  ...s_(['MM','CM','Inch']) },
            { id:'denier', label:'Denier',         w:90,  ...t_('e.g. 900D') },
        ],
        artSize: v => v.wid ? `${v.wid} ${v.uom}` : '',
        desc: v => [v.thread, v.denier, v.wid?`${v.wid} ${v.uom}`:'', v.pn].filter(Boolean).join(' '),
        seg2: v => [v.pn, v.thread, v.denier, v.wid?`${v.wid} ${v.uom}`:'', v.rubber].filter(Boolean).join(' '),
    },
    'Sewing Thread': {
        bg: '#f8f0ff', border: '#d4a8f5',
        fields: [
            { id:'pn',     label:'Product Name',   w:130, ...s_(PN_OPTS['Sewing Thread']) },
            { id:'thread', label:'Thread Type',    w:170, ...t_('e.g. 100% Spun Polyester') },
            { id:'count',  label:'Count',          w:90,  ...t_('e.g. 40/2') },
            { id:'mtr',    label:'Mtr per Cone',   w:100, ...n_('e.g. 4000') },
            { id:'uom',    label:'UOM',            w:70,  ...s_(UOM_YDS) },
        ],
        artSize: () => '',
        desc: v => [v.thread, v.count, v.pn, v.mtr, v.uom].filter(Boolean).join(' '),
        seg2: v => [v.pn, v.thread, v.count, v.mtr?`${v.mtr} ${v.uom}`:''].filter(Boolean).join(' '),
    },
    'Twill Tape': {
        bg: '#f0f5ff', border: '#a8b8f5',
        fields: [
            { id:'pn',     label:'Product Name',   w:150, ...s_(PN_OPTS['Twill Tape']) },
            { id:'thread', label:'Thread Type',    w:150, ...t_('e.g. 100% Cotton') },
            { id:'wid',    label:'Width',          w:70,  ...n_('e.g. 2.54') },
            { id:'uom',    label:'UOM',            w:70,  ...s_(UOM_CM) },
        ],
        artSize: v => v.wid ? `${v.wid} ${v.uom}` : '',
        desc: v => [v.thread, v.wid?`${v.wid} ${v.uom}`:'', v.pn].filter(Boolean).join(' '),
        seg2: v => [v.pn, v.thread, v.wid?`${v.wid} ${v.uom}`:''].filter(Boolean).join(' '),
    },
    'Gum Tape': {
        bg: '#fffff0', border: '#e8e880',
        fields: [
            { id:'pn',    label:'Product Name',       w:120, ...s_(PN_OPTS['Gum Tape']) },
            { id:'print', label:'Printing Status',    w:110, ...s_(['Non Printed','Printed']) },
            { id:'wid',   label:'Width',              w:70,  ...n_('e.g. 2') },
            { id:'wuom',  label:'Width UOM',          w:85,  ...s_(['Inch','CM','MM']) },
            { id:'thick', label:'Thickness (Micron)', w:100, ...n_('e.g. 42') },
            { id:'mtr',   label:'MTR / Roll',         w:90,  ...n_('e.g. 75') },
        ],
        artSize: v => v.wid ? `${v.wid} ${v.wuom}` : '',
        desc: v => [v.wid?`${v.wid} ${v.wuom}`:'', v.print, v.thick?`${v.thick} Micron`:'', v.pn].filter(Boolean).join(' '),
        seg2: v => [v.pn, v.wid?`${v.wid} ${v.wuom}`:'', v.print, v.thick?`${v.thick} Micron`:'', v.mtr?`${v.mtr} Mtr/Roll`:''].filter(Boolean).join(' '),
    },
    'Hanger': {
        bg: '#fff0fb', border: '#f5a8e8',
        fields: [
            { id:'pn',    label:'Product Name', w:130, ...s_(PN_OPTS['Hanger']) },
            { id:'len',   label:'Length',       w:70,  ...n_('e.g. 5') },
            { id:'wid',   label:'Width',        w:70,  ...n_('e.g. 1.5') },
            { id:'neck',  label:'Neck Height',  w:90,  ...n_('Neck Height') },
            { id:'thick', label:'Thickness',    w:85,  ...n_('Thickness') },
            { id:'uom',   label:'UOM',          w:70,  ...s_(UOM_CM) },
        ],
        artSize: v => v.len && v.wid ? `${v.len}X${v.wid} ${v.uom}` : '',
        desc: v => {
            const dim = v.len && v.wid ? `${v.len}X${v.wid}${v.neck?`X${v.neck}`:''}` : '';
            return [v.pn, dim].filter(Boolean).join(' ');
        },
        seg2: v => [v.pn, v.len&&v.wid?`${v.len}x${v.wid}`:'', v.neck?`Neck ${v.neck}`:'', v.uom].filter(Boolean).join(' '),
    },
};

function renderGenericDetailRow(rid, productLine) {
    const cfg = PRODUCT_DETAIL_CONFIGS[productLine];
    if (!cfg) return;
    const mkField = f => {
        const inner = f.type === 'select'
            ? `<select id="${f.id}_${rid}" onchange="genGenericDesc('${rid}','${productLine}')" style="width:100%;font-size:11px;padding:3px 5px;">
                <option value="">— ${f.label} —</option>
                ${(f.opts||[]).map(o=>`<option value="${o}">${o}</option>`).join('')}
               </select>`
            : `<input id="${f.id}_${rid}" type="${f.type||'text'}" placeholder="${f.ph||''}" ${f.step?`step="${f.step}"`:''}
               oninput="genGenericDesc('${rid}','${productLine}')" style="width:100%;font-size:11px;padding:3px 5px;">`;
        return `<div style="display:flex;flex-direction:column;gap:2px;flex:1 1 ${f.w||90}px;min-width:0;max-width:${f.w ? f.w*2 : 180}px;">
            <span style="font-size:10px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;white-space:nowrap;">${f.label}</span>
            ${inner}
        </div>`;
    };
    const mainRow = document.getElementById('row_' + rid);
    const colCount = mainRow?.cells?.length || 14;
    const detailTr = document.createElement('tr');
    detailTr.id = 'ctn_detail_' + rid;
    detailTr.innerHTML = `<td colspan="${colCount}" style="padding:8px 14px 12px;background:${cfg.bg||'#f7f7ff'};border-top:none;border-bottom:2px solid ${cfg.border||'#c0c0f0'};">
        <div style="display:flex;flex-wrap:wrap;gap:6px 10px;width:100%;box-sizing:border-box;overflow:hidden;">
            ${cfg.fields.map(mkField).join('')}
        </div>
        <div style="margin-top:8px;padding:6px 10px;background:rgba(255,255,255,.6);border-radius:6px;font-size:11px;display:flex;gap:20px;flex-wrap:wrap;">
            <div><strong style="color:#475569;">Item Desc:</strong> <span id="ctn_desc_preview_${rid}" style="color:#1e293b;">—</span></div>
            <div><strong style="color:#475569;">Seg-2:</strong> <span id="ctn_seg2_preview_${rid}" style="color:#6366f1;">—</span></div>
        </div>
    </td>`;
    mainRow?.after(detailTr);
}

function genGenericDesc(rid, productLine) {
    const cfg = PRODUCT_DETAIL_CONFIGS[productLine];
    if (!cfg) return;
    const vals = {};
    cfg.fields.forEach(f => { vals[f.id] = document.getElementById(f.id + '_' + rid)?.value?.trim() || ''; });
    const itemDesc = cfg.desc(vals);
    const seg2     = cfg.seg2 ? cfg.seg2(vals) : itemDesc;
    const artStr   = cfg.artSize ? cfg.artSize(vals) : '';
    const artEl    = document.getElementById('artSize_' + rid);
    if (artEl && artEl.readOnly) artEl.value = artStr;
    const nameEl = document.getElementById('itemName_' + rid);
    if (nameEl) { nameEl.value = itemDesc; nameEl.title = 'Seg-2: ' + seg2; nameEl.dataset.seg2 = seg2; }
    const dp = document.getElementById('ctn_desc_preview_' + rid);
    const sp = document.getElementById('ctn_seg2_preview_' + rid);
    if (dp) dp.textContent = itemDesc || '—';
    if (sp) sp.textContent = seg2     || '—';
}

function updateRowSpecs(rid, productLine) {
    const cfg  = INTAKE_SPECS[productLine] || INTAKE_SPEC_DEFAULT;
    const s1td = document.getElementById('spec1_' + rid);
    const s2td = document.getElementById('spec2_' + rid);
    const s3td = document.getElementById('spec3_' + rid);
    const s4td = document.getElementById('spec4_' + rid);
    if (!s1td) return;

    // ── Clean up any previous Carton detail row ──────────────────────────────
    document.getElementById('ctn_detail_' + rid)?.remove();
    // If switching away from Carton (itemNameTd has an input), restore to SELECT
    const nameTdCheck = document.getElementById('itemNameTd_' + rid);
    if (nameTdCheck?.querySelector('input#itemName_' + rid)) {
        nameTdCheck.innerHTML = `<select id="itemName_${rid}" style="min-width:160px;" onchange="onItemNameChange('${rid}')"><option value="">— Item —</option></select>`;
    }
    // Reset artSize field if it was locked for Carton
    const artEl0 = document.getElementById('artSize_' + rid);
    if (artEl0 && artEl0.readOnly) {
        artEl0.readOnly = false; artEl0.value = '';
        artEl0.placeholder = 'e.g. 62x54x31 CM';
        artEl0.style.background = ''; artEl0.style.color = '';
    }

    // Update table headers for this PO block to show real field names
    const pid = rid.split('_').slice(0, -1).join('_'); // "po1" from "po1_1"
    const hdrs = SPEC_HEADERS[productLine] ?? ['Spec 1','Spec 2','Spec 3','Spec 4'];
    ['spec1','spec2','spec3','spec4'].forEach((s, i) => {
        const th = document.getElementById('th_' + s + '_' + pid);
        if (th) th.textContent = hdrs[i] != null ? hdrs[i] : ('Spec ' + (i+1));
    });

    if (PRODUCT_DETAIL_CONFIGS[productLine]) {
        // Generic detail row (Label, Offset, Poly, PVC, Paper Tube, Drawstring, Elastic, Sewing Thread, Twill Tape, Gum Tape, Hanger)
        const nameTd = document.getElementById('itemNameTd_' + rid);
        if (nameTd) nameTd.innerHTML = `<input id="itemName_${rid}" readonly placeholder="Auto-generated…" style="min-width:220px;background:#f8fafc;font-size:11px;color:#374151;cursor:default;">`;
        if (s1td) s1td.innerHTML = '';
        if (s2td) s2td.innerHTML = '';
        if (s3td) s3td.innerHTML = '';
        if (s4td) s4td.innerHTML = '';
        const artEl = document.getElementById('artSize_' + rid);
        if (artEl) { artEl.readOnly = true; artEl.value = ''; artEl.placeholder = 'Auto-filled'; artEl.style.background = '#f8fafc'; artEl.style.color = '#6366f1'; }
        renderGenericDetailRow(rid, productLine);
    } else if (cfg.s1.type === 'carton-fields') {
        // ── Carton: full detail row with all Excel columns ────────────────────
        const co = (typeof CARTON_OPTIONS !== 'undefined') ? CARTON_OPTIONS : {};
        const mkSel = (id, opts, ph) => `<select id="${id}_${rid}" onchange="genCartonDesc('${rid}')" style="width:100%;font-size:11px;padding:3px 5px;">
            <option value="">— ${ph} —</option>${(opts||[]).map(o=>`<option value="${o}">${o}</option>`).join('')}</select>`;
        const mkInp = (id, ph, tp) => `<input id="${id}_${rid}" type="${tp||'text'}" placeholder="${ph}" step="0.1" oninput="genCartonDesc('${rid}')" style="width:100%;font-size:11px;padding:3px 5px;">`;

        // Replace item name cell with readonly auto-desc field
        const nameTd = document.getElementById('itemNameTd_' + rid);
        if (nameTd) nameTd.innerHTML = `<input id="itemName_${rid}" readonly placeholder="Auto-generated…" style="min-width:220px;background:#f8fafc;font-size:11px;color:#374151;cursor:default;" title="Item Desc — auto-filled from Carton fields below">`;

        // Spec cells are empty — all fields live in the detail row below
        if (s1td) s1td.innerHTML = '';
        if (s2td) s2td.innerHTML = '';
        if (s3td) s3td.innerHTML = '';
        if (s4td) s4td.innerHTML = '';

        // Lock artSize to auto-fill from L×W×H
        const artEl = document.getElementById('artSize_' + rid);
        if (artEl) { artEl.readOnly = true; artEl.value = ''; artEl.placeholder = 'Auto (L×W×H)'; artEl.style.background = '#f8fafc'; artEl.style.color = '#6366f1'; }

        // Build and inject the detail row
        const mainRow = document.getElementById('row_' + rid);
        const colCount = mainRow?.cells?.length || 14;
        const detailTr = document.createElement('tr');
        detailTr.id = 'ctn_detail_' + rid;
        detailTr.innerHTML = `<td colspan="${colCount}" style="padding:8px 14px 12px;background:#f0f7ff;border-top:none;border-bottom:2px solid #c7d7ff;">
            <div style="display:flex;flex-wrap:wrap;gap:6px 12px;">
                <div style="display:flex;flex-direction:column;gap:2px;min-width:130px;flex:1 1 130px;">
                    <span style="font-size:10px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;">Product Name</span>
                    ${mkSel('ctn_product', co.productNames, 'Product Name')}
                </div>
                <div style="display:flex;flex-direction:column;gap:2px;min-width:110px;flex:1 1 110px;">
                    <span style="font-size:10px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;">Printing Status</span>
                    ${mkSel('ctn_print', co.printStatuses, 'Printing')}
                </div>
                <div style="display:flex;flex-direction:column;gap:2px;min-width:80px;flex:0 1 80px;">
                    <span style="font-size:10px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;">Print Side</span>
                    ${mkSel('ctn_printside', co.printSides, 'Side')}
                </div>
                <div style="display:flex;flex-direction:column;gap:2px;min-width:65px;flex:0 1 65px;">
                    <span style="font-size:10px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;">Length</span>
                    ${mkInp('ctn_len', 'e.g. 58', 'number')}
                </div>
                <div style="display:flex;flex-direction:column;gap:2px;min-width:65px;flex:0 1 65px;">
                    <span style="font-size:10px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;">Width</span>
                    ${mkInp('ctn_wid', 'e.g. 29', 'number')}
                </div>
                <div style="display:flex;flex-direction:column;gap:2px;min-width:65px;flex:0 1 65px;">
                    <span style="font-size:10px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;">Height</span>
                    ${mkInp('ctn_hgt', 'e.g. 27.5', 'number')}
                </div>
                <div style="display:flex;flex-direction:column;gap:2px;min-width:65px;flex:0 1 65px;">
                    <span style="font-size:10px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;">Fold</span>
                    ${mkInp('ctn_fold', 'Fold')}
                </div>
                <div style="display:flex;flex-direction:column;gap:2px;min-width:65px;flex:0 1 65px;">
                    <span style="font-size:10px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;">UOM</span>
                    ${mkSel('ctn_uom', ['CM','MM','Inch'], 'UOM')}
                </div>
                <div style="display:flex;flex-direction:column;gap:2px;min-width:80px;flex:0 1 80px;">
                    <span style="font-size:10px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;">Ply</span>
                    ${mkSel('ctn_ply', co.plies, 'Ply')}
                </div>
                <div style="display:flex;flex-direction:column;gap:2px;min-width:85px;flex:0 1 85px;">
                    <span style="font-size:10px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;">Flute Type</span>
                    ${mkSel('ctn_flute', co.fluteTypes, 'Flute')}
                </div>
                <div style="display:flex;flex-direction:column;gap:2px;min-width:120px;flex:1 1 120px;">
                    <span style="font-size:10px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;">Die Cutting Status</span>
                    ${mkSel('ctn_diecut', co.dieCutStatuses, 'Die Cut')}
                </div>
                <div style="display:flex;flex-direction:column;gap:2px;min-width:145px;flex:1 1 145px;">
                    <span style="font-size:10px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;">Paper Type</span>
                    ${mkSel('ctn_paper', co.paperTypes, 'Paper Type')}
                </div>
                <div style="display:flex;flex-direction:column;gap:2px;min-width:145px;flex:1 1 145px;">
                    <span style="font-size:10px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;">Paper Grade</span>
                    ${mkSel('ctn_grade', co.paperGrades, 'Grade')}
                </div>
                <div style="display:flex;flex-direction:column;gap:2px;min-width:80px;flex:0 1 80px;">
                    <span style="font-size:10px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em;">CB/LB/Hole</span>
                    ${mkInp('ctn_cb', 'e.g. CB 60')}
                </div>
            </div>
            <div style="margin-top:8px;padding:6px 10px;background:#e8f4ff;border-radius:6px;font-size:11px;display:flex;gap:20px;flex-wrap:wrap;">
                <div><strong style="color:#475569;">Item Desc:</strong> <span id="ctn_desc_preview_${rid}" style="color:#1e293b;">—</span></div>
                <div><strong style="color:#475569;">Seg-2:</strong> <span id="ctn_seg2_preview_${rid}" style="color:#6366f1;">—</span></div>
            </div>
        </td>`;
        mainRow?.after(detailTr);
    } else if (cfg.s1.type === 'grade-select') {
        const grades = (typeof PAPER_GRADES !== 'undefined') ? Object.keys(PAPER_GRADES) : [];
        s1td.innerHTML = `<select id="grade_${rid}" style="min-width:100px;" onchange="onGradeChange('${rid}')">
            <option value="">— Grade —</option>
            ${grades.map(g => `<option value="${g}">${g} (${PAPER_GRADES[g].ply})</option>`).join('')}
        </select>`;
        if (s2td) s2td.innerHTML = `<select id="paperComb_${rid}" style="min-width:140px;">
            <option value="">— Combination —</option>
        </select>`;
        if (s3td) s3td.innerHTML = '';
        if (s4td) s4td.innerHTML = '';
    } else {
        s1td.innerHTML = cfg.s1.ph ? `<input id="spec1val_${rid}" placeholder="${cfg.s1.ph}" style="min-width:95px;">` : '';
        if (s2td) s2td.innerHTML = cfg.s2?.ph ? `<input id="spec2val_${rid}" placeholder="${cfg.s2.ph}" style="min-width:95px;">` : '';
        if (s3td) s3td.innerHTML = cfg.s3?.ph ? `<input id="spec3val_${rid}" placeholder="${cfg.s3.ph}" style="min-width:95px;">` : '';
        if (s4td) s4td.innerHTML = cfg.s4?.ph ? `<input id="spec4val_${rid}" placeholder="${cfg.s4.ph}" style="min-width:95px;">` : '';
    }
}

function onGradeChange(rid) {
    const grade = document.getElementById('grade_' + rid)?.value;
    const s2 = document.getElementById('paperComb_' + rid);
    if (!s2) return;
    const combs = (typeof PAPER_GRADES !== 'undefined' && grade && PAPER_GRADES[grade]?.combinations) || [];
    s2.innerHTML = '<option value="">— Combination —</option>' +
        combs.map(c => `<option value="${c}">${c}</option>`).join('');
}

function genCartonDesc(rid) {
    const g = id => document.getElementById(id + '_' + rid)?.value?.trim() || '';
    const product = g('ctn_product');
    const print   = g('ctn_print');
    const ply     = g('ctn_ply');
    const paper   = g('ctn_paper');
    const len     = g('ctn_len');
    const wid     = g('ctn_wid');
    const hgt     = g('ctn_hgt');
    const uom     = g('ctn_uom') || 'CM';

    // Build dimension string and auto-fill artSize
    const dims   = [len, wid, hgt].filter(Boolean).join('x');
    const dimStr = dims ? dims + ' ' + uom : '';
    const artEl  = document.getElementById('artSize_' + rid);
    if (artEl) artEl.value = dimStr;

    // Item Desc: "[Ply] [PrintStatus] [ProductName] [L]x[W]x[H] [UOM]"
    // Example: "3 Ply Printed Carton 58x29x27.5 CM"
    const itemDesc = [ply, print, product, dimStr].filter(Boolean).join(' ');

    // Seg-2: "[ProductName] [Ply] [PaperType] [PrintStatus] [L]x[W]x[H] [UOM]"
    // Example: "Carton 3 Ply Both Side Virgin Printed 58x29x27.5 CM"
    const seg2 = [product, ply, paper, print, dimStr].filter(Boolean).join(' ');

    const nameEl = document.getElementById('itemName_' + rid);
    if (nameEl) { nameEl.value = itemDesc; nameEl.title = 'Seg-2: ' + seg2; nameEl.dataset.seg2 = seg2; }

    const dp = document.getElementById('ctn_desc_preview_' + rid);
    const sp = document.getElementById('ctn_seg2_preview_' + rid);
    if (dp) dp.textContent = itemDesc || '—';
    if (sp) sp.textContent = seg2 || '—';
}

function addPoBlock() {
    poCount++;
    const pid = 'po' + poCount;

    const div = document.createElement('div');
    div.className = 'po-block';
    div.id = 'block_' + pid;

    div.innerHTML = `
    <div class="po-block-hdr" onclick="togglePo('${pid}')">
        <div style="display:flex;align-items:center;gap:10px;">
            <span class="po-num-chip">PO ${poCount}</span>
            <span class="po-block-label" id="poLabel_${pid}">New Purchase Order</span>
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
            <span class="po-badge" id="poBadge_${pid}">$0.00 · 0 pcs</span>
            ${poCount > 1 ? `<button class="ghost-btn" style="padding:3px 10px;font-size:11px;color:#f87171;border-color:#fca5a5;"
                onclick="event.stopPropagation();removePo('${pid}')">✕ Remove</button>` : ''}
            <span class="po-chevron" id="chevron_${pid}">▲</span>
        </div>
    </div>

    <div class="po-body" id="body_${pid}">
        <div class="form-grid">
            <div class="field span-4">
                <label>PO Number</label>
                <input id="poNum_${pid}" placeholder="e.g. LIZ-LO-CTN-26020242"
                       oninput="document.getElementById('poLabel_${pid}').textContent=this.value||'New Purchase Order'">
            </div>
            <div class="field span-4">
                <label>End Buyer</label>
                <input id="poEndBuyer_${pid}" placeholder="e.g. Target Australia">
            </div>
            <div class="field span-4">
                <label>TRIMS / IPO No.</label>
                <input id="poTrims_${pid}" placeholder="e.g. TRIMS/IPO/26/8799">
            </div>
            <div class="field span-3">
                <label>Design</label>
                <input id="poDesign_${pid}" placeholder="e.g. Nova Stonewash">
            </div>
            <div class="field span-3">
                <label>Order No</label>
                <input id="poOrderNo_${pid}" placeholder="e.g. 2637543">
            </div>
            <div class="field span-3">
                <label>Type</label>
                <input id="poType_${pid}" placeholder="e.g. Quilt Cover Set">
            </div>
            <div class="field span-2">
                <label>Delivery Date</label>
                <input type="date" id="poDelivery_${pid}">
            </div>
            <div class="field span-1" style="align-self:end;padding-bottom:6px;">
                <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                    <input type="checkbox" id="poArl_${pid}" style="width:auto;"> Without ARL
                </label>
            </div>
        </div>

        <!-- Item lines -->
        <div style="display:flex;align-items:center;justify-content:space-between;margin:14px 0 6px;">
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;">
                Item Lines
            </div>
            <button class="ghost-btn" style="padding:3px 12px;font-size:11px;" onclick="addRow('${pid}')">+ Add Row</button>
        </div>
        <div style="overflow-x:auto;">
            <table class="po-table" id="table_${pid}">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product Line</th>
                        <th>Item Name</th>
                        <th>Art / Size</th>
                        <th id="th_spec1_${pid}">Spec 1</th>
                        <th id="th_spec2_${pid}">Spec 2</th>
                        <th id="th_spec3_${pid}">Spec 3</th>
                        <th id="th_spec4_${pid}">Spec 4</th>
                        <th>Qty</th>
                        <th>Unit</th>
                        <th>Unit Price</th>
                        <th style="color:#92400e;background:#fff8f0;">Revised</th>
                        <th>Amount</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="tbody_${pid}"></tbody>
                <tfoot>
                    <tr>
                        <td colspan="8" style="text-align:right;color:#64748b;">Subtotal</td>
                        <td id="totQty_${pid}" style="font-weight:800;color:#4f46e5;">0</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td id="totVal_${pid}" style="font-weight:800;color:#4f46e5;">$0.00</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>`;

    document.getElementById('poBlocksContainer').appendChild(div);
    addRow(pid);
    updateSummary();
}

function togglePo(pid) {
    const body = document.getElementById('body_'    + pid);
    const chev = document.getElementById('chevron_' + pid);
    const open = body.style.display !== 'none';
    body.style.display = open ? 'none' : 'block';
    chev.classList.toggle('closed', open);
}

function removePo(pid) {
    if (!confirm('Remove this PO?')) return;
    document.getElementById('block_' + pid)?.remove();
    delete rowCtrs[pid];
    updateSummary();
}

function addRow(pid) {
    if (!rowCtrs[pid]) rowCtrs[pid] = 0;
    rowCtrs[pid]++;
    const sl  = rowCtrs[pid];
    const rid = pid + '_' + sl;

    const tr = document.createElement('tr');
    tr.id = 'row_' + rid;
    tr.innerHTML = `
        <td style="text-align:center;color:#94a3b8;font-weight:700;min-width:28px;">${sl}</td>
        <td>
            <select id="prodLine_${rid}" style="min-width:120px;" onchange="onProdLineChange('${rid}')">
                <option value="">— Line —</option>
            </select>
        </td>
        <td id="itemNameTd_${rid}">
            <select id="itemName_${rid}" style="min-width:160px;" onchange="onItemNameChange('${rid}')">
                <option value="">— Item —</option>
            </select>
        </td>
        <td><input placeholder="e.g. 62x54x31 CM"     style="min-width:110px;" id="artSize_${rid}"></td>
        <td id="spec1_${rid}" style="min-width:100px;"></td>
        <td id="spec2_${rid}" style="min-width:100px;"></td>
        <td id="spec3_${rid}" style="min-width:100px;"></td>
        <td id="spec4_${rid}" style="min-width:100px;"></td>
        <td><input type="number" placeholder="0" min="0" style="min-width:70px;" id="qty_${rid}" oninput="calcPoTotal('${pid}')"></td>
        <td><input placeholder="pcs"                  style="min-width:55px;"  id="unit_${rid}"></td>
        <td><input type="number" placeholder="0.0000" step="0.0001" style="min-width:90px;" id="unitPrc_${rid}" oninput="calcPoTotal('${pid}')"></td>
        <td><input readonly id="revPrc_${rid}" style="min-width:90px;background:#fff8f0;color:#92400e;font-weight:700;cursor:default;" placeholder="—" tabindex="-1"></td>
        <td><input readonly id="totPrc_${rid}" style="min-width:90px;background:#f8fafc;font-weight:700;color:#4f46e5;cursor:default;"></td>
        <td><button class="po-del-row" onclick="delRow('${pid}','${rid}')">×</button></td>`;
    document.getElementById('tbody_' + pid).appendChild(tr);
    // Populate product line dropdown from item master
    populateProdLineSelect(rid);
}

// ── Item master dropdowns ────────────────────────────────────────────────────
let _itemMasterCache = null;

async function getItemMaster() {
    if (_itemMasterCache) return _itemMasterCache;
    try {
        const res = await fetch(APP_BASE + '/api/customers.php'); // fallback
        // Use item master from page if available (script.js itemMaster array)
        if (window.itemMaster && Array.isArray(window.itemMaster)) {
            _itemMasterCache = window.itemMaster;
            return _itemMasterCache;
        }
    } catch(e) {}
    return window.itemMaster || [];
}

async function populateProdLineSelect(rid) {
    const sel = document.getElementById('prodLine_' + rid);
    if (!sel) return;
    // Wait for DOMContentLoaded so item-data.js (loaded by footer.php) is available
    await new Promise(r => {
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', r);
        else r();
    });
    let lines;
    if (typeof PRODUCT_LINE_ITEMS !== 'undefined') {
        lines = Object.keys(PRODUCT_LINE_ITEMS).sort();
    } else {
        const items = window.itemMaster || [];
        lines = [...new Set(items.map(i => i.productLine || i.name).filter(Boolean))].sort();
    }
    sel.innerHTML = '<option value="">— Line —</option>' + lines.map(l => `<option value="${l}">${l}</option>`).join('');
}

function onProdLineChange(rid) {
    const line = document.getElementById('prodLine_' + rid)?.value;
    // Update spec fields first — handles Carton detail row inject/cleanup
    updateRowSpecs(rid, line);
    // Populate item name dropdown only for non-Carton (Carton uses auto-generated input)
    const itemSel = document.getElementById('itemName_' + rid);
    if (itemSel && itemSel.tagName === 'SELECT') {
        let itemNames = [];
        if (typeof PRODUCT_LINE_ITEMS !== 'undefined' && PRODUCT_LINE_ITEMS[line]) {
            itemNames = PRODUCT_LINE_ITEMS[line];
        } else {
            itemNames = (window.itemMaster || [])
                .filter(i => (i.productLine || i.name) === line)
                .map(i => i.itemName || i.name);
        }
        itemSel.innerHTML = '<option value="">— Item —</option>' +
            itemNames.map(n => `<option value="${n}">${n}</option>`).join('');
    }
    const el = id => document.getElementById(id + '_' + rid);
    if (el('unitPrc')) el('unitPrc').value = '';
}

function onItemNameChange(rid) {
    const sel = document.getElementById('itemName_' + rid);
    if (!sel) return;
    const opt = sel.selectedOptions[0];
    const price = opt?.dataset?.price;
    if (price) {
        const p = document.getElementById('unitPrc_' + rid);
        if (p && !p.value) p.value = parseFloat(price).toFixed(4);
    }
}

function delRow(pid, rid) {
    document.getElementById('ctn_detail_' + rid)?.remove();
    document.getElementById('row_' + rid)?.remove();
    calcPoTotal(pid);
}

function calcRow(rid, pid) {
    calcPoTotal(pid);
}

function calcPoTotal(pid) {
    let tq = 0, tv = 0;
    document.querySelectorAll('#tbody_' + pid + ' tr').forEach(tr => {
        const rid = tr.id.replace('row_', '');
        const q   = parseFloat(document.getElementById('qty_'     + rid)?.value || 0) || 0;
        const p   = parseFloat(document.getElementById('unitPrc_' + rid)?.value || 0) || 0;
        const t   = q * p;
        const el  = document.getElementById('totPrc_' + rid);
        if (el) el.value = (q > 0 || p > 0) ? t.toFixed(2) : '';
        tq += q; tv += t;
    });
    const qEl = document.getElementById('totQty_' + pid);
    const vEl = document.getElementById('totVal_' + pid);
    const bEl = document.getElementById('poBadge_' + pid);
    if (qEl) qEl.textContent = tq.toLocaleString();
    if (vEl) vEl.textContent = '$' + tv.toFixed(2);
    if (bEl) bEl.textContent = '$' + tv.toFixed(2) + ' · ' + tq.toLocaleString() + ' pcs';
    updateSummary();
}

function updateSummary() {
    const blocks = document.querySelectorAll('.po-block');
    let tq = 0, tv = 0;
    blocks.forEach(b => {
        const pid = b.id.replace('block_', '');
        tq += parseFloat((document.getElementById('totQty_' + pid)?.textContent || '').replace(/,/g,'')) || 0;
        tv += parseFloat((document.getElementById('totVal_' + pid)?.textContent || '').replace('$','')) || 0;
    });
    document.getElementById('sumPoCount').textContent  = blocks.length;
    document.getElementById('sumTotalQty').textContent = tq.toLocaleString();
    document.getElementById('sumTotalVal').textContent = '$' + tv.toFixed(2);
}

function collectPoData() {
    const pos = [];
    document.querySelectorAll('.po-block').forEach(block => {
        const pid = block.id.replace('block_', '');
        const rows = [];
        document.querySelectorAll('#tbody_' + pid + ' tr[id^="row_"]').forEach(tr => {
            const rid = tr.id.replace('row_', '');
            const prodLine = document.getElementById('prodLine_' + rid)?.value || '';
            const nameEl  = document.getElementById('itemName_' + rid);
            const itemName = nameEl?.value || '';
            const seg2     = nameEl?.dataset?.seg2 || '';
            const g = id => document.getElementById(id + '_' + rid)?.value || '';
            let spec1='', spec2='', spec3='', spec4='', cartonExtra = null, detailExtra = null;
            if (prodLine === 'Carton') {
                spec1 = g('ctn_product');
                spec2 = g('ctn_ply');
                spec3 = g('ctn_print');
                spec4 = g('ctn_paper');
                cartonExtra = {
                    printSide: g('ctn_printside'), length: g('ctn_len'), width: g('ctn_wid'),
                    height: g('ctn_hgt'), fold: g('ctn_fold'), uom: g('ctn_uom'),
                    flute: g('ctn_flute'), dieCut: g('ctn_diecut'), grade: g('ctn_grade'), cb: g('ctn_cb'),
                };
            } else if (PRODUCT_DETAIL_CONFIGS[prodLine]) {
                detailExtra = {};
                PRODUCT_DETAIL_CONFIGS[prodLine].fields.forEach((f, i) => {
                    const val = g(f.id);
                    detailExtra[f.id] = val;
                    if (i === 0) spec1 = val;
                    if (i === 1) spec2 = val;
                    if (i === 2) spec3 = val;
                    if (i === 3) spec4 = val;
                });
            } else {
                spec1 = g('grade')    || g('spec1val');
                spec2 = g('paperComb') || g('spec2val');
                spec3 = g('spec3val');
                spec4 = g('spec4val');
            }
            const rowData = {
                prodLine, itemName, seg2,
                artSize: g('artSize'),
                spec1, spec2, spec3, spec4,
                qty:     g('qty'),
                unit:    g('unit'),
                unitPrc: g('unitPrc'),
            };
            if (cartonExtra) rowData.cartonExtra = cartonExtra;
            if (detailExtra) rowData.detailExtra = detailExtra;
            rows.push(rowData);
        });
        pos.push({
            poNum:      document.getElementById('poNum_'      + pid)?.value || '',
            endBuyer:   document.getElementById('poEndBuyer_' + pid)?.value || '',
            trims:      document.getElementById('poTrims_'    + pid)?.value || '',
            design:     document.getElementById('poDesign_'   + pid)?.value || '',
            orderNo:    document.getElementById('poOrderNo_'  + pid)?.value || '',
            type:       document.getElementById('poType_'     + pid)?.value || '',
            delivery:   document.getElementById('poDelivery_' + pid)?.value || '',
            withoutArl: document.getElementById('poArl_'      + pid)?.checked || false,
            rows,
        });
    });
    return pos;
}

async function saveIntake() {
    const sel = document.getElementById('intakeCustomer');
    const customer = sel?.selectedOptions?.[0]?.value
        ? sel.selectedOptions[0].text.trim()
        : '';
    const intakeDate = document.getElementById('intakeDate')?.value;
    if (!customer || customer === '— Select Customer —') {
        alert('Please select a Customer before saving.');
        sel?.focus();
        return false;
    }
    if (!intakeDate) {
        alert('Please enter an Intake Date before saving.');
        document.getElementById('intakeDate')?.focus();
        return false;
    }

    let orderId = window.getCurrentOrderId ? window.getCurrentOrderId() : '';
    if (!orderId) {
        try {
            const r   = await fetch(APP_BASE + '/api/order_lookup.php', { method: 'POST' });
            const res = await r.json();
            if (!res.ok) { alert('Could not create order. Try again.'); return false; }
            orderId = res.order_id;
            sessionStorage.setItem('ats_current_order_id', orderId);
            const display = document.getElementById('oidDisplay');
            if (display) display.textContent = orderId;
        } catch (e) { alert('Server error.'); return false; }
    }

    const pos = collectPoData();
    const payload = {
        order_id:     orderId,
        page_name:    'marketing-intake',
        customer,
        salesPerson:  document.getElementById('intakeSalesperson')?.value?.trim() || '',
        intakeDate,
        subject:      document.getElementById('intakeSubject')?.value?.trim()      || '',
        paperQuality: document.getElementById('intakePaperQuality')?.value?.trim() || '',
        poCount:      pos.length,
        pos,
    };

    try {
        const r   = await fetch(APP_BASE + '/api/save_page.php', {
            method: 'POST', headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        });
        const res = await r.json();
        if (res.error) { alert('Save failed: ' + res.error); return false; }
        document.getElementById('intakeStatus').textContent = 'Saved ✓';
        document.getElementById('intakeStatus').style.color = '#22c55e';
        alert('✓ Intake saved successfully!');
        const custEl = document.getElementById('oidCustomer');
        const stepEl = document.getElementById('oidStep');
        const rowEl  = document.getElementById('oidStatusRow');
        if (custEl) custEl.textContent = customer;
        if (stepEl) stepEl.textContent = 'Step: Marketing Intake';
        if (rowEl)  rowEl.style.display = 'flex';
        return true;
    } catch (e) { alert('Server error saving intake.'); return false; }
}

function clearAll(skipConfirm) {
    if (!skipConfirm && !confirm('Clear all POs and reset the intake?')) return;
    document.getElementById('poBlocksContainer').innerHTML = '';
    poCount = 0; rowCtrs = {};
    updateSummary();
    addPoBlock();
    document.getElementById('intakeStatus').textContent = 'Draft';
    document.getElementById('intakeStatus').style.color = '#f59e0b';
}

async function sendToCosting() {
    const saved = await saveIntake();
    if (!saved) return;

    const orderId = window.getCurrentOrderId ? window.getCurrentOrderId() : '';
    try {
        const r   = await fetch(APP_BASE + '/api/orders.php?id=' + encodeURIComponent(orderId) + '&step=costing-review', { method: 'PUT' });
        const res = await r.json();
        if (!res.ok) { alert('Could not advance step. Try again.'); return; }
    } catch (e) { alert('Server error.'); return; }

    document.getElementById('intakeStatus').textContent = 'Sent to Costing ✓';
    document.getElementById('intakeStatus').style.color = '#6366f1';
    if (confirm('Intake submitted! Go to Costing Review now?')) {
        window.location.href = APP_BASE + '/pages/costing-review.php';
    }
}

// Init
addPoBlock();
// Always set intake date to today (local date, never restored from draft)
document.addEventListener('DOMContentLoaded', function() {
    const now = new Date();
    const today = now.getFullYear() + '-' + String(now.getMonth()+1).padStart(2,'0') + '-' + String(now.getDate()).padStart(2,'0');
    const el = document.getElementById('intakeDate');
    if (el) el.value = today;
});

// ── Order ID integration ──────────────────────────────────────────────────
function _setTodayDate() {
    const now = new Date();
    const today = now.getFullYear() + '-' + String(now.getMonth()+1).padStart(2,'0') + '-' + String(now.getDate()).padStart(2,'0');
    const el = document.getElementById('intakeDate');
    if (el) el.value = today;
}
window.onOrderLoad = function(res) {
    const d = res.pages?.['marketing-intake'];

    // Step gating: show banner only when order has genuinely moved past intake
    // 'sales' is a legacy artifact from old script.js — ignore it
    const currentStep = res.order?.current_step || 'marketing-intake';
    const INTAKE_DONE_STEPS = ['costing-review','marketing','lc','exchange','commercial','packing','delivery','truck','origin','beneficiary','forwarding','bank-forwarding','po-status'];
    const banner = document.getElementById('stepLockedBanner');
    if (banner) {
        const pastIntake = INTAKE_DONE_STEPS.includes(currentStep);
        banner.style.display = pastIntake ? 'flex' : 'none';
        if (pastIntake) {
            const stepLabel = currentStep.replace(/-/g,' ').replace(/\b\w/g,c=>c.toUpperCase());
            const titleEl = document.getElementById('stepLockedBannerTitle');
            const stepEl  = document.getElementById('stepLockedBannerStep');
            const linkEl  = document.getElementById('stepLockedBannerLink');
            if (titleEl) titleEl.textContent = '⚠ This order has already moved past Marketing Intake.';
            if (stepEl)  stepEl.textContent = stepLabel;
            if (linkEl) {
                linkEl.textContent = 'Go to ' + stepLabel + ' →';
                linkEl.onclick = () => window.location.href = APP_BASE + '/pages/' + currentStep + '.php';
            }
        }
    }

    if (!d) return;

    // Restore customer dropdown (match by text; may need to wait for dropdown to load)
    if (d.customer) {
        const sel = document.getElementById('intakeCustomer');
        if (sel && sel.options.length > 1) {
            for (const opt of sel.options) {
                if (opt.text.trim() === d.customer) { sel.value = opt.value; break; }
            }
        } else {
            window._pendingCustomer = d.customer;
        }
    }
    if (d.salesPerson) { const el = document.getElementById('intakeSalesperson');   if (el) el.value = d.salesPerson; }
    _setTodayDate(); // always today — never restored from draft
    if (d.subject)     { const el = document.getElementById('intakeSubject');        if (el) el.value = d.subject; }
    if (d.paperQuality){ const el = document.getElementById('intakePaperQuality');   if (el) el.value = d.paperQuality; }

    // Revised prices from costing review (flat row index → price)
    const revisedByRow = res.pages?.['costing-review']?.revisedByRow || {};

    // Rebuild PO blocks with all row data
    if (d.pos && d.pos.length) {
        document.getElementById('poBlocksContainer').innerHTML = '';
        poCount = 0; rowCtrs = {};
        let flatRowNum = 0;
        d.pos.forEach(po => {
            addPoBlock();
            const pid = 'po' + poCount;
            const setVal = (id, val) => { const el = document.getElementById(id); if (el && val) el.value = val; };
            if (po.poNum) {
                setVal('poNum_' + pid, po.poNum);
                const lbl = document.getElementById('poLabel_' + pid);
                if (lbl) lbl.textContent = po.poNum;
            }
            setVal('poEndBuyer_' + pid, po.endBuyer);
            setVal('poTrims_'    + pid, po.trims);
            setVal('poDesign_'   + pid, po.design);
            setVal('poOrderNo_'  + pid, po.orderNo);
            setVal('poType_'     + pid, po.type);
            setVal('poDelivery_' + pid, po.delivery);
            const arlEl = document.getElementById('poArl_' + pid);
            if (arlEl) arlEl.checked = !!po.withoutArl;

            // Clear the default empty row, then repopulate
            document.getElementById('tbody_' + pid).innerHTML = '';
            rowCtrs[pid] = 0;
            const savedRows = po.rows?.length ? po.rows : [{}];
            savedRows.forEach(row => {
                addRow(pid);
                flatRowNum++;
                const rid = pid + '_' + rowCtrs[pid];
                // Common fields (no dependencies)
                setVal('artSize_' + rid, row.artSize);
                setVal('qty_'     + rid, row.qty);
                setVal('unit_'    + rid, row.unit);
                setVal('unitPrc_' + rid, row.unitPrc);
                // Show revised price from costing if present
                const revPrc = revisedByRow[flatRowNum];
                if (revPrc) { const revEl = document.getElementById('revPrc_' + rid); if (revEl) revEl.value = revPrc; }
                // Restore cascading selects: populate product line → inject spec fields → restore spec values
                populateProdLineSelect(rid).then(() => {
                    setVal('prodLine_' + rid, row.prodLine);
                    onProdLineChange(rid); // injects spec cells for this product type
                    setTimeout(() => {
                        setVal('itemName_' + rid, row.itemName);
                        // Carton: set grade → populate combinations → set combination
                        const s1 = row.spec1 || row.grade    || '';
                        const s2 = row.spec2 || row.paperComb || '';
                        if (row.prodLine === 'Carton') {
                            const gradeEl = document.getElementById('grade_' + rid);
                            if (gradeEl) {
                                gradeEl.value = s1;
                                onGradeChange(rid);
                                setTimeout(() => { const el = document.getElementById('paperComb_' + rid); if (el) el.value = s2; }, 30);
                            }
                        } else {
                            const el1 = document.getElementById('spec1val_' + rid);
                            const el2 = document.getElementById('spec2val_' + rid);
                            const el3 = document.getElementById('spec3val_' + rid);
                            const el4 = document.getElementById('spec4val_' + rid);
                            if (el1) el1.value = s1;
                            if (el2) el2.value = s2;
                            if (el3) el3.value = row.spec3 || '';
                            if (el4) el4.value = row.spec4 || '';
                        }
                    }, 50);
                });
                calcPoTotal(pid);
            });
        });
        updateSummary();
    }

    document.getElementById('intakeStatus').textContent = 'Loaded ✓';
    document.getElementById('intakeStatus').style.color = '#22c55e';
};

window.onNewOrder = function(orderId) {
    clearAll(true);
    const banner = document.getElementById('stepLockedBanner');
    if (banner) banner.style.display = 'none';
    const stepEl = document.getElementById('stepLockedBannerStep');
    if (stepEl) stepEl.textContent = 'Marketing Intake';
    const statusEl = document.getElementById('intakeStatus');
    if (statusEl) {
        statusEl.textContent = 'Draft';
        statusEl.style.color = '#f59e0b';
    }
};

// Populate customer dropdown from DB
(async function loadCustomerDropdown() {
    try {
        const res  = await fetch(APP_BASE + '/api/customers.php');
        const list = await res.json();
        const sel  = document.getElementById('intakeCustomer');
        if (!sel || !Array.isArray(list)) return;
        // Show only approved bulk-production customers in order flow
        const pending = ['sales_person', 'team_leader'];
        list.filter(c => {
            if (pending.includes(c.stage)) return false;
            let extra = {};
            try { extra = typeof c.extra_data === 'string' ? JSON.parse(c.extra_data || '{}') : (c.extra_data || {}); } catch (_) {}
            return (extra.customerCategory || '') === 'Bulk Production';
        }).forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = c.company_name;
            sel.appendChild(opt);
        });
        // Restore pending customer selection after dropdown is populated
        if (window._pendingCustomer) {
            for (const opt of sel.options) {
                if (opt.text.trim() === window._pendingCustomer) { sel.value = opt.value; break; }
            }
            window._pendingCustomer = null;
        }
    } catch(e) { console.warn('Could not load customers:', e); }
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>

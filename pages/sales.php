<?php
$pageTitle   = 'PI';
$activePage  = 'sales';
$navSection  = 'order';
include __DIR__ . '/../includes/header.php';
?>

<style>
/* â”€â”€ PI page â”€â”€ */
.pi-summary-bar {
    background: linear-gradient(135deg,#1e1e2e,#2d2d44);
    border-radius: 14px; padding: 16px 24px;
    display: flex; gap: 32px; flex-wrap: wrap;
    align-items: center; margin-bottom: 16px; color: #fff;
}
.pi-sum-badge {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(99,102,241,.35); border-radius: 10px;
    padding: 6px 16px; font-size: 13px; font-weight: 700;
}
.pi-sum-badge span { font-size: 18px; font-weight: 800; color: #a5b4fc; }
.pi-sum-item { text-align: center; }
.pi-sum-num { font-size: 20px; font-weight: 800; color: #a5b4fc; }
.pi-sum-lbl { font-size: 10px; color: #94a3b8; text-transform: uppercase; letter-spacing: .05em; margin-top: 2px; }

/* Master PI */
.mpi-bar {
    background: #fff; border: 1.5px solid #e0e3ff;
    border-radius: 14px; padding: 14px 20px;
    display: flex; align-items: center; justify-content: space-between;
    gap: 12px; flex-wrap: wrap; margin-bottom: 16px;
    box-shadow: 0 2px 8px rgba(99,102,241,.05);
}
.mpi-bar-left { display: flex; align-items: center; gap: 12px; }
.mpi-icon {
    width: 38px; height: 38px; border-radius: 10px;
    background: linear-gradient(135deg,#6366f1,#4f46e5);
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; flex-shrink: 0;
}
.mpi-title { font-size: 14px; font-weight: 700; color: #1e1e2e; }
.mpi-sub   { font-size: 11px; color: #94a3b8; margin-top: 1px; }

/* Saved PI list */
.saved-pi-list { margin: 8px 0; }
/* Item selector groups */
.mpi-grp { border:1.5px solid #e0e3ff; border-radius:10px; margin-bottom:10px; overflow:hidden; }
.mpi-grp-hdr {
    display:flex; align-items:center; gap:10px;
    background:#f1f0ff; padding:8px 12px;
    border-bottom:1px solid #e0e3ff;
}
.mpi-grp-hdr input[type=checkbox] { width:15px; height:15px; accent-color:#6366f1; flex-shrink:0; cursor:pointer; }
.mpi-grp-pi  { font-size:12px; font-weight:800; color:#4f46e5; min-width:90px; }
.mpi-grp-ref { font-size:11px; color:#64748b; flex:1; }
.mpi-item-row {
    display:grid; grid-template-columns:18px 1fr 52px 70px 70px 80px;
    gap:6px; align-items:center;
    padding:5px 12px; border-bottom:1px solid #f1f5f9; font-size:12px;
}
.mpi-item-row:last-child { border-bottom:none; }
.mpi-item-row input[type=checkbox] { width:14px; height:14px; accent-color:#6366f1; cursor:pointer; }
.mpi-item-desc { color:#1e1e2e; }
.mpi-item-ply,.mpi-item-qty,.mpi-item-prc,.mpi-item-tot { text-align:right; color:#475569; }
.mpi-item-tot { font-weight:700; color:#1e1e2e; }

/* Master PI modal */
.mpi-modal-shell {
    display: none; position: fixed; inset: 0;
    background: rgba(10,10,30,.5); backdrop-filter: blur(4px);
    z-index: 9999; align-items: center; justify-content: center;
}
.mpi-modal-shell.open { display: flex; }
.mpi-modal {
    background: #fff; border-radius: 18px; padding: 0;
    width: 100%; max-width: 780px;
    max-height: 90vh; overflow: hidden;
    box-shadow: 0 24px 80px rgba(0,0,0,.25);
    display: flex; flex-direction: column;
}
.mpi-modal-head {
    background: linear-gradient(135deg,#1e1e2e,#2d2d44);
    padding: 20px 26px;
    display: flex; align-items: center; justify-content: space-between;
    flex-shrink: 0;
}
.mpi-modal-body { padding: 24px 26px; overflow-y: auto; flex: 1; }
.mpi-modal-foot {
    padding: 16px 26px; border-top: 1.5px solid #e0e3ff;
    display: flex; gap: 10px; justify-content: flex-end;
    flex-shrink: 0; background: #f8f9ff;
}
.mpi-combined-po {
    border: 1.5px solid #e0e3ff; border-radius: 10px;
    padding: 12px 16px; margin-bottom: 10px;
}
.mpi-combined-po-hdr {
    display: flex; align-items: center; gap: 8px; margin-bottom: 6px;
}
.mpi-pi-tag {
    font-size: 10px; font-weight: 800; background: #ede9fe; color: #4f46e5;
    padding: 2px 8px; border-radius: 999px; text-transform: uppercase;
}

/* Order PI Overview */
.order-pi-overview { background:#fff; border:1.5px solid #e0e3ff; border-radius:14px; padding:16px 20px; margin-bottom:16px; }
.opo-title { font-size:13px; font-weight:800; color:#1e1e2e; margin-bottom:12px; display:flex; align-items:center; gap:8px; }
.opo-list  { display:flex; flex-direction:column; gap:8px; }
.opo-row   { display:flex; align-items:center; gap:10px; border-radius:10px; padding:9px 14px; flex-wrap:wrap; }
.opo-row.opo-master     { background:#ede9fe; border:1.5px solid #c4b5fd; }
.opo-row.opo-included   { background:#f0fdf4; border:1.5px solid #bbf7d0; }
.opo-row.opo-standalone { background:#fef9c3; border:1.5px solid #fde68a; cursor:pointer; }
.opo-row.opo-standalone:hover { filter:brightness(.97); }
.opo-badge            { font-size:10px; font-weight:800; padding:2px 8px; border-radius:999px; flex-shrink:0; white-space:nowrap; }
.opo-badge.b-master   { background:#7c3aed; color:#fff; }
.opo-badge.b-included { background:#16a34a; color:#fff; }
.opo-badge.b-standalone { background:#d97706; color:#fff; }
.opo-num      { font-size:13px; font-weight:800; color:#1e1e2e; min-width:120px; }
.opo-meta     { font-size:12px; color:#64748b; flex:1; }
.opo-includes { font-size:11px; color:#7c3aed; margin-top:2px; }
.opo-val      { font-size:13px; font-weight:700; color:#1e1e2e; white-space:nowrap; }
/* PI item selector (inline below each PI card) */
.opo-items-wrap { border:1.5px solid #fde68a; border-top:none; border-radius:0 0 10px 10px; margin-top:-6px; background:#fffdf0; overflow:hidden; }
.opo-ref-line   { padding:4px 14px 3px; font-size:10px; color:#92400e; background:#fffbeb; border-bottom:1px solid #fef3c7; }
.opo-item-row   { display:grid; grid-template-columns:16px 1fr 55px 65px 65px 78px; gap:6px; align-items:center; padding:5px 14px; font-size:11.5px; border-bottom:1px solid #fef9c3; }
.opo-item-row:last-child { border-bottom:none; }
.opo-item-row input[type=checkbox] { width:13px; height:13px; accent-color:#6366f1; cursor:pointer; }
.opo-item-hdr  { display:grid; grid-template-columns:16px 1fr 55px 65px 65px 78px; gap:6px; padding:4px 14px; font-size:10px; font-weight:700; color:#92400e; background:#fff8dc; border-bottom:1px solid #fde68a; }
/* Master PI basket bar */
#mpiBasket { display:none; margin-top:14px; background:linear-gradient(135deg,#1e1e2e,#312e81); color:#fff; border-radius:10px; padding:11px 18px; align-items:center; gap:18px; flex-wrap:wrap; }
#mpiBasket strong { color:#a5b4fc; }
.mpi-basket-btn { margin-left:auto; background:#6366f1; color:#fff; border:none; border-radius:8px; padding:7px 18px; font-size:12px; font-weight:700; cursor:pointer; white-space:nowrap; }
.mpi-basket-btn:hover { background:#4f46e5; }

/* PI header card */
.pi-hdr-card {
    background: #fff; border: 1.5px solid #e8eaff;
    border-radius: 14px; padding: 18px 22px;
    margin-bottom: 18px;
    box-shadow: 0 2px 8px rgba(99,102,241,.05);
}

/* PO block */
.po-block {
    background: #fff;
    border: 2px solid #e0e3ff;
    border-radius: 14px;
    margin-bottom: 20px;
    overflow: hidden;
    transition: border-color .18s, box-shadow .18s;
}
.po-block:focus-within { border-color: #6366f1; box-shadow: 0 4px 16px rgba(99,102,241,.10); }

.po-block-hdr {
    display: flex; align-items: center; justify-content: space-between;
    padding: 11px 18px; background: #f8f9ff;
    border-bottom: 1.5px solid #e0e3ff;
    gap: 10px; flex-wrap: wrap; cursor: pointer;
    user-select: none;
}
.po-block-hdr:hover { background: #f0f0ff; }
.po-num-chip {
    background: #6366f1; color: #fff;
    border-radius: 8px; padding: 3px 12px;
    font-size: 12px; font-weight: 800;
}
.po-block-body { padding: 18px 20px 14px; }

/* ERP banner */
.erp-banner {
    background: #f0f4ff; border: 1.5px solid #c7d2fe;
    border-radius: 10px; padding: 10px 14px;
    font-size: 12px; margin-bottom: 14px;
}
.erp-banner strong { color: #4f46e5; font-size: 11px; text-transform: uppercase; letter-spacing: .06em; display: block; margin-bottom: 2px; }

/* ERP search bar */
.erp-search-row {
    display: flex; gap: 8px; margin-bottom: 12px; align-items: center;
}
.erp-search-row input {
    flex: 1; padding: 8px 14px; border: 1.5px solid #e2e8f0;
    border-radius: 9px; font-size: 13px; outline: none;
    transition: border-color .15s;
}
.erp-search-row input:focus { border-color: #6366f1; }

/* Items table */
.si-table { width: 100%; border-collapse: collapse; font-size: 12px; margin-top: 10px; }
.si-table th {
    background: #f1f5f9; padding: 7px 8px; text-align: left;
    font-size: 11px; font-weight: 700; color: #64748b;
    border-bottom: 1.5px solid #e2e8f0; white-space: nowrap;
}
.si-table td { padding: 4px 5px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.si-table td input {
    width: 100%; padding: 5px 7px; border: 1.5px solid #e2e8f0;
    border-radius: 6px; font-size: 12px; outline: none;
    box-sizing: border-box; transition: border-color .15s;
}
.si-table td input:focus { border-color: #6366f1; }
.si-table td input[readonly] { background: #f8fafc; color: #64748b; }
.si-total-row td {
    background: #f8f9ff; font-weight: 700; font-size: 12px;
    padding: 8px; border-top: 2px solid #e0e3ff;
}
.si-del-btn {
    background: none; border: none; color: #f87171;
    cursor: pointer; font-size: 16px; padding: 2px 6px;
    border-radius: 6px; transition: background .15s;
}
.si-del-btn:hover { background: #fee2e2; }

/* Responsive sales layout */
#piContent,
#noOrderPrompt {
    width: 100%;
    max-width: 100%;
}
#piContent {
    min-width: 0;
}
.pi-summary-bar > div {
    min-width: 0;
}
.pi-summary-bar > div:last-child {
    margin-left: auto;
}
.mpi-bar > div:last-child {
    min-width: 0;
    justify-content: flex-end;
}
.pi-type-lbl {
    white-space: nowrap;
}
.pi-hdr-card,
.po-block,
.order-pi-overview,
#summaryBuilder,
#masterPiSelectedPanel,
#salesTermsBox {
    max-width: 100%;
}
.po-block {
    min-width: 0;
}
.po-block-hdr > div {
    min-width: 0;
}
.po-block-hdr span[id^="poLabel_"] {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.erp-search-row {
    min-width: 0;
}
.erp-search-row input {
    min-width: 180px;
}
.si-table {
    min-width: 760px;
}
.si-table th:nth-child(1),
.si-table td:nth-child(1) { width: 42px; }
.si-table th:nth-child(2),
.si-table td:nth-child(2) { min-width: 260px; }
.si-table th:nth-child(3),
.si-table td:nth-child(3),
.si-table th:nth-child(4),
.si-table td:nth-child(4),
.si-table th:nth-child(5),
.si-table td:nth-child(5),
.si-table th:nth-child(6),
.si-table td:nth-child(6) { min-width: 96px; }
.si-table td input {
    min-width: 0;
}
.opo-title {
    flex-wrap: wrap;
}
.opo-row {
    min-width: 0;
}
.opo-meta,
.opo-num {
    min-width: 0;
    overflow-wrap: anywhere;
}
.opo-items-wrap {
    overflow-x: auto;
}
.opo-item-hdr,
.opo-item-row {
    min-width: 680px;
}
.mpi-grp {
    overflow-x: auto;
}
.mpi-item-row {
    min-width: 620px;
}
#mpiBasket {
    min-width: 0;
}
#salesTermsBox {
    overflow-wrap: anywhere;
}
.term-sel {
    max-width: 100%;
}
.mpi-modal-shell {
    padding: 14px;
}
.mpi-modal {
    max-width: min(780px, calc(100vw - 28px));
}

@media (max-width: 1180px) {
    .pi-summary-bar {
        gap: 18px;
        padding: 14px 18px;
    }
    .mpi-bar {
        align-items: flex-start;
    }
    .mpi-bar > div:last-child {
        flex: 1 1 520px;
    }
    .pi-hdr-card,
    .po-block-body,
    #summaryBuilder,
    #masterPiSelectedPanel {
        padding: 16px;
    }
    #piContent .form-grid {
        gap: 12px;
    }
}

@media (max-width: 980px) {
    .pi-summary-bar {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
    .pi-summary-bar > div:last-child {
        margin-left: 0;
        text-align: left;
    }
    .mpi-bar {
        display: grid;
        grid-template-columns: 1fr;
    }
    .mpi-bar > div:last-child {
        justify-content: flex-start;
    }
    .po-block-hdr {
        align-items: flex-start;
    }
    .erp-search-row {
        flex-wrap: wrap;
    }
    .erp-search-row input {
        flex: 1 1 320px;
    }
}

@media (max-width: 760px) {
    .pi-summary-bar {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .pi-sum-badge {
        justify-content: center;
    }
    .pi-sum-item {
        text-align: left;
    }
    .mpi-bar-left {
        align-items: flex-start;
    }
    .mpi-bar > div:last-child,
    .erp-search-row,
    #piContent .page-actions-left,
    #piContent .page-actions-right {
        width: 100%;
    }
    .mpi-bar > div:last-child > *,
    .erp-search-row > *,
    #piContent .page-actions-left > *,
    #piContent .page-actions-right > * {
        flex: 1 1 100%;
    }
    .pi-type-lbl {
        justify-content: center;
    }
    .po-block-hdr {
        display: grid;
        grid-template-columns: 1fr;
    }
    .po-block-hdr > div:last-child {
        flex-wrap: wrap;
        justify-content: flex-start;
    }
    .opo-row {
        align-items: flex-start;
    }
    .opo-val {
        width: 100%;
    }
    #mpiBasket {
        flex-direction: column;
        align-items: stretch !important;
        gap: 8px;
    }
    .mpi-basket-btn {
        margin-left: 0;
        width: 100%;
    }
    .mpi-modal-head,
    .mpi-modal-body,
    .mpi-modal-foot {
        padding-left: 16px;
        padding-right: 16px;
    }
    .mpi-modal-foot {
        flex-direction: column-reverse;
    }
    .mpi-modal-foot > button {
        width: 100%;
    }
}

@media (max-width: 520px) {
    .pi-summary-bar {
        grid-template-columns: 1fr;
        border-radius: 12px;
    }
    .pi-sum-badge,
    .pi-sum-item,
    .pi-summary-bar > div:last-child {
        text-align: left;
        justify-content: flex-start;
    }
    .pi-hdr-card,
    .po-block-body,
    .order-pi-overview,
    #summaryBuilder,
    #masterPiSelectedPanel {
        padding: 14px;
        border-radius: 12px;
    }
    #salesTermsBox {
        padding: 14px 16px !important;
        font-size: 9.5pt !important;
    }
    #salesTermsList {
        padding-left: 20px !important;
    }
    .term-sel {
        width: 100%;
        margin: 3px 0;
    }
}
</style>

<!-- No-order prompt (shown when no order loaded) -->
<div id="noOrderPrompt" style="display:none;background:#fff;border:2px dashed #c7d2fe;border-radius:16px;padding:32px;margin-bottom:20px;">
    <div style="text-align:center;margin-bottom:20px;">
        <div style="font-size:36px;margin-bottom:8px;">PI</div>
        <div style="font-size:17px;font-weight:800;color:#1e1e2e;margin-bottom:4px;">No Order Loaded</div>
        <div style="font-size:13px;color:#64748b;">Select an existing order below or start a new one.</div>
    </div>
    <!-- Recent orders list from DB -->
    <div id="noOrderList" style="margin-bottom:16px;">
        <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px;">Recent Orders</div>
        <div id="noOrderRows" style="display:flex;flex-direction:column;gap:6px;">
            <div style="color:#94a3b8;font-size:13px;padding:8px 0;">Loading orders…</div>
        </div>
    </div>
    <div style="text-align:center;">
        <button class="primary-btn" style="padding:10px 26px;font-size:13px;" onclick="oidNewOrder()">+ Start New Order</button>
    </div>
</div>

<!-- Main PI content (hidden until order loaded) -->
<div id="piContent">

<!-- Summary bar -->
<div class="pi-summary-bar">
    <div class="pi-sum-badge">PI <span id="piNumDisplay">-</span></div>
    <div class="pi-sum-item"><div class="pi-sum-num" id="sumPoCount">0</div><div class="pi-sum-lbl">PIs</div></div>
    <div class="pi-sum-item"><div class="pi-sum-num" id="sumTotalQty">0</div><div class="pi-sum-lbl">Total Qty</div></div>
    <div class="pi-sum-item"><div class="pi-sum-num" id="sumTotalVal">$0.00</div><div class="pi-sum-lbl">Total Value</div></div>
    <div style="margin-left:auto;text-align:right;">
        <div style="font-size:10px;color:#94a3b8;">Status</div>
        <div style="font-size:13px;font-weight:700;color:#a5b4fc;" id="piStatus">Draft</div>
    </div>
</div>

<!-- Edit-PI banner (shown for an already Marketing-approved PI) -->
<div id="editPiBanner" style="display:none;background:#fffbeb;border:1.5px solid #fde68a;border-radius:12px;padding:12px 18px;margin-bottom:16px;">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
        <div>
            <div style="font-size:13px;font-weight:800;color:#92400e;">This PI is Marketing-approved</div>
            <div style="font-size:12px;color:#a16207;">Need to update ERP items/data? Click Edit to re-fetch live ERP for the saved orders. Re-submitting sends it back to Marketing for approval. The PI number stays the same.</div>
        </div>
        <button type="button" id="editPiBtn" class="primary-btn" onclick="startEditApprovedPi()"
                style="background:linear-gradient(135deg,#d97706,#b45309);white-space:nowrap;">Edit PI</button>
    </div>
    <div id="editNoteWrap" style="display:none;margin-top:12px;">
        <label style="display:block;font-size:11px;font-weight:700;color:#92400e;margin-bottom:4px;">Edit note (optional)</label>
        <textarea id="editNoteInput" rows="2" placeholder="Reason for the change (optional)…"
                  style="width:100%;box-sizing:border-box;border:1.5px solid #fcd34d;border-radius:8px;padding:8px 10px;font-size:13px;"></textarea>
        <div style="font-size:11px;color:#a16207;margin-top:4px;">Editing: fields are now editable and ERP items were refreshed live. Submit to send back to Marketing.</div>
    </div>
</div>

<!-- Order PI Overview -->
<div class="order-pi-overview" id="orderPiOverview" style="display:none;">
    <div class="opo-title">
        PIs for this Order
        <span id="opoPiCount" style="font-size:11px;font-weight:600;color:#6366f1;background:#ede9fe;padding:2px 10px;border-radius:999px;"></span>
        <span style="font-size:11px;color:#64748b;font-weight:400;margin-left:6px;">- Select items for Master PI</span>
    </div>
    <div class="opo-list" id="opoPiList"></div>
    <!-- Master PI basket bar -->
    <div id="mpiBasket" style="display:none;margin-top:14px;background:linear-gradient(135deg,#1e1e2e,#312e81);color:#fff;border-radius:10px;padding:11px 18px;align-items:center;gap:18px;flex-wrap:wrap;">
        <span style="font-size:12px;font-weight:700;" id="mpiBasketLabel">0 items selected</span>
        <span style="font-size:12px;">Qty: <strong id="mpiBasketQty" style="color:#a5b4fc;">0</strong></span>
        <span style="font-size:12px;">Total: <strong id="mpiBasketVal" style="color:#a5b4fc;">$0.00</strong></span>
        <button type="button" class="mpi-basket-btn" onclick="createMasterFromSelection()">Create Master PI</button>
    </div>
</div>

<!-- PI Type & Print bar -->
<div class="mpi-bar" style="flex-wrap:wrap;gap:14px;">
    <div class="mpi-bar-left" style="flex-shrink:0;">
        <div class="mpi-icon">PI</div>
        <div>
            <div class="mpi-title">Generate &amp; Print PI</div>
            <div class="mpi-sub">Choose type then click Print to open the formatted Proforma Invoice</div>
        </div>
    </div>
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
        <span id="savedPiCountBadge" style="font-size:12px;font-weight:700;background:#ede9fe;color:#6366f1;padding:4px 12px;border-radius:999px;display:none;">
            <span id="savedPiCount">0</span> PI(s) saved
        </span>
        <!-- PI Type radio pills -->
        <div style="display:flex;gap:6px;background:#f1f0ff;border:1.5px solid #c7d2fe;border-radius:10px;padding:4px 6px;">
            <label style="cursor:pointer;display:flex;align-items:center;gap:5px;padding:5px 12px;border-radius:7px;font-size:12px;font-weight:700;transition:.15s;"
                   id="piTypeLbl_single" class="pi-type-lbl active-lbl">
                <input type="radio" name="piTypeChoice" value="single" checked onchange="onPiTypeChange()" style="accent-color:#6366f1;"> Single PI
            </label>
            <label style="cursor:pointer;display:flex;align-items:center;gap:5px;padding:5px 12px;border-radius:7px;font-size:12px;font-weight:700;transition:.15s;"
                   id="piTypeLbl_summary" class="pi-type-lbl">
                <input type="radio" name="piTypeChoice" value="summary" onchange="onPiTypeChange()" style="accent-color:#6366f1;"> Summary PI
            </label>
            <label style="cursor:pointer;display:flex;align-items:center;gap:5px;padding:5px 12px;border-radius:7px;font-size:12px;font-weight:700;transition:.15s;"
                   id="piTypeLbl_master" class="pi-type-lbl">
                <input type="radio" name="piTypeChoice" value="master" onchange="onPiTypeChange()" style="accent-color:#6366f1;"> Master PI
            </label>
        </div>
        <button type="button" id="printPiBtn" class="primary-btn" onclick="goToPiPrint()"
                style="background:linear-gradient(135deg,#6366f1,#4f46e5);white-space:nowrap;padding:9px 22px;"
                disabled title="Submit PI first to unlock printing">
            Print Single PI
        </button>
        <button type="button" id="excelPiBtn" class="ghost-btn" onclick="goToPiPrint(true)"
                style="white-space:nowrap;padding:9px 22px;"
                disabled title="Submit PI first to unlock Excel download">
            Download Excel
        </button>
        <button type="button" id="emailPiBtn" class="ghost-btn" onclick="emailPiFromSales()"
                style="white-space:nowrap;padding:9px 22px;background:#0f6cbd;color:#fff;border-color:#0f6cbd;"
                disabled title="Submit PI and get Marketing approval before emailing">
            📧 Email PI
        </button>
    </div>
</div>
<style>
.pi-type-lbl { color:#64748b; }
.pi-type-lbl.active-lbl { background:#6366f1; color:#fff; }
</style>
<script>
let _currentPiStep = 'sales';
let _marketingApproved = false;
let _editingApprovedPi = false; // true after Commercial clicks "Edit PI"

// Commercial may revise an approved PI even after the order has advanced beyond
// Sales. Re-submitting the revision returns it to Marketing for approval.
function refreshEditPiBanner() {
    const banner = document.getElementById('editPiBanner');
    if (!banner) return;
    const canEdit = hasMarketingApproval();
    banner.style.display = canEdit ? 'block' : 'none';
    const btn = document.getElementById('editPiBtn');
    if (btn) btn.style.display = _editingApprovedPi ? 'none' : '';
    const noteWrap = document.getElementById('editNoteWrap');
    if (noteWrap) noteWrap.style.display = _editingApprovedPi ? 'block' : 'none';
}

function isWaitingForMarketingApproval() {
    return _currentPiStep === 'marketing';
}

function hasMarketingApproval() {
    return _marketingApproved === true;
}

function isPastPiStep() {
    // Order already advanced to LC or beyond ('marketing' isn't in the list → -1).
    return _stepIdx(_currentPiStep) > _stepIdx('sales');
}
function goToLcPage() {
    window.location.href = APP_BASE + '/pages/lc.php';
}
function resetSubmitBtn() {
    const btn = document.getElementById('universalSaveBtn');
    const advanced = isPastPiStep();
    // The PI only needs saving while it's still an editable draft. Once it's
    // submitted (waiting), Marketing-approved, or already moved to LC, hide
    // "Save PI" — it's ready.
    // Save PI is permanently hidden — the order is created only on Submit to
    // Marketing, so there is no pre-submit draft save.
    const saveBtn = document.getElementById('savePiBtn');
    if (saveBtn) saveBtn.style.display = 'none';
    refreshEditPiBanner();
    if (!btn) return;
    btn.style.background = '';
    if (_editingApprovedPi) {
        // Editing an approved PI → re-submit resets approval and returns to Marketing.
        btn.textContent = 'Submit Revision to Marketing';
        btn.disabled = false;
        btn.onclick = submitEditedPiToMarketing;
    } else if (advanced) {
        // Already sent to LC (or beyond) — don't re-submit; just open LC.
        btn.textContent = 'Open LC';
        btn.disabled = false;
        btn.onclick = goToLcPage;
    } else if (isWaitingForMarketingApproval()) {
        btn.textContent = 'Waiting for Marketing Approval';
        btn.style.background = '#94a3b8';
        btn.disabled = true;
        btn.onclick = null;
    } else if (hasMarketingApproval()) {
        btn.textContent = 'Submit to LC';
        btn.disabled = false;
        btn.onclick = submitApprovedPiToLc;
    } else {
        btn.textContent = 'Submit to Marketing';
        btn.disabled = false;
        btn.onclick = submitToMarketing;
    }
}

function onPiTypeChange() {
    resetSubmitBtn();
    const val = document.querySelector('input[name="piTypeChoice"]:checked')?.value || 'single';
    document.querySelectorAll('.pi-type-lbl').forEach(l => l.classList.remove('active-lbl'));
    const lbl = document.getElementById('piTypeLbl_' + val);
    if (lbl) lbl.classList.add('active-lbl');
    const approved = hasMarketingApproval();
    document.querySelectorAll('input[name="piTypeChoice"]').forEach(r => {
        r.disabled = !approved && r.value !== 'single';
    });
    const labels = !approved
        ? { single:'Preview Single PI', summary:'Preview Summary PI', master:'Preview Master PI' }
        : { single:'Print Single PI', summary:'Print Summary PI', master:'Print Master PI' };
    const btn = document.getElementById('printPiBtn');
    if (btn) btn.textContent = labels[val] || 'Print PI';
    const submitBtn = document.getElementById('universalSaveBtn');
    resetSubmitBtn();
    // Summary now assembles already-created PIs, so the "+ Add Another PI"
    // new-block button is no longer used.
    const addBtn = document.getElementById('addAnotherPoBtn');
    if (addBtn) addBtn.style.display = 'none';
    const termsBox = document.getElementById('salesTermsBox');
    if (termsBox) termsBox.style.display = val === 'summary' ? 'none' : '';
    // Block editor is for Single PI only; Summary uses the picker, Master uses the item list.
    const piBlocksEditor = document.getElementById('piBlocksEditor');
    if (piBlocksEditor) piBlocksEditor.style.display = val === 'single' ? 'block' : 'none';
    const summaryBuilder = document.getElementById('summaryBuilder');
    if (summaryBuilder) summaryBuilder.style.display = val === 'summary' ? 'block' : 'none';
    const masterPanel = document.getElementById('masterPiSelectedPanel');
    if (masterPanel) masterPanel.style.display = val === 'master' ? 'block' : 'none';
    if (val === 'master') renderMasterSelectedItems();
    if (val === 'summary' && typeof enterSummaryMode === 'function') enterSummaryMode();
    // Leaving Summary → restore the top bar to the PO-block (Single PI) totals.
    if (val === 'single' && typeof updateSummary === 'function') updateSummary();
    // Update top-bar PI number badge for Summary / Master tabs
    const numDisp = document.getElementById('piNumDisplay');
    if (numDisp) {
        const ordId = sessionStorage.getItem('ats_current_order_id') || '';
        if (val === 'summary') {
            numDisp.textContent = ordId ? ordId + '-SUMM' : 'SUMMARY';
        } else if (val === 'master') {
            // Show saved master PI number if available, else placeholder
            const mPi = (window._salesSnapshot && (window._salesSnapshot.masterPiNum || window._salesSnapshot.piNum)) || (ordId ? ordId + '-MASTER' : 'MASTER');
            numDisp.textContent = mPi;
        }
        // Single: left to the existing PI number field logic
    }
    // Clear override fields when switching tabs so they don't bleed across PI types
    const _ovName = document.getElementById('piCustOverrideName');
    const _ovAddr = document.getElementById('piCustOverrideAddr');
    if (_ovName) _ovName.value = '';
    if (_ovAddr) _ovAddr.value = '';
    // Show/hide and re-label the customer override block
    const overrideBlock = document.getElementById('piCustOverrideBlock');
    const overrideLabel = document.getElementById('piCustOverrideLabel');
    const overrideNameLbl = document.getElementById('piCustOverrideNameLabel');
    const overrideAddrLbl = document.getElementById('piCustOverrideAddrLabel');
    if (overrideBlock) {
        if (val === 'single') {
            overrideBlock.style.display = 'none';
        } else {
            overrideBlock.style.display = '';
            const typeName = val === 'summary' ? 'Summary PI' : 'Master PI';
            if (overrideLabel) overrideLabel.firstChild.textContent = typeName + ' — Customer Override';
            if (overrideNameLbl) overrideNameLbl.textContent = 'Customer Name (TO) — ' + typeName + ' only';
            if (overrideAddrLbl) overrideAddrLbl.textContent = 'Customer Address — ' + typeName + ' only';
        }
    }
}
document.addEventListener('DOMContentLoaded', onPiTypeChange);
function goToPiPrint(excelMode = false) {
    const approved = hasMarketingApproval();
    if (!approved && !isWaitingForMarketingApproval()) {
        alert('Submit the PI to Marketing before previewing it.');
        return;
    }
    if (!approved && excelMode) {
        alert('Waiting for Marketing approval before exporting PI.');
        return;
    }
    const val  = document.querySelector('input[name="piTypeChoice"]:checked')?.value || 'single';
    if (!approved && val !== 'single') {
        alert('Summary PI and Master PI are available after Marketing approval.');
        return;
    }
    const pages = { single:'single-pi.php', summary:'summary-pi.php', master:'master-pi.php' };
    let url = APP_BASE + '/pages/' + (pages[val] || 'single-pi.php');
    if (val === 'master') {
        const selection = (_masterEditGroups.length && !getSelectedMasterGroups().length) ? _masterEditGroups : getSelectedMasterGroups();
        if (selection.length) {
            sessionStorage.setItem('mpi_custom_items', JSON.stringify(selection));
        }
        // No selection is fine — master-pi.php loads from saved PI data directly
    }
    if (val === 'summary') {
        if (!_summarySelectedPis.length) {
            alert('Add at least one already-created PI to the Summary before printing.');
            return;
        }
        // Hand the chosen (already-created) PIs to summary-pi.php — preview/print only.
        sessionStorage.setItem('summary_selected_pis', JSON.stringify(_summarySelectedPis));
        // Pass Summary-PI-only customer overrides (blank = use shared PI Header values)
        const sName = (document.getElementById('piCustOverrideName')?.value || '').trim();
        const sAddr = (document.getElementById('piCustOverrideAddr')?.value || '').trim();
        sessionStorage.setItem('summary_pi_cust_override', JSON.stringify({ name: sName, addr: sAddr }));
        url += '?summary=1';
    }
    if (val === 'master') {
        // Pass Master-PI-only customer overrides
        const mName = (document.getElementById('piCustOverrideName')?.value || '').trim();
        const mAddr = (document.getElementById('piCustOverrideAddr')?.value || '').trim();
        sessionStorage.setItem('master_pi_cust_override', JSON.stringify({ name: mName, addr: mAddr }));
    }
    if (val === 'single' || val === 'master' || val === 'summary') {
        const days = document.getElementById('termLcDays')?.value || '90';
        const tol  = document.getElementById('termTolerance')?.value || '5';
        const hs   = document.getElementById('termHsCode')?.value || '';
        const docMust = document.getElementById('termDocMust')?.value || 'UD';
        const bnk  = document.getElementById('termBank')?.value || 'ncc';
        const sep = url.includes('?') ? '&' : '?';
        url += sep + 'days=' + encodeURIComponent(days)
            + '&lctype=Sight&tol=' + encodeURIComponent(tol)
            + '&hs=' + encodeURIComponent(hs)
            + '&doc=' + encodeURIComponent(docMust)
            + '&bank=' + encodeURIComponent(bnk);
        const orderId = window.getCurrentOrderId ? window.getCurrentOrderId() : (sessionStorage.getItem('ats_current_order_id') || '');
        if (orderId) url += '&order_id=' + encodeURIComponent(orderId);
    }
    if (excelMode) {
        url += (url.includes('?') ? '&' : '?') + 'excel=1';
    }
    if (!approved) {
        url += (url.includes('?') ? '&' : '?') + 'preview=1&embed=1';
    }
    window.location.href = url;
}

// Email the currently-selected PI via desktop Outlook (helper renders it to PDF).
// Builds the same print URL as goToPiPrint but ABSOLUTE + embed, and hands it to
// the atsmail:// helper. Requires the approved PI + the per-PC helper installed.
async function emailPiFromSales() {
    const approved = hasMarketingApproval();
    if (!approved) { alert('Submit the PI and get Marketing approval before emailing it.'); return; }
    const val = document.querySelector('input[name="piTypeChoice"]:checked')?.value || 'single';
    const pages = { single:'single-pi.php', summary:'summary-pi.php', master:'master-pi.php' };
    let url = APP_BASE + '/pages/' + (pages[val] || 'single-pi.php');

    if (val === 'master') {
        const selection = (_masterEditGroups.length && !getSelectedMasterGroups().length) ? _masterEditGroups : getSelectedMasterGroups();
        if (selection.length) sessionStorage.setItem('mpi_custom_items', JSON.stringify(selection));
    }
    if (val === 'summary') {
        if (!_summarySelectedPis.length) { alert('Add at least one PI to the Summary before emailing.'); return; }
        sessionStorage.setItem('summary_selected_pis', JSON.stringify(_summarySelectedPis));
        const sName2 = (document.getElementById('piCustOverrideName')?.value || '').trim();
        const sAddr2 = (document.getElementById('piCustOverrideAddr')?.value || '').trim();
        sessionStorage.setItem('summary_pi_cust_override', JSON.stringify({ name: sName2, addr: sAddr2 }));
        url += '?summary=1';
    }
    if (val === 'master') {
        const mName2 = (document.getElementById('piCustOverrideName')?.value || '').trim();
        const mAddr2 = (document.getElementById('piCustOverrideAddr')?.value || '').trim();
        sessionStorage.setItem('master_pi_cust_override', JSON.stringify({ name: mName2, addr: mAddr2 }));
    }
    const days = document.getElementById('termLcDays')?.value || '90';
    const tol  = document.getElementById('termTolerance')?.value || '5';
    const hs   = document.getElementById('termHsCode')?.value || '';
    const docMust = document.getElementById('termDocMust')?.value || 'UD';
    const bnk  = document.getElementById('termBank')?.value || 'ncc';
    const sep = url.includes('?') ? '&' : '?';
    url += sep + 'days=' + encodeURIComponent(days)
        + '&lctype=Sight&tol=' + encodeURIComponent(tol)
        + '&hs=' + encodeURIComponent(hs)
        + '&doc=' + encodeURIComponent(docMust)
        + '&bank=' + encodeURIComponent(bnk)
        + '&embed=1&email_render=1';
    const orderId = window.getCurrentOrderId ? window.getCurrentOrderId() : (sessionStorage.getItem('ats_current_order_id') || '');
    if (!orderId) {
        alert('Load an order before emailing the PI.');
        return;
    }
    url += '&order_id=' + encodeURIComponent(orderId);

    // The helper renders in a clean headless browser that does not share this
    // tab's login cookie. Give it a short-lived, single-use authenticated URL.
    let absUrl;
    try {
        const tokenResponse = await fetch(APP_BASE + '/api/pi_render_token.php', {method:'POST'});
        const tokenData = await tokenResponse.json();
        if (!tokenResponse.ok || !tokenData.token) throw new Error(tokenData.error || 'Could not create render token.');
        const renderUrl = new URL(url, window.location.origin);
        renderUrl.searchParams.set('render_token', tokenData.token);
        absUrl = renderUrl.toString();
    } catch (error) {
        alert('Could not prepare the PI for email. Please try again.');
        return;
    }

    const piNum   = (document.getElementById('piNumber')?.value || document.getElementById('piNumDisplay')?.textContent || 'PI').trim();
    const customer= (document.getElementById('piCustomer')?.value || '').trim();
    const safe = s => String(s || 'PI').replace(/[\/\\:*?"<>|]+/g,'-').replace(/\s+/g,'-').replace(/-+/g,'-').replace(/^-|-$/g,'') || 'PI';
    const fileNm  = safe((customer ? customer + '-' : '') + piNum);
    const subject = 'Proforma Invoice ' + piNum + (customer ? ' — ' + customer : '');
    const body    = 'Dear Sir/Madam,\r\n\r\nPlease find attached our Proforma Invoice ' + piNum +
                    '.\r\n\r\nBest regards,\r\nZaber & Zubair Accessories Ltd.';

    const link = 'atsmail://open?url=' + encodeURIComponent(absUrl)
        + '&subject=' + encodeURIComponent(subject)
        + '&body='    + encodeURIComponent(body)
        + '&file='    + encodeURIComponent(fileNm) + '&to=';
    // Friendly toast (Chrome shows its own "Open?" prompt for the atsmail:// link).
    (function(){
        let t = document.getElementById('atsEmailHint');
        if (!t) { t = document.createElement('div'); t.id = 'atsEmailHint';
            t.style.cssText = 'position:fixed;top:16px;left:50%;transform:translateX(-50%);z-index:100000;background:#0f6cbd;color:#fff;padding:12px 18px;border-radius:10px;font-size:14px;font-weight:600;box-shadow:0 8px 30px rgba(0,0,0,.25);max-width:520px;text-align:center;line-height:1.4;';
            document.body.appendChild(t); }
        t.innerHTML = '📧 Opening Outlook with the PI attached…<br><span style="font-weight:400;font-size:12.5px;">If a Windows prompt appears, click <b>Open</b> — it is needed to attach the PI and open your email.</span>';
        t.style.display = 'block'; clearTimeout(t._hideTimer); t._hideTimer = setTimeout(() => { t.style.display = 'none'; }, 6000);
    })();
    let launched = false;
    window.addEventListener('blur', () => { launched = true; }, { once: true });
    try { window.location.href = link; } catch (_) {}
    setTimeout(() => {
        if (!launched) alert('Could not open Outlook.\n\nThe "Email PI" helper may not be installed on this PC.\nAsk IT to run the setup in /outlook-helper (README.txt), or use Print / Save PDF and attach manually.');
    }, 1500);
}
</script>

<!-- PI Header -->
<div class="pi-hdr-card">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:8px;">
        <div>
            <div class="eyebrow">Proforma Invoice</div>
            <h2 style="margin:0;font-size:17px;">PI Header - Shared Details</h2>
        </div>
    </div>
    <div class="form-grid">
        <div class="field span-3" id="globalPiNumField" style="display:none;">
            <label for="piNumber">PI Number</label>
            <input id="piNumber" placeholder="e.g. 26/52017"
                   oninput="document.getElementById('piNumDisplay').textContent=this.value||'-'">
        </div>
        <div class="field span-3">
            <label for="piCustomer">Customer Name (TO)</label>
            <div class="combo-wrap" style="position:relative;">
                <input id="piCustomer" autocomplete="off" placeholder="Type customer name..."
                       oninput="onPiCustomerInput()" onfocus="onPiCustomerInput()"
                       onblur="setTimeout(hidePiCustomerDropdown,150)">
                <div id="piCustomerDropdown" class="combo-dropdown" style="display:none;"></div>
            </div>
        </div>
        <div class="field span-3">
            <label for="piBuyer">Buyer (Brand / End Buyer)</label>
            <input id="piBuyer" placeholder="e.g. Benetton, H&M, Zara...">
        </div>
        <div class="field span-3">
            <label for="piDate">PI Date</label>
            <input id="piDate" type="date">
        </div>
        <div class="field span-3">
            <label for="piMarketingUser">Marketing Person (Approver)</label>
            <select id="piMarketingUser">
                <option value="">— Select marketing person —</option>
            </select>
        </div>
        <div class="field span-6">
            <label for="piBuyerAddress">Customer Address</label>
            <textarea id="piBuyerAddress" rows="2" placeholder="Auto-filled from customer profile..."></textarea>
        </div>
        <!-- Override fields — shown only for Summary PI / Master PI tabs -->
        <div id="piCustOverrideBlock" style="display:none;grid-column:1/-1;margin-top:4px;padding-top:12px;border-top:1.5px dashed #c7d2fe;">
            <div style="font-size:11px;font-weight:700;color:#4f46e5;margin-bottom:10px;" id="piCustOverrideLabel">
                Summary PI — Customer Override
                <span style="font-weight:400;color:#94a3b8;"> · only applies to this PI type's print. Leave blank to use the fields above.</span>
            </div>
            <div class="form-grid">
                <div class="field span-6">
                    <label id="piCustOverrideNameLabel" for="piCustOverrideName">Customer Name (TO) — Summary PI only</label>
                    <input id="piCustOverrideName" placeholder="Leave blank to use shared name above…">
                </div>
                <div class="field span-6">
                    <label id="piCustOverrideAddrLabel" for="piCustOverrideAddr">Customer Address — Summary PI only</label>
                    <textarea id="piCustOverrideAddr" rows="2" placeholder="Leave blank to use shared address above…"></textarea>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="piBlocksEditor">
<!-- PO Blocks -->
<div id="poBlocksContainer"></div>

<!-- Add PO button -->
<div style="text-align:center;margin-bottom:20px;">
    <button type="button" id="addAnotherPoBtn" class="ghost-btn" onclick="addPoBlock()"
            style="padding:10px 32px;font-size:14px;border-style:dashed;border-width:2px;">
        + Add Another PI
    </button>
</div>
</div>

<!-- Summary PI builder — assemble from already-created PIs (across work orders) -->
<div id="summaryBuilder" style="display:none;background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:18px 20px;margin-bottom:20px;">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:12px;">
        <div>
            <div class="eyebrow">Summary PI</div>
            <h3 style="margin:0;font-size:16px;">Add Already-Created PIs</h3>
        </div>
        <div style="font-size:12px;color:#64748b;">Find by PI number, work order (ORD-…) or ERP sales order and add it. Combine PIs from different work orders — nothing new is created.</div>
    </div>
    <div class="erp-search-row" style="margin-bottom:8px;">
        <input id="summarySearchInput" placeholder="Enter PI number, work order (ORD-…) or ERP sales order…"
               onkeydown="if(event.key==='Enter'){event.preventDefault();addSummaryPi();}">
        <button type="button" class="primary-btn" style="white-space:nowrap;" onclick="addSummaryPi()">Add PI</button>
    </div>
    <div id="summaryMsg" style="font-size:12px;color:#64748b;margin-bottom:10px;min-height:16px;"></div>
    <div id="summaryBasket"></div>
    <div style="display:flex;justify-content:flex-end;margin-top:14px;">
        <button type="button" class="primary-btn" onclick="saveSummaryPi()">Save Summary</button>
    </div>
</div>

<div id="masterPiSelectedPanel" style="display:none;background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:18px 20px;margin-bottom:20px;">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:12px;">
        <div>
            <div class="eyebrow">Master PI Items</div>
            <h3 style="margin:0;font-size:16px;">Selected Items Only</h3>
        </div>
        <div style="font-size:12px;color:#64748b;">Choose items from the list above. This section updates automatically.</div>
    </div>
    <div id="masterPiSelectedList"></div>
</div>

<!-- Terms & Conditions preview (matches Single PI / Master PI print) -->
<div id="salesTermsBox" style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:20px 28px;margin-top:16px;font-family:'Times New Roman',Times,serif;font-size:10.5pt;color:#000;">
    <div style="font-weight:700;text-decoration:underline;margin-bottom:8px;font-size:10.5pt;">Terms &amp; Conditions:</div>
    <ol id="salesTermsList" style="margin:0;padding-left:28px;line-height:1.9;font-size:10pt;"></ol>
</div>
<style>
.term-sel {
    display:inline; border:1.5px solid #6366f1; border-radius:4px;
    padding:1px 6px; font-size:10pt; font-family:inherit;
    background:#f5f3ff; color:#1e40af; font-weight:700; outline:none; cursor:pointer;
}
.combo-dropdown {
    position:absolute; top:100%; left:0; right:0; z-index:50;
    max-height:280px; overflow-y:auto; margin-top:2px;
    background:#fff; border:1px solid #d1d5db; border-radius:8px;
    box-shadow:0 8px 24px rgba(0,0,0,.12);
}
.combo-item {
    padding:8px 12px; font-size:14px; cursor:pointer; color:#111827;
}
.combo-item:hover { background:#4f46e5; color:#fff; }
.combo-empty { padding:8px 12px; font-size:13px; color:#9ca3af; }
</style>
<script>
(function(){
    function buildSalesTerms() {
        const days = document.getElementById('termLcDays')?.value || '90';
        const lct  = document.getElementById('termLcType')?.value || 'Sight';
        const tol  = document.getElementById('termTolerance')?.value || '5';
        const hs   = document.getElementById('termHsCode')?.value || '';
        const docMust = document.getElementById('termDocMust')?.value || 'UD';

        const BANKS = {
            ncc:  { label:'National Credit & Commerce Bank Plc.', name:'National Credit &amp; Commerce Bank Plc.', addr:'Motijheel main Branch, 6 Motijheel C/A Dhaka-1000 Bangladesh.', acct:'0002-0259000092', swift:'NCCLBDDHNBB', routing:'160150137' },
            dbbl: { label:'Dutch-Bangla Bank Plc.',               name:'Dutch-Bangla Bank Plc.',               addr:'Local Office, 1, Dilkusha C/A, Dhaka-1000, Bangladesh.',             acct:'ERQ-101.117.1382',  swift:'DBBLBDDHCTS',  routing:'090273889'  }
        };
        const bankKey = document.getElementById('termBank')?.value || 'ncc';
        const bank = BANKS[bankKey] || BANKS.ncc;
        const bankOpts = Object.entries(BANKS).map(([k,b])=>`<option value="${k}"${k===bankKey?' selected':''}>${b.label}</option>`).join('');
        const bankDetail = `<div style="margin-top:4px;padding:6px 10px;background:#f8f9ff;border-left:3px solid #6366f1;font-size:9.5pt;line-height:1.7;">
            <strong>${bank.name}</strong><br>
            ${bank.addr}<br>
            Account No: ${bank.acct}<br>
            Swift Code: ${bank.swift}<br>
            Bank Routing No: ${bank.routing}
        </div>`;

        const DAY_OPTS = [
            { v:'At Sight', l:'At Sight' },
            { v:'30',  l:'30 Days' },
            { v:'60',  l:'60 Days' },
            { v:'90',  l:'90 Days' },
            { v:'120', l:'120 Days' },
        ];
        const dOpts = DAY_OPTS.map(o=>`<option value="${o.v}"${o.v===days?' selected':''}>${o.l}</option>`).join('');
        const daysLabel = days === 'At Sight' ? 'At Sight' : days + ' Days';
        const rOpts = ['3','5','10'].map(v=>`<option value="${v}"${v===tol?' selected':''}>${v}%</option>`).join('');

        const list = [
            `100% Irrevocable confirmed <select id="termLcDays" class="term-sel" onchange="buildSalesTerms()">${dOpts}</select>${days !== 'At Sight' ? ' Sight' : ''} L/C to be opened in favour of <strong>Zaber &amp; Zubair ACC. Ltd.</strong>`,
            `P.I Validity : <strong>45 Working days</strong>.`,
            `Letter of Credit to allow acceptability of +/- <select id="termTolerance" class="term-sel" onchange="buildSalesTerms()">${rOpts}</select> <strong>tolerance</strong> in quantity and Value.`,
            `Letter of Credit to allow for <strong>Partial Shipment</strong>.`,
            `The Buyer should provide a copy of the master L/C and Garment Export UD before the delivery of mentioned goods.`,
            `Where GSP certificate is required, applicant is requested to furnish full detail of the Master L/C in BBLC opened in favour of Zaber &amp; Zubair ACC. Ltd.`,
            `Prior to delivery- we will inform you full particulars of the consignment and forward the original delivery challan for the signature of the authorised signatory of your organisation. Please make arrangements to hand over the duly signed delivery challan at the time of delivery of goods.`,
            `Payment to be made on Maturity in US Dollar and Maturity date will be counted <strong>${daysLabel}</strong> from the date of DELIVERY Challan / Truck Receipt / <strong>This clause Will be integral Parts of L/C.</strong>`,
            `Interest to be paid at LIBOR by the Buyer till Maturity. If payment is not made within maturity then interest <strong>@16%</strong> will be charged for overdue period and buyer's is liable to pay. <strong>This clause Must be appeared on the L/C</strong>`,
            `Quality complaint, if any, should be notified to us prior before sewing.`,
            `The above mention terms &amp; condition will be the integral part of the BTB L/C &amp; it must be mention in the BTB L/C.`,
            `Beneficiary Bin No : <strong>000230256-0103</strong>`,
            `H.S. Code : <input id="termHsCode" type="text" value="${hs}" placeholder="Type H.S. Code…"
                style="min-width:220px;padding:4px 10px;border:1.5px solid #6366f1;border-radius:6px;font-size:10pt;font-weight:700;color:#1e40af;background:#fff;outline:none;">`,
            `${(() => {
                const opts = ['UD','IP','UP'].map(v => `<option value="${v}"${v===docMust?' selected':''}>${v}</option>`).join('');
                return `<select id="termDocMust" class="term-sel" onchange="buildSalesTerms()">${opts}</select> Mustbe`;
            })()}`,
            `Advising Bank : <select id="termBank" class="term-sel" onchange="buildSalesTerms()">${bankOpts}</select>${bankDetail}`,
        ];
        const el = document.getElementById('salesTermsList');
        if (el) el.innerHTML = list.map(t => `<li>${t}</li>`).join('');
    }
    window.buildSalesTerms = buildSalesTerms;
    document.addEventListener('DOMContentLoaded', buildSalesTerms);
})();
</script>

<!-- Page actions -->
<div class="page-actions" style="margin-top:16px;">
    <div class="page-actions-left">
        <button type="button" class="ghost-btn js-prev-page" data-prev-page="production">Previous</button>
        <!-- Save PI removed: the work order is created only on Submit to Marketing.
             No DB row / no draft is created before submit. -->
        <button type="button" id="savePiBtn" class="ghost-btn" onclick="savePi()" style="display:none;">Save PI</button>
        <button type="button" class="ghost-btn" onclick="clearPiForm()">Clear</button>
        <button type="button" class="primary-btn" id="universalSaveBtn" onclick="submitToMarketing()">Submit</button>
    </div>
    <div class="page-actions-right">
        <button type="button" class="primary-btn" id="nextLcBtn" disabled style="display:none;">LC after final submit</button>
    </div>
</div>

<!-- â”€â”€ Master PI Modal â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ -->
<div class="mpi-modal-shell" id="masterPiModal">
    <div class="mpi-modal">
        <div class="mpi-modal-head">
            <div>
                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8;">Master PI</div>
                <div style="font-size:18px;font-weight:800;color:#fff;margin-top:2px;">Select Items to Include</div>
            </div>
            <button type="button" onclick="closeMasterPi()"
                    style="background:rgba(255,255,255,.1);border:none;color:#fff;border-radius:10px;width:34px;height:34px;font-size:16px;cursor:pointer;">X</button>
        </div>

        <div class="mpi-modal-body">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:10px;padding:10px 12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;">
                <label style="display:flex;align-items:center;gap:8px;font-size:12px;font-weight:700;color:#334155;cursor:pointer;">
                    <input type="checkbox" id="mpiSelectAllItems" onchange="mpiToggleAllItems(this)"
                           style="width:15px;height:15px;accent-color:#6366f1;cursor:pointer;">
                    Select all items
                </label>
                <span style="font-size:12px;color:#64748b;">Use this to include every item from the saved PIs</span>
            </div>

            <div class="saved-pi-list" id="savedPiList">
                <p style="color:#94a3b8;font-size:13px;">No saved PIs yet. Save a PI first.</p>
            </div>

            <!-- Selection totals bar -->
            <div id="mpiPreview" style="display:none;margin-top:14px;background:#f0fdf4;border:1.5px solid #86efac;border-radius:10px;padding:10px 16px;display:flex;gap:24px;align-items:center;">
                <span style="font-size:12px;font-weight:700;color:#166534;" id="mpiItemCount">0 items</span>
                <span style="font-size:12px;color:#15803d;">Qty: <strong id="mpiTotalQty">0</strong></span>
                <span style="font-size:12px;color:#15803d;">Total: <strong id="mpiTotalVal">$0.00</strong></span>
            </div>
        </div>

        <div class="mpi-modal-foot">
            <button type="button" class="ghost-btn" onclick="closeMasterPi()">Cancel</button>
            <button type="button" class="primary-btn" onclick="generateMasterPi()"
                    style="background:linear-gradient(135deg,#7c3aed,#6366f1);">
                Open Master PI
            </button>
        </div>
    </div>
</div>

</div><!-- /piContent -->

<script>
let poCount = 0;
let rowCounters = {};

/* â”€â”€ Add a new PO block â”€â”€ */
function addPoBlock() {
    poCount++;
    const pid = 'po' + poCount;

    const div = document.createElement('div');
    div.className = 'po-block';
    div.id = 'block_' + pid;

    div.innerHTML = `
    <div class="po-block-hdr" onclick="togglePo('${pid}')">
        <div style="display:flex;align-items:center;gap:10px;">
            <span class="po-num-chip">PI ${poCount}</span>
            <span id="poLabel_${pid}" style="font-size:13px;font-weight:700;color:#1e1e2e;">New PI</span>
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
            <span id="poBadge_${pid}" style="font-size:12px;font-weight:700;color:#6366f1;background:#ede9fe;padding:3px 12px;border-radius:999px;">$0.00 | 0 pcs</span>
            ${poCount > 1 ? `<button class="ghost-btn" style="padding:3px 12px;font-size:12px;color:#f87171;border-color:#fca5a5;"
                onclick="event.stopPropagation();removePo('${pid}')">Remove</button>` : ''}
            <span id="poChevron_${pid}" style="color:#94a3b8;font-size:14px;">^</span>
        </div>
    </div>

    <div class="po-block-body" id="body_${pid}">

        <!-- PI Number for this PO -->
        <div class="form-grid" style="margin-bottom:10px;padding-bottom:12px;border-bottom:1px solid #e2e8f0;">
            <div class="field span-4">
                <label>PI Number <span id="piNumMode_${pid}" style="font-size:10px;font-weight:400;color:#94a3b8;">(auto-generated)</span></label>
                <div style="display:flex;gap:8px;align-items:center;">
                    <input id="piNum_${pid}" placeholder="Generating..." readonly
                           style="background:#f8fafc;color:#374151;flex:1;"
                           oninput="(function(v){const b=document.querySelectorAll('.po-block');if(b[0]&&b[0].id==='block_${pid}'){document.getElementById('piNumDisplay').textContent=v||'-';document.getElementById('piNumber').value=v;}})(this.value)"
                           onblur="if(!document.getElementById('piNum_${pid}').readOnly) checkPiNumDuplicate('${pid}')">
                    <button type="button" id="piNumManualBtn_${pid}" class="ghost-btn" style="white-space:nowrap;padding:6px 14px;font-size:12px;"
                            onclick="togglePiNumManual('${pid}')">Manual</button>
                </div>
                <div id="piNumMsg_${pid}" style="font-size:11px;margin-top:4px;min-height:14px;"></div>
            </div>
        </div>

        <!-- ERP Search -->
        <div class="erp-search-row">
            <input id="erpInput_${pid}" placeholder="Enter ERP sales order number..."
                   onkeydown="if(event.key==='Enter')searchErp('${pid}', window.erpChoiceAction && window.erpChoiceAction['${pid}'] === 'append')">
            <button class="primary-btn" style="white-space:nowrap;" onclick="searchErp('${pid}', window.erpChoiceAction && window.erpChoiceAction['${pid}'] === 'append')">Search Order</button>
            <button class="ghost-btn" style="white-space:nowrap;" onclick="prepareAddPo('${pid}')">+ Add Order</button>
            <button class="ghost-btn" onclick="clearPo('${pid}')">Clear</button>
        </div>
        <div class="erp-banner" id="erpBanner_${pid}">
            <strong>ERP Sales Order Search Result</strong>
            <span id="erpMsg_${pid}">Enter a sales order number to load its PO and item rows directly from ERP.</span>
        </div>

        <!-- PO Fields -->
        <div class="form-grid">
            <input id="salesOrder_${pid}" type="hidden">
            <div class="field span-4">
                <label>Customer PO Number</label>
                <input id="customerPo_${pid}" readonly placeholder="Auto-filled from ERP"
                       oninput="document.getElementById('poLabel_${pid}').textContent=this.value||'New Purchase Order'">
            </div>
            <div class="field span-4">
                <label>Buyer Name</label>
                <input id="buyerName_${pid}" readonly placeholder="Auto-filled from ERP">
            </div>
            <div class="field span-4">
                <label>Order Status</label>
                <select id="orderStatus_${pid}" disabled>
                    <option>Booked</option>
                    <option>Pending</option>
                    <option>Approved</option>
                    <option>Released to Factory</option>
                    <option>Closed</option>
                </select>
            </div>
            <div class="field span-4">
                <label>Requested Date</label>
                <input id="reqDate_${pid}" type="text" readonly placeholder="YYYY-MM-DD">
            </div>
        </div>

        <!-- Items table -->
        <div style="display:flex;align-items:center;justify-content:space-between;margin:10px 0 6px;">
            <div style="font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">
                ERP Item Rows
            </div>
            <button class="ghost-btn" style="padding:3px 12px;font-size:11px;" onclick="addRow('${pid}')">+ Add Row</button>
        </div>
        <div style="overflow-x:auto;">
            <table class="si-table" id="table_${pid}">
                <thead>
                    <tr>
                        <th>SL</th>
                        <th>Description of Goods</th>
                        <th>Ply / Type</th>
                        <th>Quantity</th>
                        <th>Price $</th>
                        <th>Amount $</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="tbody_${pid}"></tbody>
                <tfoot>
                    <tr class="si-total-row">
                        <td colspan="3" style="text-align:right;">Subtotal</td>
                        <td id="totQty_${pid}">0</td>
                        <td></td>
                        <td id="totVal_${pid}">$0.00</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>`;

    document.getElementById('poBlocksContainer').appendChild(div);
    addRow(pid);
    updateSummary();
    autoFillPiNum(pid);
}

// Toggle the PI number field between auto-generated (readonly) and manual entry.
function togglePiNumManual(pid) {
    const inp    = document.getElementById('piNum_' + pid);
    const btn    = document.getElementById('piNumManualBtn_' + pid);
    const label  = document.getElementById('piNumMode_' + pid);
    const msg    = document.getElementById('piNumMsg_' + pid);
    if (!inp) return;
    const goingManual = inp.readOnly; // currently auto → switch to manual
    if (goingManual) {
        releasePiReservation(pid); // free the auto number; manual doesn't reserve
        inp.readOnly = false;
        inp.style.background = '#fff';
        inp.style.color = '#111827';
        inp.focus();
        inp.select();
        if (btn)   btn.textContent = 'Use Auto';
        if (label) { label.textContent = '(manual — type your own, no duplicates)'; label.style.color = '#d97706'; }
        if (msg)   msg.textContent = '';
    } else {
        // Back to auto: clear + regenerate.
        inp.readOnly = true;
        inp.style.background = '#f8fafc';
        inp.style.color = '#374151';
        inp.value = '';
        if (btn)   btn.textContent = 'Manual';
        if (label) { label.textContent = '(auto-generated)'; label.style.color = '#94a3b8'; }
        if (msg)   { msg.textContent = ''; msg.style.color = ''; }
        autoFillPiNum(pid);
    }
}

// Check the manually-entered PI number isn't already used (server + other open blocks).
async function checkPiNumDuplicate(pid) {
    const inp = document.getElementById('piNum_' + pid);
    const msg = document.getElementById('piNumMsg_' + pid);
    const val = (inp?.value || '').trim();
    if (!val) { if (msg) msg.textContent = ''; inp?.setCustomValidity?.(''); return true; }

    // Same number typed in another block on this page?
    let localDup = false;
    document.querySelectorAll('[id^="piNum_"]').forEach(el => {
        if (el.id !== 'piNum_' + pid && (el.value || '').trim() === val) localDup = true;
    });
    if (localDup) {
        if (msg) { msg.textContent = 'This PI number is already used in another block above.'; msg.style.color = '#dc2626'; }
        inp?.setCustomValidity?.('Duplicate PI number');
        return false;
    }

    if (msg) { msg.textContent = 'Checking…'; msg.style.color = '#94a3b8'; }
    try {
        const resp = await fetch(APP_BASE + '/api/pis.php?q=' + encodeURIComponent(val));
        const res  = await resp.json();
        const exists = res && res.match === 'pi' && res.pi
            && String(res.pi.pi_number || '').trim().toLowerCase() === val.toLowerCase();
        if (exists) {
            if (msg) { msg.textContent = 'PI number ' + val + ' already exists. Choose another.'; msg.style.color = '#dc2626'; }
            inp?.setCustomValidity?.('Duplicate PI number');
            return false;
        }
        if (msg) { msg.textContent = 'Available.'; msg.style.color = '#16a34a'; }
        inp?.setCustomValidity?.('');
        return true;
    } catch (_) {
        if (msg) { msg.textContent = 'Could not verify — check your connection.'; msg.style.color = '#d97706'; }
        return true; // don't hard-block on a network error
    }
}

// Per-block reservation tokens so two+ people can create PIs at once without
// collisions. The server hands out the lowest FREE number and reserves it;
// we heartbeat to keep it, and release it on Clear/New Order.
window._piReserveTokens = window._piReserveTokens || {};

async function autoFillPiNum(pid) {
    try {
        // Release any previous reservation this block held (e.g. re-generating).
        releasePiReservation(pid);
        const r = await fetch(APP_BASE + '/api/pis.php?next_num=1');
        const d = await r.json();
        if (!d.pi_number) return;

        const el = document.getElementById('piNum_' + pid);
        if (el && !el.value) {
            el.value = d.pi_number;
            if (d.reserve_token) window._piReserveTokens[pid] = { token: d.reserve_token, num: d.pi_number };
            const firstBlock = document.querySelectorAll('.po-block')[0];
            if (firstBlock && firstBlock.id === 'block_' + pid) {
                document.getElementById('piNumDisplay').textContent = d.pi_number;
                document.getElementById('piNumber').value           = d.pi_number;
            }
        }
    } catch(e) { /* silent - user can type manually */ }
}

// Free a block's reserved number immediately (Clear / New Order / regenerate).
function releasePiReservation(pid) {
    const res = window._piReserveTokens && window._piReserveTokens[pid];
    if (!res || !res.token) return;
    try {
        const url = APP_BASE + '/api/pis.php?release_num=1&token=' + encodeURIComponent(res.token)
                  + '&pi_number=' + encodeURIComponent(res.num || '');
        // keepalive so it still fires if the page is unloading.
        fetch(url, { keepalive: true }).catch(() => {});
    } catch (_) {}
    delete window._piReserveTokens[pid];
}

// Heartbeat: keep all held reservations alive while the PI page is open.
function piReservationHeartbeat() {
    const tokens = window._piReserveTokens || {};
    Object.values(tokens).forEach(res => {
        if (res && res.token) {
            fetch(APP_BASE + '/api/pis.php?reserve_heartbeat=1&token=' + encodeURIComponent(res.token)).catch(() => {});
        }
    });
}
setInterval(piReservationHeartbeat, 5 * 60 * 1000); // every 5 min (TTL is 15 min)
// Release everything when the page is being left.
window.addEventListener('pagehide', function () {
    Object.keys(window._piReserveTokens || {}).forEach(pid => releasePiReservation(pid));
});

/* Toggle collapse */
function togglePo(pid) {
    const body  = document.getElementById('body_' + pid);
    const chev  = document.getElementById('poChevron_' + pid);
    const open  = body.style.display !== 'none';
    body.style.display = open ? 'none' : 'block';
    chev.textContent   = open ? 'v' : '^';
}

/* Remove PO block */
function removePo(pid) {
    if (!confirm('Remove this PI block?')) return;
    releasePiReservation(pid); // free its reserved number
    document.getElementById('block_' + pid)?.remove();
    updateSummary();
}

/* Add item row */
function addRow(pid) {
    if (!rowCounters[pid]) rowCounters[pid] = 0;
    rowCounters[pid]++;
    const sl  = rowCounters[pid];
    const rid = pid + '_' + sl;

    const tr = document.createElement('tr');
    tr.id = 'row_' + rid;
    tr.innerHTML = `
        <td style="text-align:center;width:30px;font-size:11px;color:#94a3b8;">${sl}</td>
        <td><input id="desc_${rid}" placeholder="Description of goods"></td>
        <td><input id="ply_${rid}"  placeholder="e.g. 5Ply" style="width:80px;"></td>
        <td><input id="qty_${rid}"  type="number" min="0" placeholder="0" oninput="calcRow('${rid}','${pid}')" style="width:80px;"></td>
        <td><input id="prc_${rid}"  type="number" min="0" step="0.0001" placeholder="0.0000" oninput="calcRow('${rid}','${pid}')" style="width:80px;"></td>
        <td><input id="amt_${rid}"  readonly style="background:#f8fafc;font-weight:700;color:#4f46e5;width:80px;"></td>
        <td><button class="si-del-btn" onclick="delRow('${rid}','${pid}')">X</button></td>`;
    document.getElementById('tbody_' + pid).appendChild(tr);
}

function delRow(rid, pid) {
    document.getElementById('row_' + rid)?.remove();
    calcTotal(pid);
}

function calcRow(rid, pid) {
    const qty = parseFloat(document.getElementById('qty_' + rid)?.value || 0) || 0;
    const prc = parseFloat(document.getElementById('prc_' + rid)?.value || 0) || 0;
    const amt = document.getElementById('amt_' + rid);
    if (amt) amt.value = qty > 0 || prc > 0 ? (qty * prc).toFixed(2) : '';
    calcTotal(pid);
}

function calcTotal(pid) {
    const rows = document.querySelectorAll('#tbody_' + pid + ' tr');
    let tq = 0, tv = 0;
    rows.forEach(r => {
        const rid = r.id.replace('row_', '');
        tq += parseFloat(document.getElementById('qty_' + rid)?.value || 0) || 0;
        tv += parseFloat(document.getElementById('amt_' + rid)?.value || 0) || 0;
    });
    document.getElementById('totQty_' + pid).textContent = tq.toLocaleString();
    document.getElementById('totVal_' + pid).textContent = '$' + tv.toFixed(2);
    const badge = document.getElementById('poBadge_' + pid);
    if (badge) badge.textContent = '$' + tv.toFixed(2) + ' | ' + tq.toLocaleString() + ' pcs';
    updateSummary();
}

function updateSummary() {
    const blocks = document.querySelectorAll('.po-block');
    let tq = 0, tv = 0;
    blocks.forEach(b => {
        const pid = b.id.replace('block_', '');
        tq += parseFloat(document.getElementById('totQty_' + pid)?.textContent?.replace(/,/g,'') || 0) || 0;
        tv += parseFloat(document.getElementById('totVal_' + pid)?.textContent?.replace('$','') || 0) || 0;
    });
    document.getElementById('sumPoCount').textContent  = blocks.length;
    document.getElementById('sumTotalQty').textContent = tq.toLocaleString();
    document.getElementById('sumTotalVal').textContent = '$' + tv.toFixed(2);
}

/* ERP Search (mock - replace with real ERP API call) */
window.erpChoiceOptions = window.erpChoiceOptions || {};
window.erpChoiceAction = window.erpChoiceAction || {};

function getFieldListValue(el) {
    if (!el) return [];
    const raw = String(el.dataset.list || '').trim();
    if (raw) {
        try {
            const parsed = JSON.parse(raw);
            if (Array.isArray(parsed)) {
                return parsed.map(v => String(v || '').trim()).filter(Boolean);
            }
        } catch (_) {}
    }
    const value = String(el.value || '').trim();
    if (!value) return [];
    const separator = String(el.id || '').startsWith('salesOrder_')
        ? /\s*[,;\n]+\s*/
        : /\s*\/\s*/;
    return value.split(separator).map(v => v.trim()).filter(Boolean);
}

function setFieldListValue(el, values, separator) {
    if (!el) return [];
    const unique = [];
    (values || []).forEach(value => {
        const normalized = String(value || '').trim();
        if (!normalized) return;
        if (!unique.includes(normalized)) unique.push(normalized);
    });
    el.dataset.list = JSON.stringify(unique);
    el.value = unique.join(separator);
    return unique;
}

function buildErpLinesForGroupData(erpData) {
    const aggregated = new Map();

    (erpData.groups || []).flatMap(g => (g.lines || [])).forEach(l => {
        const status = String(l.lineStatus || '').trim().toUpperCase();
        const desc = l.item || '';
        const ply = l.type || l.uom || '';
        const qty = parseFloat(l.qty || 0) || 0;
        const price = parseFloat(l.price || 0) || 0;

        if (status === 'CANCELLED') {
            return;
        }
        if (qty <= 0) {
            return;
        }

        const key = [desc, ply, price.toFixed(6)].join('|');

        if (!aggregated.has(key)) {
            aggregated.set(key, {
                desc,
                ply,
                qty: 0,
                price,
                total: 0
            });
        }

        const current = aggregated.get(key);
        current.qty += qty;
        current.total += qty * price;
    });

    return Array.from(aggregated.values()).map(line => ({
        desc: line.desc,
        ply: line.ply,
        qty: line.qty,
        price: line.price,
        total: line.total.toFixed(2)
    }));
}

function applyErpSelection(pid, erpData, chosenPo) {
    if (!erpData || !Array.isArray(erpData.groups) || !erpData.groups.length) return;

    const firstGrp = erpData.groups[0];
    const allLines = buildErpLinesForGroupData(erpData);

    fillPoBlock(pid, {
        poNum: chosenPo || firstGrp.customerPo || '',
        buyer: firstGrp.buyer || '',
        items: allLines
    }, {
        salesOrder: firstGrp.salesOrderNo || '',
        reqDate: firstGrp.requestDate || firstGrp.shipDate || '',
        status: firstGrp.status || ''
    });

    setPiCustomerIfEmpty(firstGrp.customerName || '');
    setPiAddressFromErpIfEmpty(firstGrp);
    setPiMarketingUserByName(firstGrp.salesPerson || '');
    const total = allLines.length;
    const msg = document.getElementById('erpMsg_' + pid);
    if (msg) {
        msg.innerHTML = `<span style="color:#16a34a;font-weight:700;">ERP matched PO ${chosenPo || erpData.po}</span> - ${erpData.groups.length} sales order(s) · ${total} merged line(s) loaded.`;
    }
    window.erpChoiceAction[pid] = 'replace';
}

function prepareAddPo(pid) {
    window.erpChoiceAction[pid] = 'append';
    const input = document.getElementById('erpInput_' + pid);
    if (input) {
        input.value = '';
        input.focus();
        input.placeholder = 'Enter another ERP sales order number...';
    }
    const msg = document.getElementById('erpMsg_' + pid);
    if (msg) {
        msg.innerHTML = '<span style="color:#2563eb;font-weight:700;">Add Order mode</span> - enter another sales order to append its PO and items to this same PI.';
    }
}

function appendPoToBlock(pid, po, extra) {
    const salesOrderEl = document.getElementById('salesOrder_' + pid);
    const customerPoEl = document.getElementById('customerPo_' + pid);
    const buyerNameEl = document.getElementById('buyerName_' + pid);
    const reqDateEl = document.getElementById('reqDate_' + pid);
    const statusEl = document.getElementById('orderStatus_' + pid);
    const poLabelEl = document.getElementById('poLabel_' + pid);
    const tbody = document.getElementById('tbody_' + pid);

    const existingSalesOrders = getFieldListValue(salesOrderEl);
    const existingCustomerPos = getFieldListValue(customerPoEl);
    const addedSalesOrders = Array.isArray(po?.salesOrders)
        ? po.salesOrders
        : [po?.salesOrder || po?.salesOrderNo || extra?.salesOrder || ''];
    const addedCustomerPos = Array.isArray(po?.poNums)
        ? po.poNums
        : [po?.poNum || ''];
    const mergedSalesOrders = setFieldListValue(salesOrderEl, [
        ...existingSalesOrders,
        ...addedSalesOrders
    ], ', ');
    const mergedCustomerPos = setFieldListValue(customerPoEl, [
        ...existingCustomerPos,
        ...addedCustomerPos
    ], ' / ');

    if (poLabelEl) {
        poLabelEl.textContent = mergedCustomerPos.join(' / ') || 'New Purchase Order';
    }
    if (buyerNameEl && !String(buyerNameEl.value || '').trim()) {
        buyerNameEl.value = po?.buyer || extra?.buyer || '';
    }
    if (reqDateEl && !String(reqDateEl.value || '').trim()) {
        reqDateEl.value = po?.reqDate || extra?.reqDate || '';
    }
    if (statusEl && !String(statusEl.value || '').trim()) {
        statusEl.value = po?.status || extra?.status || statusEl.value;
    }

    const existingItems = [];
    if (tbody) {
        tbody.querySelectorAll('tr').forEach(tr => {
            const id = tr.getAttribute('data-row-id') || String(tr.id || '').replace('row_', '');
            if (!id) return;
            existingItems.push({
                desc: document.getElementById('desc_' + id)?.value || '',
                ply: document.getElementById('ply_' + id)?.value || '',
                qty: document.getElementById('qty_' + id)?.value || 0,
                price: document.getElementById('prc_' + id)?.value || 0,
                total: document.getElementById('amt_' + id)?.value || 0
            });
        });
    }

    fillPoBlock(pid, {
        poNum: mergedCustomerPos.join(' / '),
        buyer: buyerNameEl?.value || po?.buyer || extra?.buyer || '',
        reqDate: reqDateEl?.value || po?.reqDate || extra?.reqDate || '',
        status: statusEl?.value || po?.status || extra?.status || '',
        salesOrder: mergedSalesOrders.join(', '),
        items: [...existingItems, ...(po?.items || [])]
    }, extra);
}

async function searchMultipleErpOrders(pid, orderNumbers, appendMode) {
    const msg = document.getElementById('erpMsg_' + pid);
    if (msg) {
        msg.innerHTML = `<span style="color:#2563eb;font-weight:700;">Loading ${orderNumbers.length} ERP sales orders...</span>`;
    }

    try {
        // Fetch everything before changing the form, so one failed ERP order never
        // leaves an edited PI half-replaced with incomplete data.
        const results = await Promise.all(orderNumbers.map(async orderNo => {
            const response = await fetch(
                APP_BASE + '/api/erp_order_proxy.php?order=' + encodeURIComponent(orderNo) + '&refresh=' + Date.now(),
                {cache:'no-store'}
            );
            let erp = {};
            try { erp = await response.json(); }
            catch (_) { throw new Error(`Sales order ${orderNo}: ERP returned an invalid response.`); }
            if (!response.ok || erp.error) {
                throw new Error(`Sales order ${orderNo}: ${erp.detail || erp.error || 'Could not search ERP.'}`);
            }
            if (!erp.found || !Array.isArray(erp.groups) || !erp.groups.length) {
                throw new Error(`Sales order ${orderNo} was not found in ERP.`);
            }
            return { orderNo, erp };
        }));

        const allGroups = results.flatMap(result => result.erp.groups || []);
        const firstGroup = allGroups[0] || {};
        const salesOrders = [...new Set(results.flatMap(result => {
            const found = (result.erp.groups || []).map(group => String(group.salesOrderNo || '').trim()).filter(Boolean);
            return found.length ? found : [result.orderNo];
        }))];
        const customerPos = [...new Set(results.flatMap(result => {
            const found = (result.erp.groups || []).map(group => String(group.customerPo || '').trim()).filter(Boolean);
            return found.length ? found : [String(result.erp.po || '').trim()].filter(Boolean);
        }))];
        const items = buildErpLinesForGroupData({groups: allGroups});
        const poData = {
            poNum: customerPos.join(' / '),
            poNums: customerPos,
            buyer: firstGroup.buyer || '',
            items,
            salesOrder: salesOrders.join(', '),
            salesOrders,
            reqDate: firstGroup.requestDate || firstGroup.shipDate || '',
            status: firstGroup.status || ''
        };
        const extra = {
            salesOrder: salesOrders.join(', '),
            reqDate: poData.reqDate,
            status: poData.status
        };

        if (appendMode) {
            appendPoToBlock(pid, poData, extra);
        } else {
            fillPoBlock(pid, poData, extra);
            setFieldListValue(document.getElementById('salesOrder_' + pid), salesOrders, ', ');
            setFieldListValue(document.getElementById('customerPo_' + pid), customerPos, ' / ');
        }

        setPiCustomerIfEmpty(firstGroup.customerName || '');
        setPiAddressFromErpIfEmpty(firstGroup);
        setPiMarketingUserByName(firstGroup.salesPerson || '');
        if (msg) {
            msg.innerHTML = `<span style="color:#16a34a;font-weight:700;">Loaded ${salesOrders.length} ERP sales orders</span> - ${customerPos.length} PO number(s) - ${items.length} merged line(s).`;
        }
        window.erpChoiceAction[pid] = 'replace';
        const input = document.getElementById('erpInput_' + pid);
        if (input) {
            input.value = '';
            input.placeholder = 'Enter ERP sales order number...';
        }
        return {success:true, orderCount:salesOrders.length};
    } catch (error) {
        if (msg) msg.innerHTML = `<span style="color:#f87171;">ERP error: ${escapeErpOptionHtml(error.message || 'Server unreachable.')}</span>`;
        return {success:false, error:error.message || 'Server unreachable.'};
    }
}

function chooseErpOption(pid, optionIndex) {
    const options = window.erpChoiceOptions[pid] || [];
    const option = options[optionIndex];
    if (!option || !option.data) return;
    if (window.erpChoiceAction[pid] === 'append') {
        const firstGrp = (option.data.groups || [])[0] || {};
        appendPoToBlock(pid, {
            poNum: option.po || option.data.po || '',
            buyer: firstGrp.buyer || '',
            items: buildErpLinesForGroupData(option.data),
            salesOrder: firstGrp.salesOrderNo || '',
            reqDate: firstGrp.requestDate || firstGrp.shipDate || '',
            status: firstGrp.status || ''
        }, {
            salesOrder: firstGrp.salesOrderNo || '',
            reqDate: firstGrp.requestDate || firstGrp.shipDate || '',
            status: firstGrp.status || ''
        });
        setPiCustomerIfEmpty(firstGrp.customerName || '');
    setPiAddressFromErpIfEmpty(firstGrp);
    setPiMarketingUserByName(firstGrp.salesPerson || '');
        const msg = document.getElementById('erpMsg_' + pid);
        if (msg) {
            msg.innerHTML = `<span style="color:#16a34a;font-weight:700;">Added PO ${option.po || option.data.po || ''}</span> to this PI block.`;
        }
        window.erpChoiceAction[pid] = 'replace';
        const input = document.getElementById('erpInput_' + pid);
        if (input) {
            input.value = '';
            input.placeholder = 'Enter ERP sales order number...';
        }
        return;
    }
    applyErpSelection(pid, option.data, option.po || '');
}

function escapeErpOptionHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function toggleErpOptionDetails(pid, optionIndex) {
    const wrap = document.getElementById('erpOptionsWrap_' + pid);
    if (!wrap) return;

    const cards = wrap.querySelectorAll('.erp-option-card');
    cards.forEach((card, index) => {
        const body = card.querySelector('.erp-option-details');
        const icon = card.querySelector('.erp-option-toggle');
        const shouldOpen = index === optionIndex && card.getAttribute('data-open') !== '1';
        card.setAttribute('data-open', shouldOpen ? '1' : '0');
        if (body) body.style.display = shouldOpen ? 'block' : 'none';
        if (icon) icon.textContent = shouldOpen ? 'Hide details' : 'Show details';
    });
}

function erpOptionPreviewHtml(option, query = '') {
    const groupData = option?.data || {};
    const groups = Array.isArray(groupData.groups) ? groupData.groups : [];
    const salesOrders = groups.map(g => g.salesOrderNo).filter(Boolean);
    const lines = buildErpLinesForGroupData(groupData);
    const firstGroup = groups[0] || {};
    const matchedQuery = String(query || '').trim();
    const erpPo = String(option?.po || groupData?.po || '').trim();
    const matchSummary = option?.matchSummary || {};

    const matchSummaryLabels = {
        customer_po_no: 'ERP Customer PO',
        remarks: 'Remarks',
        item_description: 'Description',
        item_code: 'Item Code',
        ordered_item: 'Ordered Item'
    };

    const matchSummaryHtml = Object.entries(matchSummary)
        .filter(([, values]) => Array.isArray(values) && values.length)
        .map(([field, values]) => `
            <div style="padding:10px;border:1px solid #dbe3ff;border-radius:10px;background:#fff;">
                <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;">${escapeErpOptionHtml(matchSummaryLabels[field] || field)}</div>
                <div style="margin-top:4px;color:#1f2937;font-weight:700;">${values.slice(0, 3).map(value => escapeErpOptionHtml(value)).join('<br>')}</div>
            </div>
        `).join('');

    const salesOrderHtml = salesOrders.length
        ? salesOrders.map(so => `<span style="display:inline-block;margin:2px 6px 2px 0;padding:3px 8px;border-radius:999px;background:#eef2ff;color:#4f46e5;font-size:11px;font-weight:700;">${escapeErpOptionHtml(so)}</span>`).join('')
        : '<span style="color:#94a3b8;">No sales order</span>';

    const itemsHtml = lines.length
        ? lines.map(line => `
            <tr>
                <td style="padding:6px 8px;border:1px solid #e5e7eb;">${escapeErpOptionHtml(line.desc || '-')}</td>
                <td style="padding:6px 8px;border:1px solid #e5e7eb;">${escapeErpOptionHtml(line.ply || '-')}</td>
                <td style="padding:6px 8px;border:1px solid #e5e7eb;text-align:right;">${escapeErpOptionHtml(line.qty || 0)}</td>
                <td style="padding:6px 8px;border:1px solid #e5e7eb;text-align:right;">${escapeErpOptionHtml(line.price || 0)}</td>
                <td style="padding:6px 8px;border:1px solid #e5e7eb;text-align:right;">${escapeErpOptionHtml(line.total || '0.00')}</td>
            </tr>
        `).join('')
        : `<tr><td colspan="5" style="padding:8px;border:1px solid #e5e7eb;color:#94a3b8;">No item lines found.</td></tr>`;

    return `
        <div style="margin-top:12px;padding:12px;border-top:1px solid #dbe3ff;background:#f8fbff;border-radius:12px;">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:10px;margin-bottom:12px;">
                <div style="padding:10px;border:1px solid #dbe3ff;border-radius:10px;background:#fff;">
                    <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;">Matched Search</div>
                    <div style="margin-top:4px;font-weight:700;color:#1f2937;">${escapeErpOptionHtml(matchedQuery || '-')}</div>
                </div>
                <div style="padding:10px;border:1px solid #dbe3ff;border-radius:10px;background:#fff;">
                    <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;">ERP Customer PO</div>
                    <div style="margin-top:4px;font-weight:700;color:#1f2937;">${escapeErpOptionHtml(erpPo || '-')}</div>
                </div>
                <div style="padding:10px;border:1px solid #dbe3ff;border-radius:10px;background:#fff;">
                    <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;">Customer</div>
                    <div style="margin-top:4px;font-weight:700;color:#1f2937;">${escapeErpOptionHtml(option?.customerName || firstGroup?.customerName || '-')}</div>
                </div>
            </div>
            ${matchSummaryHtml ? `<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:10px;margin-bottom:12px;">${matchSummaryHtml}</div>` : ''}
            <div style="font-size:12px;font-weight:700;color:#1f2937;margin-bottom:8px;">Sales Orders</div>
            <div style="margin-bottom:12px;">${salesOrderHtml}</div>
            <div style="font-size:12px;font-weight:700;color:#1f2937;margin-bottom:8px;">ERP Item Details</div>
            <div style="overflow:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:12px;background:#fff;">
                    <thead>
                        <tr>
                            <th style="padding:6px 8px;border:1px solid #e5e7eb;background:#f8fafc;text-align:left;">Description</th>
                            <th style="padding:6px 8px;border:1px solid #e5e7eb;background:#f8fafc;text-align:left;">Type</th>
                            <th style="padding:6px 8px;border:1px solid #e5e7eb;background:#f8fafc;text-align:right;">Qty</th>
                            <th style="padding:6px 8px;border:1px solid #e5e7eb;background:#f8fafc;text-align:right;">Price</th>
                            <th style="padding:6px 8px;border:1px solid #e5e7eb;background:#f8fafc;text-align:right;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>${itemsHtml}</tbody>
                </table>
            </div>
        </div>
    `;
}

function renderErpOptions(pid, query, options) {
    const msg = document.getElementById('erpMsg_' + pid);
    if (!msg) return;

    window.erpChoiceOptions[pid] = options || [];
    msg.innerHTML = `
        <div style="color:#1d4ed8;font-weight:700;margin-bottom:8px;">Multiple ERP POs matched <strong>${escapeErpOptionHtml(query)}</strong>. Please choose one:</div>
        <div id="erpOptionsWrap_${pid}" style="display:flex;flex-direction:column;gap:8px;">
            ${(options || []).map((option, index) => `
                <div class="erp-option-card"
                     data-open="0"
                     style="border:1px solid #dbe3ff;border-radius:14px;background:#fff;padding:12px;">
                    <div onclick="toggleErpOptionDetails('${pid}', ${index})"
                         style="cursor:pointer;">
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;">
                            <div>
                                <div style="font-weight:800;color:#4f46e5;">${escapeErpOptionHtml(option.po || '')}</div>
                                <div style="color:#64748b;margin-top:6px;font-size:12px;">
                                    ${(() => {
                                        const labelMap = {
                                            customer_po_no: 'ERP Customer PO',
                                            remarks: 'Remarks',
                                            item_description: 'Description',
                                            item_code: 'Item Code',
                                            ordered_item: 'Ordered Item'
                                        };
                                        const summary = option.matchSummary || {};
                                        const parts = Object.entries(summary)
                                            .filter(([, values]) => Array.isArray(values) && values.length)
                                            .slice(0, 3)
                                            .map(([field, values]) => {
                                                const label = labelMap[field] || field;
                                                const sample = values[0] || '';
                                                return `<span style="display:inline-block;margin:2px 8px 2px 0;padding:2px 8px;border-radius:999px;background:#f8fafc;border:1px solid #e2e8f0;color:#475569;font-size:11px;">
                                                    Matched in: <strong>${escapeErpOptionHtml(label)}</strong>${sample ? ` · ${escapeErpOptionHtml(sample)}` : ''}
                                                </span>`;
                                            });
                                        return parts.join('');
                                    })()}
                                </div>
                                <div style="color:#1f2937;margin-top:6px;font-size:12px;font-weight:700;">
                                    Sales Order${(option.salesOrders || []).length === 1 ? '' : 's'}:
                                    ${((option.salesOrders || []).length
                                        ? option.salesOrders.map(so => `<span style="display:inline-block;margin:2px 6px 2px 0;padding:2px 8px;border-radius:999px;background:#eef2ff;color:#4f46e5;font-size:11px;font-weight:700;">${escapeErpOptionHtml(so)}</span>`).join('')
                                        : '<span style="color:#94a3b8;font-weight:600;">None</span>')}
                                </div>
                                <div style="color:#64748b;margin-top:6px;">${escapeErpOptionHtml(option.customerName || '-')} · ${escapeErpOptionHtml(option.groupCount || 0)} sales order(s) · ${escapeErpOptionHtml(option.lineCount || 0)} line(s)</div>
                            </div>
                            <div class="erp-option-toggle" style="white-space:nowrap;color:#2563eb;font-size:12px;font-weight:700;">Show details</div>
                        </div>
                    </div>
                    <div class="erp-option-details" style="display:none;">
                        ${erpOptionPreviewHtml(option, query)}
                        <div style="margin-top:12px;display:flex;justify-content:flex-end;">
                            <button type="button"
                                    class="primary-btn"
                                    onclick="chooseErpOption('${pid}', ${index})">
                                Use This PO
                            </button>
                        </div>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
}

function searchErp(pid, appendMode = false) {
    const query = document.getElementById('erpInput_' + pid)?.value?.trim();
    if (!query) return;
    window.erpChoiceAction[pid] = appendMode ? 'append' : 'replace';

    const msg = document.getElementById('erpMsg_' + pid);
    const orderNumbers = [...new Set(query.split(/[\s,;]+/).map(value => value.trim()).filter(Boolean))];
    const invalidOrder = orderNumbers.find(value => !/^\d+$/.test(value));
    if (invalidOrder) {
        if (msg) msg.innerHTML = `<span style="color:#f87171;">ERP error: ${escapeErpOptionHtml(invalidOrder)} is not a valid numeric sales order number.</span>`;
        return;
    }
    if (orderNumbers.length > 1) {
        searchMultipleErpOrders(pid, orderNumbers, appendMode);
        return;
    }
    if (msg) msg.innerHTML = '<span style="color:#94a3b8;">Searching live ERP by sales order...</span>';

    fetch(APP_BASE + '/api/erp_order_proxy.php?order=' + encodeURIComponent(query))
        .then(async response => {
            const erp = await response.json();
            if (!response.ok || erp.error) {
                throw new Error(erp.detail || erp.error || 'Could not search ERP.');
            }
            return erp;
        })
        .then(async erp => {
            if (!erp.found || !Array.isArray(erp.groups) || !erp.groups.length) {
                if (msg) msg.innerHTML = `<span style="color:#f87171;">Sales order <strong>${escapeErpOptionHtml(query)}</strong> was not found in ERP.</span>`;
                return;
            }

            // Do NOT claim the ERP order here. Searching is exploratory — claiming on
            // every search created empty "ghost" work orders that permanently locked
            // ERP orders when the user walked away without saving a PI. The ERP order
            // is claimed only when the PI is actually saved / submitted to Marketing
            // (linkErpOrdersToPiWorkOrder / server-side linkSalesErpOrders). We still
            // warn if this ERP order already belongs to a *different* work order.
            const currentWorkOrderId = sessionStorage.getItem('ats_current_order_id') || '';
            const firstGrp = erp.groups[0] || {};
            const poNumber = firstGrp.customerPo || erp.po || '';
            const items = buildErpLinesForGroupData(erp);
            const poData = {
                poNum: poNumber,
                buyer: firstGrp.buyer || '',
                items,
                salesOrder: firstGrp.salesOrderNo || query,
                reqDate: firstGrp.requestDate || firstGrp.shipDate || '',
                status: firstGrp.status || ''
            };
            const extra = {
                salesOrder: firstGrp.salesOrderNo || query,
                reqDate: firstGrp.requestDate || firstGrp.shipDate || '',
                status: firstGrp.status || ''
            };

            if (appendMode) {
                appendPoToBlock(pid, poData, extra);
            } else {
                fillPoBlock(pid, poData, extra);
            }

            setPiCustomerIfEmpty(firstGrp.customerName || '');
    setPiAddressFromErpIfEmpty(firstGrp);
    setPiMarketingUserByName(firstGrp.salesPerson || '');
            if (msg) {
                const action = appendMode ? 'Added' : 'Loaded';
                msg.innerHTML = `<span style="color:#16a34a;font-weight:700;">${action} sales order ${escapeErpOptionHtml(firstGrp.salesOrderNo || query)}</span> - PO ${escapeErpOptionHtml(poNumber || '-')} - ${items.length} merged line(s).`;
            }

            window.erpChoiceAction[pid] = 'replace';
            const input = document.getElementById('erpInput_' + pid);
            if (input) {
                input.value = '';
                input.placeholder = 'Enter ERP sales order number...';
            }
        })
        .catch(error => {
            if (msg) msg.innerHTML = `<span style="color:#f87171;">ERP error: ${escapeErpOptionHtml(error.message || 'Server unreachable.')}</span>`;
        });
}

function fillPoBlock(pid, po, extra) {
    document.getElementById('salesOrder_' + pid).value    = po.salesOrder || po.salesOrderNo || extra?.salesOrder || '';
    document.getElementById('customerPo_' + pid).value    = po.poNum || '';
    document.getElementById('buyerName_'  + pid).value    = po.buyer || extra?.buyer || '';
    document.getElementById('reqDate_'    + pid).value    = po.reqDate || extra?.reqDate || '';
    document.getElementById('poLabel_'    + pid).textContent = po.poNum || 'New Purchase Order';

    const statusEl = document.getElementById('orderStatus_' + pid);
    if (statusEl) statusEl.value = po.status || extra?.status || statusEl.value;

    // Clear existing rows then fill items
    document.getElementById('tbody_' + pid).innerHTML = '';
    rowCounters[pid] = 0;

    (po.items || []).forEach(item => {
        addRow(pid);
        const sl  = rowCounters[pid];
        const rid = pid + '_' + sl;
        const descEl = document.getElementById('desc_' + rid);
        const plyEl  = document.getElementById('ply_'  + rid);
        const qtyEl  = document.getElementById('qty_'  + rid);
        const prcEl  = document.getElementById('prc_'  + rid);
        const amtEl  = document.getElementById('amt_'  + rid);
        if (descEl) descEl.value = item.desc  || '';
        if (plyEl)  plyEl.value  = item.ply   || '';
        if (qtyEl)  qtyEl.value  = item.qty   || 0;
        if (prcEl)  prcEl.value  = item.price || 0;
        if (amtEl)  amtEl.value  = item.total || ((item.qty * item.price) || 0).toFixed(2);
    });
    calcTotal(pid);
}

function mapMarketingRowToPiItem(row) {
    const qty = parseFloat(row?.qty || 0) || 0;
    const price = parseFloat(row?.unitPrc || 0) || 0;
    const desc = row?.seg2 || row?.itemName || row?.detailExtra?.pn || '';
    const ply = row?.unit || row?.spec1 || '';
    return {
        desc,
        ply,
        qty,
        price,
        total: (qty * price).toFixed(2)
    };
}

async function fetchErpPoData(query) {
    if (!query) return null;
    try {
        const erpRes = await fetch(APP_BASE + '/api/erp_proxy.php?po=' + encodeURIComponent(query));
        const erp = await erpRes.json();
        if (erp.error || !erp.found || !Array.isArray(erp.groups) || !erp.groups.length) return null;

        const firstGrp = erp.groups[0];
        const allLines = buildErpLinesForGroupData(erp);

        return {
            po: {
                poNum: firstGrp.customerPo || query,
                buyer: firstGrp.buyer || '',
                customerName: firstGrp.customerName || '',
                items: allLines
            },
            extra: {
                salesOrder: firstGrp.salesOrderNo || '',
                reqDate: firstGrp.requestDate || firstGrp.shipDate || '',
                status: firstGrp.status || 'Booked'
            }
        };
    } catch (_) {
        return null;
    }
}

async function hydratePiFromMarketingIntake(mkt, salesSnapshot) {
    if (!mkt?.pos?.length) return false;

    const selectedType = salesSnapshot?.piType
        || (mkt.pos.length > 1 ? 'summary' : 'single');
    const radio = document.querySelector(`input[name="piTypeChoice"][value="${selectedType}"]`);
    if (radio) radio.checked = true;
    onPiTypeChange();

    const customer = salesSnapshot?.customer || mkt.customer || '';
    const buyers = [...new Set((mkt.pos || []).map(po => (po.endBuyer || '').trim()).filter(Boolean))];
    const sharedBuyer = salesSnapshot?.buyer || (buyers.length === 1 ? buyers[0] : (buyers[0] || ''));
    const sharedDate = salesSnapshot?.piDate || mkt.intakeDate || new Date().toISOString().split('T')[0];

    document.getElementById('piCustomer').value = customer;
    window._pendingPiCustomer = customer;
    document.getElementById('piBuyer').value = sharedBuyer;
    document.getElementById('piDate').value = sharedDate;
    if (salesSnapshot?.buyerAddress) {
        document.getElementById('piBuyerAddress').value = salesSnapshot.buyerAddress;
    } else if (customer) {
        onPiCustomerChange();
    }

    document.getElementById('poBlocksContainer').innerHTML = '';
    poCount = 0;
    rowCounters = {};

    for (const [idx, po] of (mkt.pos || []).entries()) {
        addPoBlock();
        const blocks = document.querySelectorAll('.po-block');
        const pid = blocks[blocks.length - 1].id.replace('block_', '');
        const erpData = await fetchErpPoData(po.poNum || '');
        const fallbackItems = (po.rows || []).map(mapMarketingRowToPiItem).filter(item => item.desc || item.qty || item.price);
        const sourcePo = erpData?.po || {
            poNum: po.poNum || '',
            buyer: po.endBuyer || '',
            items: fallbackItems
        };
        const sourceExtra = erpData?.extra || {
            salesOrder: po.orderNo || '',
            reqDate: po.delivery || '',
            status: 'Booked'
        };
        fillPoBlock(pid, sourcePo, sourceExtra);
        setPiCustomerIfEmpty(erpData?.po?.customerName);

        const msg = document.getElementById('erpMsg_' + pid);
        if (msg) {
            msg.innerHTML = erpData
                ? '<span style="color:#16a34a;font-weight:700;">Loaded from ERP</span>'
                : '<span style="color:#64748b;">ERP not found - loaded from Marketing Intake. You can still add or edit items.</span>';
        }

        const piNumEl = document.getElementById('piNum_' + pid);
        if (piNumEl && idx === 0) {
            document.getElementById('piNumber').value = piNumEl.value || '';
            document.getElementById('piNumDisplay').textContent = piNumEl.value || '-';
        }
    }

    updateSummary();
    document.getElementById('piStatus').textContent = 'Loaded';
    return true;
}

function clearPo(pid) {
    window.erpChoiceAction[pid] = 'replace';
    ['salesOrder','customerPo','buyerName','reqDate'].forEach(f => {
        const el = document.getElementById(f + '_' + pid);
        if (el) el.value = '';
    });
    document.getElementById('erpInput_' + pid).value = '';
    document.getElementById('erpInput_' + pid).placeholder = 'Enter ERP sales order number...';
    document.getElementById('tbody_'    + pid).innerHTML = '';
    document.getElementById('poLabel_'  + pid).textContent = 'New Purchase Order';
    rowCounters[pid] = 0;
    addRow(pid);
    calcTotal(pid);
    const msg = document.getElementById('erpMsg_' + pid);
    if (msg) msg.textContent = 'Enter a sales order number to load its PO and item rows directly from ERP.';
}

function getSelectedMasterGroups() {
    const groups = {};
    document.querySelectorAll('.mpi-item-chk[data-mpi-source="overview"]:checked').forEach(chk => {
        const pi   = _savedPisOverviewCache[+chk.dataset.pi];
        const po   = (pi?.pos || [])[+chk.dataset.po];
        const item = (po?.items || [])[+chk.dataset.item];
        if (!pi || !po || !item) return;
        const key = chk.dataset.pi + '_' + chk.dataset.po;
        if (!groups[key]) {
            groups[key] = {
                piNumber:           pi.pi_number,
                orderRef:           po.orderRef || po.salesOrder || po.salesOrderNo || '',
                poNum:              po.poNum || po.customerPo || '',
                style:              po.style || '',
                sharedBuyer:        po.sharedBuyer || '',
                sharedBuyerAddress: po.sharedBuyerAddress || '',
                buyer:              po.buyer || '',
                salesOrder:         po.salesOrder || po.salesOrderNo || '',
                status:             po.status || '',
                reqDate:            po.reqDate || '',
                items: []
            };
        }
        groups[key].items.push(item);
    });
    return Object.values(groups);
}

// In-memory editable copy of Master PI groups (authoritative source for the panel).
let _masterEditGroups = [];

function _masterGroupKey(group) {
    return (group.piNumber || '') + '|' + (group.poNum || '') + '|' + (group.orderRef || '');
}

// Merge freshly-selected groups from checkboxes into _masterEditGroups, preserving
// any edits the user already made to existing groups/items.
function _masterMergeGroups(freshGroups) {
    const existing = {};
    _masterEditGroups.forEach(g => { existing[_masterGroupKey(g)] = g; });
    const freshKeys = new Set(freshGroups.map(_masterGroupKey));
    // Remove groups that were unchecked
    _masterEditGroups = _masterEditGroups.filter(g => freshKeys.has(_masterGroupKey(g)));
    // Add new groups (preserve existing edited ones)
    freshGroups.forEach(g => {
        if (!existing[_masterGroupKey(g)]) _masterEditGroups.push(g);
    });
}

function renderMasterSelectedItems() {
    const panel = document.getElementById('masterPiSelectedList');
    if (!panel) return;
    const fresh = getSelectedMasterGroups();
    _masterMergeGroups(fresh);
    _renderMasterEditPanel();
}

function renderMasterSelectedItemsFromGroups(groups) {
    _masterEditGroups = groups.map(g => ({
        ...g,
        items: (g.items || []).map(it => ({ ...it }))
    }));
    _renderMasterEditPanel();
}

// Mutate an item field in _masterEditGroups and refresh totals.
function _masterItemChanged(groupIdx, itemIdx, field, value) {
    const group = _masterEditGroups[groupIdx];
    if (!group) return;
    const item = group.items[itemIdx];
    if (!item) return;
    item[field] = value;
    const qty = parseFloat(item.qty   || 0) || 0;
    const prc = parseFloat(item.price || item.unitPrice || 0) || 0;
    item.total = qty * prc;
    const amtEl = document.getElementById('mbi_amt_' + groupIdx + '_' + itemIdx);
    if (amtEl) amtEl.value = item.total ? item.total.toFixed(2) : '';
    _masterRefreshTotals();
}

function _masterAddItemRow(groupIdx) {
    const group = _masterEditGroups[groupIdx];
    if (!group) return;
    group.items.push({ desc: '', ply: '', qty: 0, price: 0, total: 0 });
    _renderMasterEditPanel();
}

function _masterDelItemRow(groupIdx, itemIdx) {
    const group = _masterEditGroups[groupIdx];
    if (!group) return;
    group.items.splice(itemIdx, 1);
    _renderMasterEditPanel();
}

function _masterRefreshTotals() {
    let tq = 0, tv = 0;
    _masterEditGroups.forEach(g => g.items.forEach(it => {
        tq += parseFloat(it.qty || 0) || 0;
        tv += parseFloat(it.total || 0) || 0;
    }));
    const el = document.getElementById('mbiTotals');
    if (el) el.innerHTML = `<span>Qty: ${tq.toLocaleString()}</span><span>Total: $${tv.toFixed(2)}</span>`;
}

function _renderMasterEditPanel() {
    const panel = document.getElementById('masterPiSelectedList');
    if (!panel) return;
    if (!_masterEditGroups.length) {
        panel.innerHTML = '<div style="padding:18px;border:1px dashed #cbd5e1;border-radius:10px;color:#64748b;background:#f8fafc;">Select one or more items from the order PI list above to build the Master PI.</div>';
        return;
    }

    let html = '';
    _masterEditGroups.forEach((group, groupIdx) => {
        const refParts = [
            group.poNum    ? 'PO # ' + group.poNum    : '',
            group.orderRef ? 'ORDER REF: ' + group.orderRef : '',
            group.style    ? 'Style# ' + group.style   : ''
        ].filter(Boolean);
        const refLabel = refParts.join(' · ');

        let itemRows = '';
        group.items.forEach((item, itemIdx) => {
            const qty = parseFloat(item.qty || 0) || 0;
            const prc = parseFloat(item.price || item.unitPrice || 0) || 0;
            const tot = parseFloat(item.total || (qty * prc)) || 0;
            itemRows += `<tr>
                <td style="text-align:center;font-size:11px;color:#94a3b8;">${itemIdx + 1}</td>
                <td><input value="${escHtml(item.desc || item.itemName || '')}"
                    style="width:100%;padding:4px 6px;border:1.5px solid #e2e8f0;border-radius:5px;font-size:12px;"
                    oninput="_masterItemChanged(${groupIdx},${itemIdx},'desc',this.value)"></td>
                <td><input value="${escHtml(item.ply || '')}"
                    style="width:60px;padding:4px 6px;border:1.5px solid #e2e8f0;border-radius:5px;font-size:12px;"
                    oninput="_masterItemChanged(${groupIdx},${itemIdx},'ply',this.value)"></td>
                <td><input type="number" min="0" value="${qty || ''}"
                    style="width:80px;padding:4px 6px;border:1.5px solid #e2e8f0;border-radius:5px;font-size:12px;"
                    oninput="_masterItemChanged(${groupIdx},${itemIdx},'qty',this.value)"></td>
                <td><input type="number" min="0" step="0.0001" value="${prc || ''}"
                    style="width:80px;padding:4px 6px;border:1.5px solid #e2e8f0;border-radius:5px;font-size:12px;"
                    oninput="_masterItemChanged(${groupIdx},${itemIdx},'price',this.value)"></td>
                <td><input id="mbi_amt_${groupIdx}_${itemIdx}" readonly value="${tot ? tot.toFixed(2) : ''}"
                    style="width:80px;padding:4px 6px;border:1.5px solid #e2e8f0;border-radius:5px;font-size:12px;background:#f8fafc;font-weight:700;color:#4f46e5;"></td>
                <td><button type="button" class="si-del-btn" title="Remove row"
                    onclick="_masterDelItemRow(${groupIdx},${itemIdx})">X</button></td>
            </tr>`;
        });

        html += `<div style="border:1.5px solid #e0e3ff;border-radius:10px;margin-bottom:10px;overflow:hidden;">
            ${refLabel ? `<div style="padding:4px 12px;font-size:11px;color:#64748b;background:#f8fafc;border-bottom:1px solid #e2e8f0;">${escHtml(refLabel)}</div>` : ''}
            <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:12px;">
                <thead><tr style="background:#f1f5f9;">
                    <th style="padding:4px 6px;font-weight:700;color:#64748b;font-size:11px;border-bottom:1px solid #e2e8f0;">SL</th>
                    <th style="padding:4px 6px;font-weight:700;color:#64748b;font-size:11px;text-align:left;border-bottom:1px solid #e2e8f0;">Description</th>
                    <th style="padding:4px 6px;font-weight:700;color:#64748b;font-size:11px;border-bottom:1px solid #e2e8f0;">Ply</th>
                    <th style="padding:4px 6px;font-weight:700;color:#64748b;font-size:11px;border-bottom:1px solid #e2e8f0;">Qty</th>
                    <th style="padding:4px 6px;font-weight:700;color:#64748b;font-size:11px;border-bottom:1px solid #e2e8f0;">Price $</th>
                    <th style="padding:4px 6px;font-weight:700;color:#64748b;font-size:11px;border-bottom:1px solid #e2e8f0;">Amount $</th>
                    <th style="border-bottom:1px solid #e2e8f0;"></th>
                </tr></thead>
                <tbody>${itemRows}</tbody>
            </table>
            </div>
            <div style="padding:4px 8px;">
                <button type="button" class="ghost-btn" style="font-size:11px;padding:2px 10px;"
                    onclick="_masterAddItemRow(${groupIdx})">+ Add Row</button>
            </div>
        </div>`;
    });

    html += `<div id="mbiTotals" style="display:flex;justify-content:flex-end;gap:18px;padding:14px 6px 0;font-size:13px;font-weight:700;color:#312e81;"></div>`;
    panel.innerHTML = html;
    _masterRefreshTotals();
}

// â”€â”€ Collect current PI data â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function collectSalesPageData() {
    const piType = document.querySelector('input[name="piTypeChoice"]:checked')?.value || 'single';
    if (piType === 'master') {
        const masterGroups = (_masterEditGroups.length && !getSelectedMasterGroups().length) ? _masterEditGroups : getSelectedMasterGroups();
        const selectedPiNumbers = [...new Set(masterGroups.map(group => group.piNumber).filter(Boolean))];
        let grandQty = 0;
        let grandVal = 0;
        const pos = masterGroups.map(group => {
            let qty = 0;
            let val = 0;
            group.items.forEach(item => {
                const itemQty = parseFloat(item.qty || 0);
                const itemVal = parseFloat(item.total || (itemQty * parseFloat(item.price || item.unitPrice || 0))) || 0;
                qty += itemQty;
                val += itemVal;
            });
            grandQty += qty;
            grandVal += val;
            return {
                piNum: group.piNumber || '',
                poNum: group.poNum || '',
                qty,
                val: val.toFixed(2),
                items: group.items,
                buyer: group.buyer || '',
                salesOrder: group.salesOrder || '',
                status: group.status || '',
                reqDate: group.reqDate || ''
            };
        });

        return {
            piType,
            piNum: document.getElementById('piNumber').value.trim(),
            customer: document.getElementById('piCustomer').value.trim(),
            buyer: document.getElementById('piBuyer').value.trim(),
            productLine: '',
            piDate: document.getElementById('piDate').value,
            buyerAddress: document.getElementById('piBuyerAddress').value.trim(),
            marketingUserId: document.getElementById('piMarketingUser')?.value || '',
            marketingUserName: (document.getElementById('piMarketingUser')?.selectedOptions?.[0]?.textContent || '').trim(),
            hsCode: document.getElementById('termHsCode')?.value,
            consigneeBank: '',
            advisingBank: '',
            pos,
            masterPiSelection: masterGroups,
            selectedPiNumbers,
            grandQty,
            grandVal: grandVal.toFixed(2)
        };
    }

    const firstPid = document.querySelectorAll('.po-block')[0]?.id.replace('block_', '') || '';
    const piNum    = document.getElementById('piNum_' + firstPid)?.value?.trim()
                  || document.getElementById('piNumber').value.trim();
    const customer   = document.getElementById('piCustomer').value.trim();
    const buyer      = document.getElementById('piBuyer').value.trim();
    const productLine= '';
    const piDate     = document.getElementById('piDate').value;
    const buyerAddress  = document.getElementById('piBuyerAddress').value.trim();
    const consigneeBank = '';
    const advisingBank  = '';

    const blocks = document.querySelectorAll('.po-block');
    const pos = [];
    let grandQty = 0, grandVal = 0;

    blocks.forEach(b => {
        const pid  = b.id.replace('block_', '');
        const qty  = parseFloat(document.getElementById('totQty_' + pid)?.textContent?.replace(/,/g,'') || 0) || 0;
        const val  = parseFloat(document.getElementById('totVal_' + pid)?.textContent?.replace('$','') || 0) || 0;
        const poNum = document.getElementById('customerPo_' + pid)?.value?.trim()
                   || document.getElementById('erpInput_'   + pid)?.value?.trim()
                   || ('PO ' + pid);

        const items = [];
        document.querySelectorAll('#tbody_' + pid + ' tr').forEach(tr => {
            const rid  = tr.id.replace('row_', '');
            const desc = document.getElementById('desc_' + rid)?.value?.trim();
            const ply  = document.getElementById('ply_'  + rid)?.value?.trim();
            const q    = parseFloat(document.getElementById('qty_'  + rid)?.value || 0) || 0;
            const p    = parseFloat(document.getElementById('prc_'  + rid)?.value || 0) || 0;
            if (desc || q) items.push({ desc, ply, qty: q, price: p, total: (q*p).toFixed(2) });
        });

        pos.push({
            piNum: document.getElementById('piNum_' + pid)?.value?.trim() || '',
            piNumManual: !!(document.getElementById('piNum_' + pid) && !document.getElementById('piNum_' + pid).readOnly),
            poNum,
            qty,
            val,
            items,
            buyer: document.getElementById('buyerName_' + pid)?.value?.trim() || '',
            salesOrder: document.getElementById('salesOrder_' + pid)?.value?.trim() || '',
            status: document.getElementById('orderStatus_' + pid)?.value?.trim() || '',
            reqDate: document.getElementById('reqDate_' + pid)?.value || ''
        });
        grandQty += qty;
        grandVal += val;
    });

    return {
        piType,
        piNum,
        customer,
        buyer,
        productLine,
        piDate,
        buyerAddress,
        marketingUserId: document.getElementById('piMarketingUser')?.value || '',
        marketingUserName: (document.getElementById('piMarketingUser')?.selectedOptions?.[0]?.textContent || '').trim(),
        hsCode: document.getElementById('termHsCode')?.value || '',
        consigneeBank,
        advisingBank,
        pos,
        selectedPiNumbers: [...new Set(pos.map(po => po.piNum).filter(Boolean))],
        grandQty,
        grandVal: grandVal.toFixed(2)
    };
}

function collectPiData() {
    return collectSalesPageData();
}

async function createOrUpdateMasterPiRecord(data) {
    const orderId = sessionStorage.getItem('ats_current_order_id') || '';
    if (!orderId) throw new Error('No order loaded.');
    if (!data.pos?.length) throw new Error('Please select at least one item for Master PI.');

    const masterPiNum = (data.piNum && data.piNum !== data.selectedPiNumbers?.[0])
        ? data.piNum
        : (orderId + '-MASTER');

    const payload = {
        piNum: masterPiNum,
        customer: data.customer,
        buyer: data.buyer,
        piDate: data.piDate,
        buyerAddress: data.buyerAddress,
        consigneeBank: data.consigneeBank,
        advisingBank: data.advisingBank,
        productLine: data.productLine || '',
        pos: data.pos,
        grandQty: data.grandQty || 0,
        grandVal: data.grandVal || 0,
        orderId,
        isMaster: true,
        includedPis: data.selectedPiNumbers || []
    };

    const piRes = await fetch(APP_BASE + '/api/pis.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });
    const piJson = await piRes.json();
    if (piJson.error) throw new Error(piJson.error);

    const pageRes = await fetch(APP_BASE + '/api/save_page.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            order_id: orderId,
            page_name: 'sales',
            ...data,
            piType: 'master',
            piNum: masterPiNum
        })
    });
    const pageJson = await pageRes.json();
    if (pageJson.error) throw new Error(pageJson.error);

    return { orderId, masterPiNum };
}

// Create an order lazily at the PI (sales) step — used when starting directly from PI.
async function ensurePiOrder() {
    let orderId = sessionStorage.getItem('ats_current_order_id') || '';
    if (orderId) return orderId;
    try {
        const r = await fetch(APP_BASE + '/api/order_lookup.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ step: 'sales' })
        });
        const res = await r.json();
        if (!res.ok) { alert('Could not create order. Try again.'); return ''; }
        orderId = res.order_id;
        sessionStorage.setItem('ats_current_order_id', orderId);
        sessionStorage.removeItem('ats_new_order');
        const display = document.getElementById('oidDisplay');
        if (display) display.textContent = orderId;
        const stepEl = document.getElementById('oidStep');
        if (stepEl) stepEl.textContent = 'Step: PI';
        return orderId;
    } catch (e) { alert('Server error creating order.'); return ''; }
}

// Associate every ERP sales order included in this PI with the same internal
// work order. This is required for a PI started with "+ New Order", because the
// ERP orders are selected before the internal order is created.
async function linkErpOrdersToPiWorkOrder(data, orderId) {
    const erpOrderNumbers = [...new Set((data.pos || []).flatMap(po => {
        if (Array.isArray(po.salesOrders) && po.salesOrders.length) {
            return po.salesOrders;
        }
        return String(po.salesOrder || '').split(/\s*,\s*/);
    }).map(value => String(value || '').trim()).filter(value => /^\d+$/.test(value)))];

    for (const erpOrderNo of erpOrderNumbers) {
        const response = await fetch(APP_BASE + '/api/erp_order_import.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ sale_order_no: erpOrderNo, work_order_id: orderId })
        });
        const result = await response.json();
        if (!response.ok || result.error) {
            throw new Error(result.error || ('Could not link ERP order ' + erpOrderNo + ' to this PI.'));
        }
    }
}

// â”€â”€ Save PI to database â€” one PI record per PO block â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
savePi = async function() {
    let data;
    try { data = collectPiData(); } catch(e) { console.error('collectPiData error:', e); alert('Form read error: ' + e.message); return; }
    const piType = document.querySelector('input[name="piTypeChoice"]:checked')?.value || 'single';
    let orderId = sessionStorage.getItem('ats_current_order_id') || '';
    // Start-from-PI: create the order lazily (at the PI step) on first save
    if (!orderId) {
        orderId = await ensurePiOrder();
        if (!orderId) return;
    }

    if (piType === 'master') {
        if (!data.pos.length) { alert('Please select at least one item for Master PI.'); return; }
        try {
            const saved = await createOrUpdateMasterPiRecord(data);
            document.getElementById('piStatus').textContent = 'Saved';
            refreshSavedPiBadge();
            renderOrderPiOverview(saved.orderId);
            alert('Master PI created: ' + saved.masterPiNum);
        } catch (e) {
            console.error('save master selection error:', e);
            alert('Save failed: ' + (e.message || 'Could not reach server. Check your connection.'));
        }
        return;
    }

    // Validate every PO block has a PI number
    const missingNum = data.pos.findIndex(po => !po.piNum);
    if (missingNum !== -1) { alert('Please enter a PI Number for PO ' + (missingNum + 1) + '.'); return; }

    try {
        // Save each PO block as its own PI record
        let savedCount = 0, totalQty = 0, totalVal = 0;
        for (const po of data.pos) {
            // Embed shared header fields into pos JSON so print pages can read them
            // even when the salesPg snapshot is unavailable
            const poWithShared = {
                ...po,
                sharedBuyer:       data.buyer,
                sharedBuyerAddress: data.buyerAddress
            };
            const perPi = {
                piNum:       po.piNum,
                piNumberManual: !!po.piNumManual,
                customer:    data.customer,
                buyer:       data.buyer,
                piDate:      data.piDate,
                buyerAddress: data.buyerAddress,
                consigneeBank: data.consigneeBank,
                advisingBank:  data.advisingBank,
                pos:         [poWithShared],
                grandQty:    po.qty,
                grandVal:    parseFloat(po.val || 0).toFixed(2),
                orderId
            };
            const piRes  = await fetch(APP_BASE + '/api/pis.php', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(perPi) });
            const piJson = await piRes.json();
            if (piJson.error) throw new Error(piJson.error + ' (PI: ' + po.piNum + ')');
            savedCount++;
            totalQty += po.qty;
            totalVal += parseFloat(po.val || 0);
        }

        if (orderId) {
            const pageRes = await fetch(APP_BASE + '/api/save_page.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ order_id: orderId, page_name: 'sales', ...data })
            });
            const pageJson = await pageRes.json();
            if (pageJson.error) alert('PIs saved, but Sales page snapshot could not be saved: ' + pageJson.error);
        }

        document.getElementById('piStatus').textContent = 'Saved';
        refreshSavedPiBadge();
        renderOrderPiOverview(orderId);
        alert(savedCount + ' PI(s) saved · ' + totalQty.toLocaleString() + ' pcs · $' + totalVal.toFixed(2));
    } catch (e) {
        console.error('savePi error:', e);
        alert('Save failed: ' + (e.message || 'Could not reach server. Check your connection.'));
    }
};

async function submitToMarketing() {
    if (hasMarketingApproval()) {
        alert('This PI is already approved. Use Submit to LC to continue.');
        return;
    }
    if (isWaitingForMarketingApproval()) {
        alert('This PI is already waiting for Marketing approval.');
        return;
    }
    let data;
    try { data = collectPiData(); } catch(e) { console.error('collectPiData error:', e); alert('Form read error: ' + e.message); return; }
    const piType = document.querySelector('input[name="piTypeChoice"]:checked')?.value || 'single';
    if (piType === 'master' && !data.pos.length) { alert('Please select at least one item for Master PI.'); return; }

    if (piType !== 'master') {
        const missingNum = data.pos.findIndex(po => !po.piNum);
        if (missingNum !== -1) { alert('Please enter a PI Number for PI ' + (missingNum + 1) + '.'); return; }
        // Any manually-typed PI number in an editable block must be unique.
        const manualBlocks = document.querySelectorAll('.po-block');
        for (const b of manualBlocks) {
            const bpid = b.id.replace('block_', '');
            const inp  = document.getElementById('piNum_' + bpid);
            if (inp && !inp.readOnly) {
                const ok = await checkPiNumDuplicate(bpid);
                if (!ok) { alert('Duplicate PI number "' + inp.value.trim() + '". Please choose a unique PI number before submitting.'); inp.focus(); return; }
            }
        }
    }

    // A marketing approver must be chosen so the approval goes to the right person.
    if (!data.marketingUserId) {
        alert('Please select the Marketing Person (Approver) before submitting.');
        document.getElementById('piMarketingUser')?.focus();
        return;
    }

    const btn = document.getElementById('universalSaveBtn');
    if (btn) { btn.textContent = 'Submitting...'; btn.disabled = true; }

    let orderId = sessionStorage.getItem('ats_current_order_id') || '';
    if (!orderId) {
        orderId = await ensurePiOrder();
        if (!orderId) { if (btn) { btn.textContent = 'Submit'; btn.disabled = false; } return; }
    }

    try {
        // Link all ERP orders before the handoff. They remain in Commercial's
        // notifications while this work order is at Sales, then clear together
        // after the successful move to Marketing below.
        await linkErpOrdersToPiWorkOrder(data, orderId);

        let savedCount = 0, totalQty = 0, totalVal = 0;
        if (piType !== 'master') {
            // Save each PI block as its own PI record
            for (const po of data.pos) {
                const perPi = {
                    piNum: po.piNum, piNumberManual: !!po.piNumManual,
                    customer: data.customer, buyer: data.buyer,
                    piDate: data.piDate, buyerAddress: data.buyerAddress,
                    consigneeBank: data.consigneeBank, advisingBank: data.advisingBank,
                    pos: [po], grandQty: po.qty,
                    grandVal: parseFloat(po.val || 0).toFixed(2), orderId
                };
                const piRes  = await fetch(APP_BASE + '/api/pis.php', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(perPi) });
                const piJson = await piRes.json();
                if (piJson.error) throw new Error(piJson.error + ' (PI: ' + po.piNum + ')');
                savedCount++; totalQty += po.qty; totalVal += parseFloat(po.val || 0);
            }
        } else {
            savedCount = data.pos.length;
            totalQty = data.grandQty || 0;
            totalVal = parseFloat(data.grandVal || 0);
        }

        // The Marketing handoff must finish before we show "Submitted".
        if (orderId) {
            const pageRes = await fetch(APP_BASE + '/api/save_page.php', {
                method: 'POST', headers: {'Content-Type':'application/json'},
                body: JSON.stringify({ order_id: orderId, page_name: 'sales', ...data })
            });
            const pageText = await pageRes.text();
            let pageJson = {};
            try { pageJson = pageText ? JSON.parse(pageText) : {}; }
            catch (err) { throw new Error('Sales page snapshot returned invalid response.'); }
            if (!pageRes.ok || pageJson.error) {
                throw new Error(pageJson.error || 'Sales page snapshot could not be saved.');
            }

            const firstPo = (data.pos && data.pos[0]) || {};
            // PI submit hands the order to Marketing for approval.
            // Marketing approval returns it to PI so Commercial can print/create Summary/Master PI.
            const orderParams = new URLSearchParams({ id: orderId, step: 'marketing' });
            if (data.customer) orderParams.set('customer', data.customer);
            if (data.buyer)    orderParams.set('buyer', data.buyer);
            if (firstPo.poNum) orderParams.set('po', firstPo.poNum);
            // Sales person → shown on the dashboard's Sales Person column.
            // Route the approval notification to the selected marketing person.
            if (data.marketingUserId) orderParams.set('marketing_user', data.marketingUserId);
            const orderRes = await fetch(APP_BASE + '/api/orders.php?' + orderParams.toString(), { method: 'PUT' });
            const orderText = await orderRes.text();
            let orderJson = {};
            try { orderJson = orderText ? JSON.parse(orderText) : {}; }
            catch (err) { throw new Error('Marketing handoff returned invalid response.'); }
            if (!orderRes.ok || orderJson.error) {
                throw new Error(orderJson.error || 'Could not send PI to Marketing.');
            }
        }

        _currentPiStep = 'marketing';
        document.getElementById('piStatus').textContent = 'Submitted';
        const stepEl = document.getElementById('oidStep');
        if (stepEl) stepEl.textContent = 'Step: Marketing';
        updatePrintLock('marketing', true);
        refreshSavedPiBadge();
        renderOrderPiOverview(orderId);
        if (btn) {
            btn.textContent = 'Waiting for Marketing Approval';
            btn.style.background = '#94a3b8';
            btn.disabled = true;
        }
        window.location.href = APP_BASE + '/pages/notifications.php';
    } catch (e) {
        console.error('submitToMarketing error:', e);
        if (btn) {
            onPiTypeChange();
            btn.disabled = false;
        }
        alert('Submit failed: ' + (e.message || 'Could not reach server. Check your connection.'));
    }
}

async function submitApprovedPiToLc() {
    if (_currentPiStep !== 'sales') {
        // Order already moved to LC (or beyond) — just open the LC page.
        goToLcPage();
        return;
    }
    if (!hasMarketingApproval()) {
        alert('Marketing approval is required before sending this order to LC.');
        return;
    }

    const orderId = sessionStorage.getItem('ats_current_order_id') || '';
    if (!orderId) {
        alert('No order loaded.');
        return;
    }
    // The single PI is already saved and Marketing-approved. Confirm a saved PI
    // exists (fetch once if the cache hasn't loaded yet) — but do NOT re-save it.
    if (!_savedPisCache.length) {
        try {
            const pis = await (await fetch(APP_BASE + '/api/pis.php?order_id=' + encodeURIComponent(orderId))).json();
            _savedPisCache = (pis || []).filter(p => !p.is_master);
        } catch (_) {}
    }
    if (!_savedPisCache.length) {
        alert('No saved PI found for this order.');
        return;
    }

    const btn = document.getElementById('universalSaveBtn');
    if (btn) {
        btn.textContent = 'Sending to LC...';
        btn.disabled = true;
    }

    try {
        // Do NOT re-save the PI here — it is already saved and Marketing-approved,
        // so re-collecting a partially-loaded form could overwrite the approved PI.
        // Just advance the work order to LC.
        const response = await fetch(
            APP_BASE + '/api/orders.php?id=' + encodeURIComponent(orderId) + '&step=lc',
            { method: 'PUT' }
        );
        const text = await response.text();
        let json = {};
        try { json = text ? JSON.parse(text) : {}; }
        catch (_) { throw new Error('LC handoff returned an invalid response.'); }
        if (!response.ok || json.error) {
            throw new Error(json.error || 'Could not send the approved PI to LC.');
        }

        _currentPiStep = 'lc';
        document.getElementById('piStatus').textContent = 'Sent to LC';
        const stepEl = document.getElementById('oidStep');
        if (stepEl) stepEl.textContent = 'Step: LC';
        window.location.href = APP_BASE + '/pages/lc.php';
    } catch (e) {
        console.error('submitApprovedPiToLc error:', e);
        alert('Submit to LC failed: ' + (e.message || 'Could not reach server.'));
        resetSubmitBtn();
    }
}

// ── Edit an already Marketing-approved PI ────────────────────────────────────
// Re-fetch LIVE ERP for every ERP sales order already saved on this PI, refresh
// the item rows, and put the form into edit mode. Re-submitting resets approval.
async function startEditApprovedPi() {
    if (!hasMarketingApproval()) { alert('Only a Marketing-approved PI can be edited here.'); return; }
    const btn = document.getElementById('editPiBtn');
    if (btn) { btn.textContent = 'Refreshing from ERP…'; btn.disabled = true; }

    // Every PO block that carries saved ERP sales orders is refreshed as one
    // atomic group. Each number calls the live-first ERP endpoint; the form is
    // changed only after every linked ERP order has returned successfully.
    const blocks = Array.from(document.querySelectorAll('.po-block'));
    let refreshed = 0;
    const failures = [];
    for (const b of blocks) {
        const pid = b.id.replace('block_', '');
        const soEl = document.getElementById('salesOrder_' + pid);
        const nums = getFieldListValue(soEl); // handles comma / multi lists
        if (!nums.length) continue;
        const result = await searchMultipleErpOrders(pid, nums, false);
        if (result?.success) refreshed += result.orderCount || nums.length;
        else failures.push(...nums);
    }

    if (!refreshed) {
        if (btn) { btn.textContent = 'Edit PI'; btn.disabled = false; }
        alert(failures.length
            ? 'Could not refresh the linked ERP order(s): ' + failures.join(', ')
            : 'No linked ERP sales order was found on this PI.');
        return;
    }

    _editingApprovedPi = true;
    document.getElementById('piStatus').textContent = 'Editing (ERP refreshed)';
    resetSubmitBtn();
    if (btn) { btn.textContent = 'Edit PI'; btn.disabled = false; }
    alert('Latest ERP data loaded for ' + refreshed + ' order(s)' + (failures.length ? ' (' + failures.length + ' failed)' : '') +
          '.\nReview the items, add an optional note, then Submit Revision to Marketing.');
}

// Submit an edited (previously approved) PI: save with history + note, reset the
// Marketing approval, and hand back to Marketing. PI number is unchanged.
async function submitEditedPiToMarketing() {
    const orderId = sessionStorage.getItem('ats_current_order_id') || '';
    if (!orderId) { alert('No order loaded.'); return; }

    let data;
    try { data = collectPiData(); } catch (e) { alert('Form read error: ' + e.message); return; }
    if (!data.marketingUserId) {
        alert('Please select the Marketing Person (Approver) before submitting.');
        document.getElementById('piMarketingUser')?.focus();
        return;
    }
    const editNote = (document.getElementById('editNoteInput')?.value || '').trim();

    const btn = document.getElementById('universalSaveBtn');
    if (btn) { btn.textContent = 'Submitting revision…'; btn.disabled = true; }

    try {
        // Re-save each PI record with isEdit=true so the API snapshots the prior
        // version into history_json and keeps the SAME pi number.
        for (const po of (data.pos || [])) {
            if (!po.piNum) continue;
            const perPi = {
                piNum: po.piNum, customer: data.customer, buyer: data.buyer,
                piDate: data.piDate, buyerAddress: data.buyerAddress,
                pos: [{ ...po, sharedBuyer: data.buyer, sharedBuyerAddress: data.buyerAddress }],
                grandQty: po.qty, grandVal: parseFloat(po.val || 0).toFixed(2),
                orderId, isEdit: true, editNote
            };
            const r = await fetch(APP_BASE + '/api/pis.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(perPi)
            });
            const j = await r.json();
            if (j.error) throw new Error(j.error + ' (PI: ' + po.piNum + ')');
        }

        // Refresh the sales snapshot.
        await fetch(APP_BASE + '/api/save_page.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId, page_name: 'sales', ...data })
        });

        // Reset the Marketing approval so it must be re-approved.
        await fetch(APP_BASE + '/api/save_page.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                order_id: orderId, page_name: 'marketing',
                marketingApproved: false, piApprovalStatus: 'pending', editNote
            })
        });

        // Hand back to Marketing for approval.
        const params = new URLSearchParams({ id: orderId, step: 'marketing' });
        if (data.marketingUserId) params.set('marketing_user', data.marketingUserId);
        const orderRes = await fetch(APP_BASE + '/api/orders.php?' + params.toString(), { method: 'PUT' });
        const orderJson = await orderRes.json().catch(() => ({}));
        if (!orderRes.ok || orderJson.error) throw new Error(orderJson.error || 'Could not resubmit to Marketing.');

        _editingApprovedPi = false;
        _marketingApproved = false;
        _currentPiStep = 'marketing';
        document.getElementById('piStatus').textContent = 'Resubmitted';
        window.location.href = APP_BASE + '/pages/notifications.php';
    } catch (e) {
        console.error('submitEditedPiToMarketing error:', e);
        alert('Resubmit failed: ' + (e.message || 'Could not reach server.'));
        if (btn) { btn.disabled = false; }
        resetSubmitBtn();
    }
}

function clearPiForm() {
    if (!confirm('Clear the current PI form?')) return;
    resetPiFormFields();
}
// Reset the PI form without prompting (used by the New Order flow, which already confirmed).
function resetPiFormFields() {
    // Free any PI numbers this session was holding so they're reusable.
    Object.keys(window._piReserveTokens || {}).forEach(pid => releasePiReservation(pid));
    document.getElementById('poBlocksContainer').innerHTML = '';
    document.getElementById('piNumber').value = '';
    document.getElementById('piNumDisplay').textContent = '-';
    document.getElementById('piCustomer').value = '';
    // Clear ALL shared header fields too — otherwise the previous order's buyer,
    // date, address and marketing person (and its saved snapshot) bleed into the
    // new order, which also re-attached its ERP orders on save.
    const buyerEl = document.getElementById('piBuyer');          if (buyerEl) buyerEl.value = '';
    const addrEl  = document.getElementById('piBuyerAddress');   if (addrEl)  addrEl.value  = '';
    const mkEl    = document.getElementById('piMarketingUser');  if (mkEl)    mkEl.value    = '';
    const dateEl  = document.getElementById('piDate');           if (dateEl)  dateEl.value  = new Date().toISOString().split('T')[0];
    // Drop stale cross-order state carried from the previously loaded order.
    window._salesSnapshot = null;
    window._pendingPiCustomer = '';
    _summarySelectedPis = [];
    _masterEditGroups = [];
    _savedPisCache = [];
    _savedPisOverviewCache = [];
    _savedPisModalCache = [];
    document.getElementById('piStatus').textContent = 'Draft';
    poCount = 0; rowCounters = {};
    addPoBlock();
    updateSummary();
}

// â”€â”€ Master PI â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

// Cached PI lists from the two selection surfaces
let _savedPisOverviewCache = [];
let _savedPisModalCache    = [];
let _savedPisCache         = [];

function refreshSavedPiBadge() {
    const orderId = sessionStorage.getItem('ats_current_order_id') || '';
    const badge = document.getElementById('savedPiCountBadge');
    const cnt   = document.getElementById('savedPiCount');
    // A new/unsaved order has no order id yet — it has zero saved PIs.
    // (Never fall back to fetching ALL PIs, or a fresh order shows a stale count.)
    if (!orderId) {
        _savedPisCache = [];
        if (badge) badge.style.display = 'none';
        return;
    }
    fetch(APP_BASE + '/api/pis.php?order_id=' + encodeURIComponent(orderId))
        .then(r => r.json())
        .then(pis => {
            _savedPisCache = (pis || []).filter(p => !p.is_master);
            if (_savedPisCache.length > 0) {
                badge.style.display = 'inline-block';
                cnt.textContent = _savedPisCache.length;
                updatePrintLock(_currentPiStep, true);
            } else {
                badge.style.display = 'none';
            }
        })
        .catch(() => {});
}

// ── Summary PI builder — assemble already-created PIs across work orders ──────
let _summarySelectedPis = [];

function _summaryPiKey(pi) { return String(pi.id || pi.pi_number || ''); }

function enterSummaryMode() {
    // Pre-seed with the current order's already-created individual PIs (once), so
    // the loaded work order's PI is there by default; user can add more / remove.
    if (!_summarySelectedPis.length && Array.isArray(_savedPisCache) && _savedPisCache.length) {
        _savedPisCache.forEach(pi => { if (!pi.is_master) _summarySelectedPis.push(pi); });
    }
    renderSummaryBasket();
}

async function addSummaryPi() {
    const input = document.getElementById('summarySearchInput');
    const msg   = document.getElementById('summaryMsg');
    const term  = (input?.value || '').trim();
    if (!term) return;
    if (msg) msg.innerHTML = '<span style="color:#94a3b8;">Searching…</span>';
    try {
        // A work-order id (ORD-…) adds every already-created PI belonging to it.
        if (/^ORD-/i.test(term)) {
            const list = await (await fetch(APP_BASE + '/api/pis.php?order_id=' + encodeURIComponent(term))).json();
            const orderPis = Array.isArray(list) ? list.filter(p => !p.is_master) : [];
            if (!orderPis.length) {
                if (msg) msg.innerHTML = `<span style="color:#f87171;">No already-created PI found for work order <strong>${escHtml(term)}</strong>.</span>`;
                return;
            }
            let added = 0;
            orderPis.forEach(pi => {
                const key = _summaryPiKey(pi);
                if (!_summarySelectedPis.some(p => _summaryPiKey(p) === key)) { _summarySelectedPis.push(pi); added++; }
            });
            if (msg) msg.innerHTML = added
                ? `<span style="color:#16a34a;font-weight:700;">Added ${added} PI(s) from ${escHtml(term)}.</span>`
                : `<span style="color:#d97706;">All PIs from ${escHtml(term)} are already added.</span>`;
            if (input) input.value = '';
            renderSummaryBasket();
            return;
        }
        const resp = await fetch(APP_BASE + '/api/pis.php?q=' + encodeURIComponent(term));
        const text = await resp.text();
        let res;
        try { res = JSON.parse(text); }
        catch (_) {
            console.error('addSummaryPi: non-JSON response', resp.status, text.slice(0, 300));
            if (msg) msg.innerHTML = `<span style="color:#f87171;">Search error (HTTP ${resp.status}). See console.</span>`;
            return;
        }
        const pi = (res && (res.match === 'pi' || res.match === 'po')) ? res.pi : null;
        if (!pi) {
            if (msg) msg.innerHTML = `<span style="color:#f87171;">No already-created PI found for <strong>${escHtml(term)}</strong>.</span>`;
            return;
        }
        // Normalize: the ?q= search returns pos already decoded, but be defensive.
        if (typeof pi.pos === 'string') { try { pi.pos = JSON.parse(pi.pos) || []; } catch (_) { pi.pos = []; } }
        const key = _summaryPiKey(pi);
        if (_summarySelectedPis.some(p => _summaryPiKey(p) === key)) {
            if (msg) msg.innerHTML = `<span style="color:#d97706;">${escHtml(pi.pi_number || term)} is already added.</span>`;
        } else {
            _summarySelectedPis.push(pi);
            if (msg) msg.innerHTML = `<span style="color:#16a34a;font-weight:700;">Added ${escHtml(pi.pi_number || term)}.</span>`;
        }
        if (input) input.value = '';
        renderSummaryBasket();
    } catch (e) {
        console.error('addSummaryPi failed', e);
        if (msg) msg.innerHTML = `<span style="color:#f87171;">Search failed: ${escHtml(e.message || 'network error')}.</span>`;
    }
}

function removeSummaryPi(key) {
    _summarySelectedPis = _summarySelectedPis.filter(p => _summaryPiKey(p) !== key);
    renderSummaryBasket();
}

// Persist the assembled Summary to the current order's sales snapshot so it is
// saved (not just previewed) and is restored when the order is reloaded. Merges
// onto the existing snapshot so single-PI data isn't clobbered.
async function saveSummaryPi() {
    const orderId = sessionStorage.getItem('ats_current_order_id') || '';
    if (!orderId) { alert('Load an order first.'); return; }
    if (!_summarySelectedPis.length) { alert('Add at least one PI to the summary first.'); return; }

    let grandQty = 0, grandVal = 0;
    _summarySelectedPis.forEach(pi => {
        grandQty += parseFloat(pi.grand_qty || 0) || 0;
        grandVal += parseFloat(pi.grand_val || 0) || 0;
    });
    const first   = _summarySelectedPis[0] || {};
    const firstPo = (first.pos || [])[0] || {};
    const data = {
        ...(window._salesSnapshot || {}),
        piType: 'summary',
        summarySelectedPis: _summarySelectedPis,
        selectedPiNumbers: _summarySelectedPis.map(p => p.pi_number).filter(Boolean),
        customer: document.getElementById('piCustomer').value.trim() || first.customer || '',
        buyer: document.getElementById('piBuyer').value.trim() || firstPo.sharedBuyer || '',
        piDate: document.getElementById('piDate').value || first.pi_date || '',
        buyerAddress: document.getElementById('piBuyerAddress').value.trim() || firstPo.sharedBuyerAddress || '',
        grandQty,
        grandVal: grandVal.toFixed(2),
    };

    const msg = document.getElementById('summaryMsg');
    if (msg) msg.innerHTML = '<span style="color:#94a3b8;">Saving…</span>';
    try {
        const r = await fetch(APP_BASE + '/api/save_page.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId, page_name: 'sales', ...data })
        });
        const text = await r.text();
        let j = {};
        try { j = text ? JSON.parse(text) : {}; } catch (_) { throw new Error('Save returned an invalid response.'); }
        if (!r.ok || j.error) throw new Error(j.error || 'Save failed.');
        window._salesSnapshot = data; // keep local snapshot in sync
        document.getElementById('piStatus').textContent = 'Saved';
        renderOrderPiOverview(orderId); // show the saved Summary row in "PIs for this Order"
        if (msg) msg.innerHTML = `<span style="color:#16a34a;font-weight:700;">Summary saved · ${_summarySelectedPis.length} PI(s) · Qty ${grandQty.toLocaleString()} · $${grandVal.toFixed(2)}.</span>`;
    } catch (e) {
        console.error('saveSummaryPi failed', e);
        if (msg) msg.innerHTML = `<span style="color:#f87171;">Save failed: ${escHtml(e.message || 'server error')}.</span>`;
    }
}

// Build Master PI data from the items ticked in the overview (works regardless of
// the current PI-type radio — the summary flow selects items while in Summary mode).
function collectMasterDataFromSelection() {
    const masterGroups = getSelectedMasterGroups();
    const selectedPiNumbers = [...new Set(masterGroups.map(g => g.piNumber).filter(Boolean))];
    let grandQty = 0, grandVal = 0;
    const pos = masterGroups.map(group => {
        let qty = 0, val = 0;
        (group.items || []).forEach(item => {
            const iq = parseFloat(item.qty || 0) || 0;
            const iv = parseFloat(item.total || (iq * parseFloat(item.price || item.unitPrice || 0))) || 0;
            qty += iq; val += iv;
        });
        grandQty += qty; grandVal += val;
        return {
            piNum: group.piNumber || '', poNum: group.poNum || '',
            qty, val: val.toFixed(2), items: group.items || [],
            buyer: group.buyer || group.sharedBuyer || '',
            salesOrder: group.salesOrder || '', status: group.status || '', reqDate: group.reqDate || ''
        };
    });
    const firstGroup = masterGroups[0] || {};
    return {
        piType: 'master',
        piNum: (document.getElementById('piNumber').value || '').trim(),
        customer: (document.getElementById('piCustomer').value || '').trim(),
        buyer: (document.getElementById('piBuyer').value || '').trim() || firstGroup.sharedBuyer || firstGroup.buyer || '',
        piDate: document.getElementById('piDate').value || '',
        buyerAddress: (document.getElementById('piBuyerAddress').value || '').trim() || firstGroup.sharedBuyerAddress || '',
        hsCode: document.getElementById('termHsCode')?.value || '',
        consigneeBank: '', advisingBank: '',
        pos, masterPiSelection: masterGroups, selectedPiNumbers,
        grandQty, grandVal: grandVal.toFixed(2)
    };
}

// Create + save the Master PI from the ticked items as a PI *record only*.
// IMPORTANT: do NOT overwrite the order's sales snapshot — that would wipe the
// saved Summary and make its PIs disappear. The new Master just shows up as a
// MASTER row alongside the Summary; no navigation to the print page.
async function createMasterFromSelection() {
    const groups = getSelectedMasterGroups();
    if (!groups.length) { alert('Tick at least one item under the PIs above to include in the Master PI.'); return; }
    const orderId = sessionStorage.getItem('ats_current_order_id') || '';
    if (!orderId) { alert('Load an order first.'); return; }

    const data = collectMasterDataFromSelection();
    const masterPiNum = (data.piNum && data.piNum !== data.selectedPiNumbers?.[0])
        ? data.piNum
        : (orderId + '-MASTER');
    const payload = {
        piNum: masterPiNum,
        customer: data.customer, buyer: data.buyer, piDate: data.piDate,
        buyerAddress: data.buyerAddress, consigneeBank: '', advisingBank: '', productLine: '',
        pos: data.pos, grandQty: data.grandQty, grandVal: data.grandVal,
        orderId, isMaster: true, includedPis: data.selectedPiNumbers || []
    };
    try {
        const r = await fetch(APP_BASE + '/api/pis.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const j = await r.json();
        if (j.error) throw new Error(j.error);
        sessionStorage.setItem('mpi_custom_items', JSON.stringify(data.masterPiSelection || []));
        document.getElementById('piStatus').textContent = 'Master PI Saved';
        refreshSavedPiBadge();
        renderOrderPiOverview(orderId); // Summary snapshot untouched → Summary PIs stay listed + new MASTER row
        const basket = document.getElementById('mpiBasket');
        if (basket) basket.style.display = 'none';
        alert('Master PI created: ' + masterPiNum + ' · $' + data.grandVal + '\nUse the Print button on the Master row when you are ready.');
    } catch (e) {
        console.error('createMasterFromSelection failed', e);
        alert('Master PI create failed: ' + (e.message || 'Could not reach server.'));
    }
}

// Open the printable Master PI (loads the saved Master for the current order).
// Delete the saved Summary PI (clears piType/summarySelectedPis from the sales snapshot).
async function deleteSummaryPi(orderId) {
    if (!confirm('Delete the saved Summary PI for this order?\nThis removes the summary — the individual PIs are kept.')) return;
    try {
        const data = { ...(window._salesSnapshot || {}), piType: null, summarySelectedPis: [], selectedPiNumbers: [] };
        await fetch(APP_BASE + '/api/save_page.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId, page_name: 'sales', ...data })
        });
        window._salesSnapshot = data;
        _summarySelectedPis = [];
        renderOrderPiOverview(orderId);
        document.getElementById('piStatus').textContent = 'Summary deleted';
    } catch (e) { alert('Delete failed: ' + (e.message || 'server error')); }
}

// Delete a Master PI record from the DB.
async function deleteMasterPi(piId, orderId) {
    if (!confirm('Delete this Master PI? This cannot be undone.')) return;
    try {
        const r = await fetch(APP_BASE + '/api/pis.php?id=' + encodeURIComponent(piId), { method: 'DELETE' });
        const d = await r.json();
        if (!d.ok) throw new Error(d.error || 'Delete failed');
        renderOrderPiOverview(orderId);
        document.getElementById('piStatus').textContent = 'Master PI deleted';
    } catch (e) { alert('Delete failed: ' + (e.message || 'server error')); }
}

function printMasterPi() {
    const days = document.getElementById('termLcDays')?.value || '90';
    const tol  = document.getElementById('termTolerance')?.value || '5';
    const hs   = document.getElementById('termHsCode')?.value || '';
    const docMust = document.getElementById('termDocMust')?.value || 'UD';
    const bnk  = document.getElementById('termBank')?.value || 'ncc';
    window.location.href = APP_BASE + '/pages/master-pi.php?days=' + encodeURIComponent(days)
        + '&lctype=Sight&tol=' + encodeURIComponent(tol)
        + '&hs=' + encodeURIComponent(hs)
        + '&doc=' + encodeURIComponent(docMust)
        + '&bank=' + encodeURIComponent(bnk);
}

function _summaryUpdateTopBar(count, qty, val) {
    // Reflect the summary in the top summary bar (PO-block totals are irrelevant here).
    const sc = document.getElementById('sumPoCount');  if (sc) sc.textContent = count;
    const sq = document.getElementById('sumTotalQty'); if (sq) sq.textContent = (qty || 0).toLocaleString();
    const sv = document.getElementById('sumTotalVal'); if (sv) sv.textContent = '$' + (val || 0).toFixed(2);
}

// Called from inline item inputs to propagate edits back into _summarySelectedPis
// and refresh grand totals.
function _summaryItemChanged(piKey, poIdx, itemIdx, field, value) {
    const pi = _summarySelectedPis.find(p => _summaryPiKey(p) === piKey);
    if (!pi) return;
    const item = (pi.pos?.[poIdx]?.items || [])[itemIdx];
    if (!item) return;
    item[field] = value;
    // Recompute item total
    const qty = parseFloat(item.qty   || 0) || 0;
    const prc = parseFloat(item.price || item.unitPrice || 0) || 0;
    item.total = qty * prc;
    // Recompute PI grand totals
    let gq = 0, gv = 0;
    (pi.pos || []).forEach(po => (po.items || []).forEach(it => {
        gq += parseFloat(it.qty || 0) || 0;
        gv += parseFloat(it.total || 0) || 0;
    }));
    pi.grand_qty = gq;
    pi.grand_val = gv;
    // Update the amount cell for this row
    const amtEl = document.getElementById('sbi_' + piKey.replace(/\W/g,'_') + '_' + poIdx + '_' + itemIdx + '_amt');
    if (amtEl) amtEl.value = item.total ? item.total.toFixed(2) : '';
    // Update header badge
    const hdrEl = document.getElementById('sbi_hdr_' + piKey.replace(/\W/g,'_'));
    if (hdrEl) hdrEl.textContent = gq.toLocaleString() + ' pcs · $' + gv.toFixed(2);
    // Refresh global totals bar
    let totalQty = 0, totalVal = 0;
    _summarySelectedPis.forEach(p => { totalQty += parseFloat(p.grand_qty||0)||0; totalVal += parseFloat(p.grand_val||0)||0; });
    _summaryUpdateTopBar(_summarySelectedPis.length, totalQty, totalVal);
}

function _summaryAddItemRow(piKey, poIdx) {
    const pi = _summarySelectedPis.find(p => _summaryPiKey(p) === piKey);
    if (!pi) return;
    if (!pi.pos[poIdx]) return;
    pi.pos[poIdx].items = pi.pos[poIdx].items || [];
    pi.pos[poIdx].items.push({ desc: '', ply: '', qty: 0, price: 0, total: 0 });
    renderSummaryBasket();
}

function _summaryDelItemRow(piKey, poIdx, itemIdx) {
    const pi = _summarySelectedPis.find(p => _summaryPiKey(p) === piKey);
    if (!pi) return;
    (pi.pos[poIdx]?.items || []).splice(itemIdx, 1);
    // Recompute PI grand totals
    let gq = 0, gv = 0;
    (pi.pos || []).forEach(po => (po.items || []).forEach(it => {
        gq += parseFloat(it.qty || 0) || 0;
        gv += parseFloat(it.total || 0) || 0;
    }));
    pi.grand_qty = gq;
    pi.grand_val = gv;
    renderSummaryBasket();
}

function renderSummaryBasket() {
    const box = document.getElementById('summaryBasket');
    if (!box) return;
    if (!_summarySelectedPis.length) {
        box.innerHTML = '<div style="padding:16px;border:1px dashed #cbd5e1;border-radius:10px;color:#64748b;background:#f8fafc;">No PIs added yet. Search an existing PI number or ERP sales order above.</div>';
        _summaryUpdateTopBar(0, 0, 0);
        return;
    }
    let totalQty = 0, totalVal = 0;
    const rows = _summarySelectedPis.map(pi => {
        const qty = parseFloat(pi.grand_qty || 0) || 0;
        const val = parseFloat(pi.grand_val || 0) || 0;
        totalQty += qty; totalVal += val;
        const piKey = _summaryPiKey(pi);
        const piKeyId = piKey.replace(/\W/g,'_');
        const po0 = (pi.pos || [])[0] || {};
        const ref = po0.poNum || po0.customerPo || po0.salesOrder || '';

        // Build editable item tables for each PO group
        let itemTablesHtml = '';
        (pi.pos || []).forEach((po, poIdx) => {
            const poRef = [po.poNum || po.customerPo || '', po.salesOrder || po.orderRef || '', po.style || ''].filter(Boolean).join(' · ');
            let itemRows = '';
            (po.items || []).forEach((item, itemIdx) => {
                const idPfx = `sbi_${piKeyId}_${poIdx}_${itemIdx}`;
                const escKey = piKey.replace(/'/g,"\\'");
                const tot = parseFloat(item.total || 0) || 0;
                itemRows += `<tr>
                    <td style="text-align:center;font-size:11px;color:#94a3b8;">${itemIdx+1}</td>
                    <td><input value="${escHtml(item.desc || item.itemName || '')}"
                        style="width:100%;padding:4px 6px;border:1.5px solid #e2e8f0;border-radius:5px;font-size:12px;"
                        oninput="_summaryItemChanged('${escKey}',${poIdx},${itemIdx},'desc',this.value)"></td>
                    <td><input value="${escHtml(item.ply || '')}"
                        style="width:60px;padding:4px 6px;border:1.5px solid #e2e8f0;border-radius:5px;font-size:12px;"
                        oninput="_summaryItemChanged('${escKey}',${poIdx},${itemIdx},'ply',this.value)"></td>
                    <td><input type="number" min="0" value="${parseFloat(item.qty||0)||''}"
                        style="width:80px;padding:4px 6px;border:1.5px solid #e2e8f0;border-radius:5px;font-size:12px;"
                        oninput="_summaryItemChanged('${escKey}',${poIdx},${itemIdx},'qty',this.value)"></td>
                    <td><input type="number" min="0" step="0.0001" value="${parseFloat(item.price||item.unitPrice||0)||''}"
                        style="width:80px;padding:4px 6px;border:1.5px solid #e2e8f0;border-radius:5px;font-size:12px;"
                        oninput="_summaryItemChanged('${escKey}',${poIdx},${itemIdx},'price',this.value)"></td>
                    <td><input id="${idPfx}_amt" readonly value="${tot ? tot.toFixed(2) : ''}"
                        style="width:80px;padding:4px 6px;border:1.5px solid #e2e8f0;border-radius:5px;font-size:12px;background:#f8fafc;font-weight:700;color:#4f46e5;"></td>
                    <td><button type="button" class="si-del-btn" title="Remove row"
                        onclick="_summaryDelItemRow('${escKey}',${poIdx},${itemIdx})">X</button></td>
                </tr>`;
            });
            itemTablesHtml += `<div style="margin:6px 0 10px;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;">
                ${poRef ? `<div style="padding:3px 10px;font-size:10.5px;color:#64748b;background:#f8fafc;border-bottom:1px solid #e2e8f0;">${escHtml(poRef)}</div>` : ''}
                <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:12px;">
                    <thead><tr style="background:#f1f5f9;">
                        <th style="padding:4px 6px;font-weight:700;color:#64748b;font-size:11px;white-space:nowrap;border-bottom:1px solid #e2e8f0;">SL</th>
                        <th style="padding:4px 6px;font-weight:700;color:#64748b;font-size:11px;text-align:left;border-bottom:1px solid #e2e8f0;">Description</th>
                        <th style="padding:4px 6px;font-weight:700;color:#64748b;font-size:11px;border-bottom:1px solid #e2e8f0;">Ply</th>
                        <th style="padding:4px 6px;font-weight:700;color:#64748b;font-size:11px;border-bottom:1px solid #e2e8f0;">Qty</th>
                        <th style="padding:4px 6px;font-weight:700;color:#64748b;font-size:11px;border-bottom:1px solid #e2e8f0;">Price $</th>
                        <th style="padding:4px 6px;font-weight:700;color:#64748b;font-size:11px;border-bottom:1px solid #e2e8f0;">Amount $</th>
                        <th style="border-bottom:1px solid #e2e8f0;"></th>
                    </tr></thead>
                    <tbody>${itemRows}</tbody>
                </table>
                </div>
                <div style="padding:4px 8px;">
                    <button type="button" class="ghost-btn" style="font-size:11px;padding:2px 10px;"
                        onclick="_summaryAddItemRow('${piKey.replace(/'/g,"\\'")}',${poIdx})">+ Add Row</button>
                </div>
            </div>`;
        });

        return `<div style="border:1.5px solid #e0e3ff;border-radius:10px;margin-bottom:10px;overflow:hidden;">
            <div class="opo-row opo-standalone" style="cursor:default;border-radius:0;">
                <span class="opo-badge b-standalone">PI</span>
                <div class="opo-num">${escHtml(pi.pi_number || '-')}</div>
                <div class="opo-meta">${escHtml(pi.customer || '-')}${ref ? ' · ' + escHtml(ref) : ''}
                    <span id="sbi_hdr_${piKeyId}" style="margin-left:6px;color:#4f46e5;font-weight:600;">${qty.toLocaleString()} pcs · $${val.toFixed(2)}</span>
                </div>
                <button class="ghost-btn" style="font-size:11px;padding:2px 10px;color:#f87171;border-color:#fca5a5;margin-left:auto;"
                        onclick="removeSummaryPi('${escHtml(piKey)}')">Remove</button>
            </div>
            <div style="padding:8px 12px 4px;">${itemTablesHtml}</div>
        </div>`;
    }).join('');
    box.innerHTML =
        rows +
        `<div style="display:flex;justify-content:flex-end;gap:18px;padding:12px 6px 0;font-size:13px;font-weight:700;color:#312e81;">
            <span>${_summarySelectedPis.length} PI(s)</span>
            <span>Qty: ${totalQty.toLocaleString()}</span>
            <span>Total: $${totalVal.toFixed(2)}</span>
        </div>`;
    _summaryUpdateTopBar(_summarySelectedPis.length, totalQty, totalVal);
}

function renderOrderPiOverview(orderId) {
    const overview = document.getElementById('orderPiOverview');
    const list     = document.getElementById('opoPiList');
    if (!orderId) { overview.style.display = 'none'; return; }

    fetch(APP_BASE + '/api/pis.php?order_id=' + encodeURIComponent(orderId))
        .then(r => r.json())
        .then(pis => {
            pis = pis || [];
            // For a cross-order Summary PI, this order has no pis rows of its own.
            // Still show the overview if the snapshot has summarySelectedPis.
            const snapshotSummary = (window._salesSnapshot && window._salesSnapshot.piType === 'summary'
                && Array.isArray(window._salesSnapshot.summarySelectedPis) && window._salesSnapshot.summarySelectedPis.length)
                ? window._salesSnapshot.summarySelectedPis : null;
            if (!pis.length && !snapshotSummary) { overview.style.display = 'none'; return; }
            updatePrintLock(_currentPiStep, true);

            const masters     = pis.filter(p => p.is_master);
            const individuals = pis.filter(p => !p.is_master);
            const includedNums = new Set(masters.flatMap(m => m.included_pis || []));

            // When a Summary is saved, its (cross-order) PIs become the selectable
            // set for building a Master PI; otherwise use this order's own PIs.
            const savedSummary = snapshotSummary;
            const selectablePis = (savedSummary && savedSummary.length) ? savedSummary : individuals;

            // Store the selectable PIs for item-level Master PI selection
            _savedPisOverviewCache = selectablePis;

            document.getElementById('opoPiCount').textContent = selectablePis.length + ' PI(s)';
            overview.style.display = 'block';

            let html = '';

            // Saved Summary header row — the PIs it combines are listed (expandable)
            // just below so items can be picked from all of them for a Master PI.
            if (savedSummary) {
                let sQty = 0, sVal = 0;
                savedSummary.forEach(p => { sQty += parseFloat(p.grand_qty || 0) || 0; sVal += parseFloat(p.grand_val || 0) || 0; });
                const nums = savedSummary.map(p => escHtml(p.pi_number || '')).filter(Boolean).join(', ') || '-';
                html += `<div class="opo-row opo-master" style="background:#eef2ff;border:1.5px solid #c7d2fe;">
                    <span class="opo-badge b-master" style="background:#4f46e5;">SUMMARY</span>
                    <div class="opo-num">${escHtml(orderId)}-SUMM</div>
                    <div class="opo-meta">${savedSummary.length} PI(s) · Qty ${sQty.toLocaleString()} · expand the PIs below to pick items for a Master PI
                        <div class="opo-includes">Combines: ${nums}</div>
                    </div>
                    <div class="opo-val">$${sVal.toFixed(2)}</div>
                    <?php if (in_array($__user['role'] ?? '', ['commercial','commercial_dept','admin'])): ?>
                    <button class="ghost-btn" style="font-size:11px;padding:2px 10px;color:#f87171;border-color:#fca5a5;"
                            onclick="deleteSummaryPi('${escHtml(orderId)}')">Delete</button>
                    <?php endif; ?>
                </div>`;
            }

            masters.forEach(pi => {
                const combined = (pi.included_pis || []).join(', ') || '-';
                html += `<div class="opo-row opo-master">
                    <span class="opo-badge b-master">MASTER</span>
                    <div class="opo-num">${pi.pi_number}</div>
                    <div class="opo-meta">
                        ${pi.customer || '-'} | ${(pi.pos||[]).length} PO(s)
                        <div class="opo-includes">Combines: ${combined}</div>
                    </div>
                    <div class="opo-val">$${parseFloat(pi.grand_val||0).toFixed(2)}</div>
                    <button class="ghost-btn" style="font-size:11px;padding:2px 10px;" onclick="printMasterPi()">Print</button>
                    <?php if (in_array($__user['role'] ?? '', ['commercial','commercial_dept','admin'])): ?>
                    <button class="ghost-btn" style="font-size:11px;padding:2px 10px;color:#f87171;border-color:#fca5a5;"
                            onclick="deleteMasterPi(${pi.id},'${escHtml(orderId)}')">Delete</button>
                    <?php endif; ?>
                </div>`;
            });

            selectablePis.forEach((pi, piIdx) => {
                const inMaster = includedNums.has(pi.pi_number);
                const badge    = inMaster ? '<span class="opo-badge b-included">IN MASTER</span>' : '';

                // Build inline item rows for this PI
                let itemsHtml = '<div class="opo-item-hdr"><span></span><span>Description of Goods</span><span>Ply</span><span>Qty</span><span>Unit Price</span><span>Amount</span></div>';
                (pi.pos || []).forEach((po, poIdx) => {
                    const orderRef = po.orderRef || po.salesOrder || po.salesOrderNo || '';
                    const poNum    = po.poNum    || po.customerPo || '';
                    const style    = po.style    || '';
                    const refParts = [
                        poNum    ? 'PO # ' + poNum : '',
                        orderRef ? 'ORDER REF: ' + orderRef : '',
                        style    ? 'Style# ' + style : ''
                    ].filter(Boolean);
                    if (refParts.length) {
                        itemsHtml += `<div class="opo-ref-line">${refParts.join(' | ')}</div>`;
                    }
                    (po.items || []).forEach((item, itemIdx) => {
                        const qty = parseFloat(item.qty   || 0);
                        const prc = parseFloat(item.price || item.unitPrice || 0);
                        const tot = parseFloat(item.total || (qty * prc)) || 0;
                        itemsHtml += `<div class="opo-item-row">
                            <input type="checkbox" class="mpi-item-chk"
                                   data-mpi-source="overview"
                                    data-pi="${piIdx}" data-po="${poIdx}" data-item="${itemIdx}"
                                    onchange="mpiUpdateTotals()">
                            <span>${item.desc || '-'}</span>
                            <span>${item.ply || ''}</span>
                            <span>${qty.toLocaleString()}</span>
                            <span>${prc ? '$' + prc.toFixed(2) : '-'}</span>
                            <span style="font-weight:700;">${tot ? '$' + tot.toFixed(2) : '-'}</span>
                        </div>`;
                    });
                });

                html += `
                <div style="border-radius:10px;overflow:hidden;border:1.5px solid ${inMaster ? '#bbf7d0' : '#fde68a'};">
                    <div class="opo-row ${inMaster ? 'opo-included' : 'opo-standalone'}"
                         style="border-radius:0;cursor:pointer;" onclick="mpiExpandOpoRow(${piIdx})">
                        ${badge}
                        <div class="opo-num">${pi.pi_number}</div>
                        <div class="opo-meta">${pi.customer || '-'} | ${(pi.pos||[]).length} PO(s)</div>
                        <div class="opo-val">$${parseFloat(pi.grand_val||0).toFixed(2)}</div>
                        <span id="opoChev_${piIdx}" style="font-size:11px;color:#6366f1;white-space:nowrap;">Select Items</span>
                        <button class="ghost-btn" style="font-size:11px;padding:2px 10px;" onclick="event.stopPropagation();loadPiIntoBlock('${pi.pi_number}')">Load</button>
                    </div>
                    <div id="opoItems_${piIdx}" style="display:none;">${itemsHtml}</div>
                </div>`;
            });

            list.innerHTML = html;
            document.getElementById('mpiBasket').style.display = 'none';
        })
        .catch(() => { overview.style.display = 'none'; });
}

function mpiExpandOpoRow(piIdx) {
    const panel = document.getElementById('opoItems_' + piIdx);
    const chev  = document.getElementById('opoChev_'  + piIdx);
    if (!panel) return;
    const open = panel.style.display !== 'none';
    panel.style.display = open ? 'none' : 'block';
    chev.textContent = open ? 'Select Items' : 'Hide Items';
}

function openMasterPi() {
    const piType = document.querySelector('input[name="piTypeChoice"]:checked')?.value || 'single';
    if (piType === 'master') {
        const selection = getSelectedMasterGroups();
        if (selection.length) {
            generateMasterPi();
            return;
        }
    }

    // If nothing is selected yet, guide the user to the selector.
    const overview = document.getElementById('orderPiOverview');
    if (overview) overview.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function loadPiIntoBlock(piNumber) {
    fetch(APP_BASE + '/api/pis.php?q=' + encodeURIComponent(piNumber))
        .then(r => r.json())
        .then(res => {
            if (res.match !== 'pi') { alert('PI not found: ' + piNumber); return; }
            const pi = res.pi;
            const po = (pi.pos || [])[0] || {};
            addPoBlock();
            const blocks = document.querySelectorAll('.po-block');
            const pid = blocks[blocks.length - 1].id.replace('block_', '');
            const piNumEl = document.getElementById('piNum_' + pid);
            if (piNumEl) { piNumEl.value = pi.pi_number; }
            fillPoBlock(pid, po, po);
        })
        .catch(() => alert('Could not load PI.'));
}

function closeMasterPi() {
    document.getElementById('masterPiModal').classList.remove('open');
    document.getElementById('mpiPreview').style.display = 'none';
}

function mpiToggleAllItems(chk) {
    document.querySelectorAll('.mpi-item-chk[data-mpi-source="modal"]').forEach(c => {
        c.checked = chk.checked;
    });
    mpiUpdateTotals();
}

function renderSavedPiList() {
    const container = document.getElementById('savedPiList');
    container.innerHTML = '<p style="color:#94a3b8;font-size:13px;padding:8px 0;">Loading...</p>';
    const orderId = sessionStorage.getItem('ats_current_order_id') || '';
    const url = orderId ? APP_BASE + '/api/pis.php?order_id=' + encodeURIComponent(orderId) : APP_BASE + '/api/pis.php';
    fetch(url)
        .then(r => r.json())
        .then(allPis => {
            const pis = (allPis || []).filter(p => !p.is_master);
            _savedPisModalCache = pis;
            if (!pis.length) {
                container.innerHTML = '<p style="color:#94a3b8;font-size:13px;padding:8px 0;">No saved PIs yet. Save a PI first.</p>';
                const selectAll = document.getElementById('mpiSelectAllItems');
                if (selectAll) selectAll.checked = false;
                return;
            }

            let html = '';
            pis.forEach((pi, piIdx) => {
                (pi.pos || []).forEach((po, poIdx) => {
                    (po.items || []).forEach((item, itemIdx) => {
                        const qty = parseFloat(item.qty || 0);
                        const prc = parseFloat(item.price || item.unitPrice || 0);
                        const tot = parseFloat(item.total || (qty * prc)) || 0;
                        html += `<div class="mpi-item-row">
                            <input type="checkbox" class="mpi-item-chk"
                                   data-mpi-source="modal"
                                   data-pi="${piIdx}" data-po="${poIdx}" data-item="${itemIdx}"
                                   onchange="mpiUpdateTotals()">
                            <span class="mpi-item-desc">${item.desc || '-'}</span>
                            <span class="mpi-item-ply">${item.ply || ''}</span>
                            <span class="mpi-item-qty">${qty.toLocaleString()}</span>
                            <span class="mpi-item-prc">${prc ? prc.toFixed(3) : '-'}</span>
                            <span class="mpi-item-tot">${tot ? '$' + tot.toFixed(2) : '-'}</span>
                        </div>`;
                    });
                });
            });

            container.innerHTML = html || '<p style="color:#94a3b8;font-size:13px;padding:8px 0;">No items found in the saved PIs.</p>';
            const selectAll = document.getElementById('mpiSelectAllItems');
            if (selectAll) selectAll.checked = false;
            mpiUpdateTotals();
        })
        .catch(() => { container.innerHTML = '<p style="color:#f87171;font-size:13px;">Could not load saved PIs.</p>'; });
}

function _getMpiCacheForCheckbox(chk) {
    return chk?.dataset?.mpiSource === 'overview' ? _savedPisOverviewCache : _savedPisModalCache;
}

function mpiUpdateTotals() {
    let qty = 0, val = 0, count = 0;
    document.querySelectorAll('.mpi-item-chk:checked').forEach(chk => {
        const cache = _getMpiCacheForCheckbox(chk);
        const pi    = cache[+chk.dataset.pi];
        const po    = (pi?.pos || [])[+chk.dataset.po];
        const item  = (po?.items || [])[+chk.dataset.item];
        if (!item) return;
        qty += parseFloat(item.qty || 0);
        val += parseFloat(item.total || ((item.qty||0) * (item.price||0)));
        count++;
    });
    const basket = document.getElementById('mpiBasket');
    if (basket) {
        basket.style.display = count ? 'flex' : 'none';
        document.getElementById('mpiBasketLabel').textContent = count + ' item' + (count !== 1 ? 's' : '') + ' selected';
        document.getElementById('mpiBasketQty').textContent   = qty.toLocaleString();
        document.getElementById('mpiBasketVal').textContent   = '$' + val.toFixed(2);
    }
    const bar = document.getElementById('mpiPreview');
    if (bar) {
        bar.style.display = count ? 'flex' : 'none';
        const ic = document.getElementById('mpiItemCount'); if (ic) ic.textContent = count + ' item' + (count !== 1 ? 's' : '') + ' selected';
        const tq = document.getElementById('mpiTotalQty');  if (tq) tq.textContent = qty.toLocaleString();
        const tv = document.getElementById('mpiTotalVal');  if (tv) tv.textContent = '$' + val.toFixed(2);
    }
    if (document.querySelector('input[name="piTypeChoice"]:checked')?.value === 'master') {
        renderMasterSelectedItems();
    }
}

function deleteSavedPi(id, piNum) {
    if (!confirm('Delete saved PI: ' + piNum + '?')) return;
    fetch(APP_BASE + '/api/pis.php?id=' + id, { method: 'DELETE' })
        .then(r => r.json())
        .then(() => { refreshSavedPiBadge(); renderSavedPiList(); updateMpiPreview(); })
        .catch(() => alert('Delete failed.'));
}

function generateMasterPi() {
    const groups = {};
    document.querySelectorAll('.mpi-item-chk:checked').forEach(chk => {
        const cache = _getMpiCacheForCheckbox(chk);
        const pi    = cache[+chk.dataset.pi];
        const po    = (pi?.pos || [])[+chk.dataset.po];
        const item  = (po?.items || [])[+chk.dataset.item];
        if (!pi || !po || !item) return;
        const key = chk.dataset.pi + '_' + chk.dataset.po;
        if (!groups[key]) {
            groups[key] = {
                piNumber:           pi.pi_number,
                orderRef:           po.orderRef   || po.salesOrder || po.salesOrderNo || '',
                poNum:              po.poNum      || po.customerPo || '',
                style:              po.style      || '',
                sharedBuyer:        po.sharedBuyer || '',
                sharedBuyerAddress: po.sharedBuyerAddress || '',
                items: []
            };
        }
        groups[key].items.push(item);
    });
    const selection = Object.values(groups);
    if (!selection.length) { alert('Please select at least one item.'); return; }
    sessionStorage.setItem('mpi_custom_items', JSON.stringify(selection));
    const days = document.getElementById('termLcDays')?.value || '90';
    const tol  = document.getElementById('termTolerance')?.value || '5';
    const hs   = document.getElementById('termHsCode')?.value || '';
    const docMust = document.getElementById('termDocMust')?.value || 'UD';
    const bnk  = document.getElementById('termBank')?.value || 'ncc';
    window.location.href = APP_BASE + '/pages/master-pi.php?days=' + encodeURIComponent(days)
        + '&lctype=Sight&tol=' + encodeURIComponent(tol)
        + '&hs=' + encodeURIComponent(hs)
        + '&doc=' + encodeURIComponent(docMust)
        + '&bank=' + encodeURIComponent(bnk);
}
function _getMpiCacheForCheckbox(chk) {
    return chk?.dataset?.mpiSource === 'overview' ? _savedPisOverviewCache : _savedPisModalCache;
}

function mpiUpdateTotals() {
    let qty = 0, val = 0, count = 0;
    document.querySelectorAll('.mpi-item-chk:checked').forEach(chk => {
        const cache = _getMpiCacheForCheckbox(chk);
        const pi    = cache[+chk.dataset.pi];
        const po   = (pi?.pos || [])[+chk.dataset.po];
        const item = (po?.items || [])[+chk.dataset.item];
        if (!item) return;
        qty += parseFloat(item.qty || 0);
        val += parseFloat(item.total || ((item.qty||0) * (item.price||0)));
        count++;
    });
    // Drive the inline basket bar
    const basket = document.getElementById('mpiBasket');
    if (basket) {
        basket.style.display = count ? 'flex' : 'none';
        document.getElementById('mpiBasketLabel').textContent = count + ' item' + (count !== 1 ? 's' : '') + ' selected';
        document.getElementById('mpiBasketQty').textContent   = qty.toLocaleString();
        document.getElementById('mpiBasketVal').textContent   = '$' + val.toFixed(2);
    }
    // Also update modal preview if open
    const bar = document.getElementById('mpiPreview');
    if (bar) {
        bar.style.display = count ? 'flex' : 'none';
        const ic = document.getElementById('mpiItemCount'); if (ic) ic.textContent = count + ' item' + (count !== 1 ? 's' : '') + ' selected';
        const tq = document.getElementById('mpiTotalQty');  if (tq) tq.textContent = qty.toLocaleString();
        const tv = document.getElementById('mpiTotalVal');  if (tv) tv.textContent = '$' + val.toFixed(2);
    }
    if (document.querySelector('input[name="piTypeChoice"]:checked')?.value === 'master') {
        renderMasterSelectedItems();
    }
}

function deleteSavedPi(id, piNum) {
    if (!confirm('Delete saved PI: ' + piNum + '?')) return;
    fetch(APP_BASE + '/api/pis.php?id=' + id, { method: 'DELETE' })
        .then(r => r.json())
        .then(() => { refreshSavedPiBadge(); renderSavedPiList(); updateMpiPreview(); })
        .catch(() => alert('Delete failed.'));
}

async function generateMasterPi() {
    let data;
    try {
        data = collectPiData();
        if (!data.pos.length) { alert('Please select at least one item.'); return; }
        const saved = await createOrUpdateMasterPiRecord(data);
        sessionStorage.setItem('mpi_custom_items', JSON.stringify(data.masterPiSelection || []));
        document.getElementById('piStatus').textContent = 'Saved';
        refreshSavedPiBadge();
        renderOrderPiOverview(saved.orderId);
        const days = document.getElementById('termLcDays')?.value || '90';
        const tol  = document.getElementById('termTolerance')?.value || '5';
        const hs   = document.getElementById('termHsCode')?.value || '';
        const docMust = document.getElementById('termDocMust')?.value || 'UD';
        const bnk  = document.getElementById('termBank')?.value || 'ncc';
        window.location.href = APP_BASE + '/pages/master-pi.php?days=' + encodeURIComponent(days)
            + '&lctype=Sight&tol=' + encodeURIComponent(tol)
            + '&hs=' + encodeURIComponent(hs)
            + '&doc=' + encodeURIComponent(docMust)
            + '&bank=' + encodeURIComponent(bnk);
    } catch (e) {
        console.error('generateMasterPi error:', e);
        alert('Master PI create failed: ' + (e.message || 'Could not reach server.'));
    }
}

// â”€â”€ Show/hide PI content based on whether an order is active â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function setPiContentVisible(visible) {
    document.getElementById('noOrderPrompt').style.display = visible ? 'none'  : 'block';
    document.getElementById('piContent').style.display     = visible ? 'block' : 'none';
    if (!visible) loadNoOrderList();
}

function loadNoOrderList() {
    const rows = document.getElementById('noOrderRows');
    if (!rows) return;
    fetch(APP_BASE + '/api/orders.php')
        .then(r => r.json())
        .then(list => {
            const relevant = (list || []).filter(o => o.current_step === 'sales');
            if (!relevant.length) {
                rows.innerHTML = '<div style="color:#94a3b8;font-size:13px;padding:8px 0;">No PI orders found.</div>';
                return;
            }
            rows.innerHTML = relevant.slice(0, 15).map(o => {
                const date = o.updated_at ? new Date(o.updated_at).toLocaleDateString('en-GB') : '—';
                return `<div onclick="loadOrderFromDashboard('${o.order_id}','sales')"
                    style="display:flex;align-items:center;gap:10px;padding:9px 14px;border:1.5px solid #e0e3ff;
                           border-radius:10px;cursor:pointer;background:#fafbff;transition:.12s;"
                    onmouseover="this.style.background='#eef2ff'" onmouseout="this.style.background='#fafbff'">
                    <span style="font-size:13px;font-weight:800;color:#4f46e5;min-width:140px;">${o.order_id}</span>
                    <span style="font-size:12px;color:#1e1e2e;flex:1;">${o.customer_name || '—'}</span>
                    <span style="font-size:11px;color:#94a3b8;">${date}</span>
                </div>`;
            }).join('');
        })
        .catch(() => { rows.innerHTML = '<div style="color:#f87171;font-size:13px;">Could not load orders.</div>'; });
}

// â”€â”€ Customer dropdown â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
let _piCustomers = [];
function loadPiCustomers() {
    fetch(APP_BASE + '/api/customers.php')
        .then(r => r.json())
        .then(list => {
            _piCustomers = list.filter(c => {
                if (['sales_person','team_leader'].includes(c.stage)) return false;
                return true;
            });
            const pendingName = (window._pendingPiCustomer || '').trim();
            if (pendingName && !_piCustomers.some(c => (c.company_name || '').trim() === pendingName)) {
                const fallback = (list || []).find(c => (c.company_name || '').trim() === pendingName);
                if (fallback) {
                    _piCustomers.push(fallback);
                } else {
                    _piCustomers.push({
                        company_name: pendingName,
                        address_head_office: '',
                        factory_address: '',
                        extra_data: '{}',
                        stage: 'linked_from_pi'
                    });
                }
            }
            const inp = document.getElementById('piCustomer');
            if (!inp) return;
            if (window._pendingPiCustomer) {
                inp.value = window._pendingPiCustomer;
                window._pendingPiCustomer = '';
                onPiCustomerChange();
            }
        })
        .catch(() => {});
}
function escHtml(s) {
    return String(s || '').replace(/[&<>"]/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[m]));
}
function hidePiCustomerDropdown() {
    const dd = document.getElementById('piCustomerDropdown');
    if (dd) dd.style.display = 'none';
}
function onPiCustomerInput() {
    const inp = document.getElementById('piCustomer');
    const dd  = document.getElementById('piCustomerDropdown');
    if (!inp || !dd) return;
    const q = (inp.value || '').trim().toLowerCase();
    const matches = (q
        ? _piCustomers.filter(c => (c.company_name || '').toLowerCase().includes(q))
        : _piCustomers).slice(0, 100);
    if (!matches.length) {
        dd.innerHTML = '<div class="combo-empty">No match</div>';
    } else {
        dd.innerHTML = matches.map(c => {
            const nm = escHtml(c.company_name);
            return `<div class="combo-item" onmousedown="pickPiCustomer(this)" data-name="${nm}">${nm}</div>`;
        }).join('');
    }
    dd.style.display = 'block';
}
function pickPiCustomer(el) {
    document.getElementById('piCustomer').value = el.getAttribute('data-name');
    hidePiCustomerDropdown();
    onPiCustomerChange();
}
function onPiCustomerChange() {
    const name = (document.getElementById('piCustomer').value || '').trim();
    const c = _piCustomers.find(x => (x.company_name || '').trim() === name);
    const addr = c ? (c.factory_address || c.address_head_office || '') : '';
    if (addr) document.getElementById('piBuyerAddress').value = addr;
    buildSalesTerms();
}
// Fill the Customer (TO) field from an ERP/PO customer name, only if still blank
function setPiCustomerIfEmpty(name) {
    const val = (name || '').trim();
    if (!val) return;
    const inp = document.getElementById('piCustomer');
    if (!inp || inp.value.trim()) return;
    inp.value = val;
    onPiCustomerChange();
}
// Fall back to the ERP ship-to / bill-to address when the customer profile has
// none saved, so the Customer Address isn't left blank.
function setPiAddressFromErpIfEmpty(erpGroup) {
    const addrEl = document.getElementById('piBuyerAddress');
    if (!addrEl || addrEl.value.trim()) return; // keep an already-filled address
    const erpAddr = (erpGroup?.shipToAddress || erpGroup?.billToAddress || '').trim();
    if (erpAddr) addrEl.value = erpAddr;
}
loadPiCustomers();

// ── Marketing person (approver) dropdown ──────────────────────────────────
let _piMarketingUsers = [];
function loadMarketingUsers() {
    fetch(APP_BASE + '/api/users.php?role=marketing')
        .then(r => r.json())
        .then(list => {
            _piMarketingUsers = Array.isArray(list) ? list : [];
            const sel = document.getElementById('piMarketingUser');
            if (!sel) return;
            const cur = sel.value;
            sel.innerHTML = '<option value="">— Select marketing person —</option>' +
                _piMarketingUsers.map(u => {
                    const team = u.team ? ' (' + u.team + ')' : '';
                    return `<option value="${u.id}">${(u.name || '').replace(/</g,'&lt;')}${team}</option>`;
                }).join('');
            if (cur) sel.value = cur;
            // Apply an ERP name that arrived before the list finished loading.
            if (window._pendingMarketingUserName) {
                setPiMarketingUserByName(window._pendingMarketingUserName);
                window._pendingMarketingUserName = '';
            }
        })
        .catch(() => {});
}
function setPiMarketingUser(id) {
    const sel = document.getElementById('piMarketingUser');
    if (sel && id != null) sel.value = String(id);
}
// Match an ERP person NAME to a Marketing user in the dropdown; select it if
// found. Only fills when still empty so it never overrides a manual choice.
function setPiMarketingUserByName(name) {
    const val = (name || '').trim();
    if (!val) return;
    const sel = document.getElementById('piMarketingUser');
    if (!sel || sel.value) return; // keep a manual selection
    if (!_piMarketingUsers.length) { window._pendingMarketingUserName = val; return; }
    const norm = s => String(s || '').trim().toLowerCase();
    const match = _piMarketingUsers.find(u => norm(u.name) === norm(val))
        || _piMarketingUsers.find(u => norm(u.name).includes(norm(val)) || norm(val).includes(norm(u.name)));
    if (match) sel.value = String(match.id);
}
loadMarketingUsers();

// Init
addPoBlock();
document.getElementById('piDate').value = new Date().toISOString().split('T')[0];
refreshSavedPiBadge();

// Show prompt by default; footer.php will call onOrderLoad if a session order exists
setPiContentVisible(false);

document.getElementById('masterPiModal').addEventListener('click', function(e) {
    if (e.target === this) closeMasterPi();
});

// â”€â”€ Order ID integration â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
const _STEP_ORDER = ['marketing-intake','costing-review','production','sales','lc','exchange','commercial','packing','delivery','truck','origin','beneficiary','forwarding','bank-forwarding','po-status'];
function _stepIdx(s) { return _STEP_ORDER.indexOf(s); }

function updatePrintLock(step, forceUnlock = false) {
    const btn = document.getElementById('printPiBtn');
    const excelBtn = document.getElementById('excelPiBtn');
    if (!btn) return;
    _currentPiStep = step || 'sales';
    const waitingForMarketing = step === 'marketing';
    const approvedAtPi = step === 'sales' && hasMarketingApproval();
    const canPrint = hasMarketingApproval() && (approvedAtPi || _stepIdx(step) > _stepIdx('sales'));
    const canPreview = !canPrint && waitingForMarketing && forceUnlock;
    btn.disabled = !(canPrint || canPreview);
    btn.textContent = canPreview ? 'Preview Single PI' : 'Print Single PI';
    btn.title    = canPrint ? '' : (canPreview ? 'Preview only until Marketing approval' : 'Submit PI and get Marketing approval before printing');
    btn.style.opacity = (canPrint || canPreview) ? '' : '0.5';
    if (excelBtn) {
        excelBtn.disabled = !canPrint;
        excelBtn.title = canPrint ? '' : (waitingForMarketing ? 'Waiting for Marketing approval before Excel download' : 'Submit PI and get Marketing approval before Excel download');
        excelBtn.style.opacity = canPrint ? '' : '0.5';
    }
    const emailBtn = document.getElementById('emailPiBtn');
    if (emailBtn) {
        emailBtn.disabled = !canPrint;
        emailBtn.title = canPrint ? 'Open Outlook with this PI attached' : 'Submit PI and get Marketing approval before emailing';
        emailBtn.style.opacity = canPrint ? '' : '0.5';
    }
    onPiTypeChange();
}

window.onOrderLoad = async function(res) {
    setPiContentVisible(true);
    resetSubmitBtn();
    _summarySelectedPis = []; _masterEditGroups = []; // re-seeded from this order's PIs when Summary/Master mode opens
    const orderId = res.order?.order_id;
    const salesSnapshot = res.pages?.sales || null;
    window._salesSnapshot = salesSnapshot; // used by Save Summary to merge without clobbering
    const marketingSnapshot = res.pages?.marketing || null;
    const currentStep = res.order?.current_step || 'sales';
    _marketingApproved = marketingSnapshot?.marketingApproved === true
        || marketingSnapshot?.marketingApproved === 'true'
        || marketingSnapshot?.piApprovalStatus === 'approved';
    const hasSavedPiData = !!(
        salesSnapshot &&
        (
            (Array.isArray(salesSnapshot.pos) && salesSnapshot.pos.length) ||
            (Array.isArray(salesSnapshot.pis) && salesSnapshot.pis.length)
        )
    );
    _currentPiStep = currentStep;
    _editingApprovedPi = false; // fresh load is never mid-edit
    updatePrintLock(currentStep, hasSavedPiData);
    refreshEditPiBanner(); // ensure the Edit banner reflects approval on load
    const marketingIntake = res.pages?.['marketing-intake'] || null;
    const fallbackCustomer = salesSnapshot?.customer || marketingIntake?.customer || res.order?.customer_name || '';
    if (salesSnapshot?.piType) {
        const radio = document.querySelector(`input[name="piTypeChoice"][value="${salesSnapshot.piType}"]`);
        if (radio) radio.checked = true;
    }
    onPiTypeChange();
    renderOrderPiOverview(orderId);
    if (salesSnapshot?.customer) document.getElementById('piCustomer').value = salesSnapshot.customer;
    if (salesSnapshot?.buyer) document.getElementById('piBuyer').value = salesSnapshot.buyer;
    if (salesSnapshot?.piDate) document.getElementById('piDate').value = salesSnapshot.piDate;
    if (salesSnapshot?.buyerAddress) document.getElementById('piBuyerAddress').value = salesSnapshot.buyerAddress;
    if (salesSnapshot?.marketingUserId) setPiMarketingUser(salesSnapshot.marketingUserId);
    if (salesSnapshot?.piNum) {
        document.getElementById('piNumber').value = salesSnapshot.piNum;
        document.getElementById('piNumDisplay').textContent = salesSnapshot.piNum;
    }

    if (salesSnapshot?.piType === 'master') {
        document.getElementById('poBlocksContainer').innerHTML = '';
        poCount = 0;
        rowCounters = {};
        renderMasterSelectedItemsFromGroups(salesSnapshot.masterPiSelection || []);
        document.getElementById('piStatus').textContent = 'Loaded';
        return;
    }

    if (salesSnapshot?.piType === 'summary') {
        // Restore the saved Summary selection (already-created PIs) into the basket.
        if (Array.isArray(salesSnapshot.summarySelectedPis) && salesSnapshot.summarySelectedPis.length) {
            _summarySelectedPis = salesSnapshot.summarySelectedPis;
        }
        if (typeof enterSummaryMode === 'function') enterSummaryMode();
        document.getElementById('piStatus').textContent = 'Loaded';
        return;
    }

    if (salesSnapshot?.erpImported && Array.isArray(salesSnapshot.pos) && salesSnapshot.pos.length) {
        document.getElementById('poBlocksContainer').innerHTML = '';
        poCount = 0;
        rowCounters = {};
        salesSnapshot.pos.forEach(po => {
            addPoBlock();
            const blocks = document.querySelectorAll('.po-block');
            const pid = blocks[blocks.length - 1].id.replace('block_', '');
            fillPoBlock(pid, po, {
                salesOrder: po.salesOrder || '',
                reqDate: po.reqDate || '',
                status: po.status || 'Booked'
            });
        });
        document.getElementById('piStatus').textContent = 'ERP Imported';
        updateSummary();
        return;
    }

    if (res.pis && res.pis.length) {
        const firstPi = res.pis[0];
        // Fill shared header fields from first PI
        document.getElementById('piNumber').value           = firstPi.pi_number || '';
        document.getElementById('piNumDisplay').textContent = firstPi.pi_number || '-';
        document.getElementById('piCustomer').value         = firstPi.customer || fallbackCustomer || '';
        const firstPo = firstPi.pos?.[0] || {};
        document.getElementById('piBuyer').value            = salesSnapshot?.buyer          || firstPo.sharedBuyer          || '';
        document.getElementById('piDate').value             = firstPi.pi_date || '';
        document.getElementById('piBuyerAddress').value     = salesSnapshot?.buyerAddress   || firstPo.sharedBuyerAddress   || '';

        // Each PI becomes its own PO block
        document.getElementById('poBlocksContainer').innerHTML = '';
        poCount = 0; rowCounters = {};
        const pisToLoad = res.pis;
        pisToLoad.forEach(pi => {
            const po = (pi.pos || [])[0] || {};
            addPoBlock();
            const blocks = document.querySelectorAll('.po-block');
            const pid = blocks[blocks.length - 1].id.replace('block_', '');
            const piNumEl = document.getElementById('piNum_' + pid);
            if (piNumEl) piNumEl.value = pi.pi_number || '';
            fillPoBlock(pid, po, po);
        });
        document.getElementById('piStatus').textContent = 'Loaded';
        return;
    }

    if (await hydratePiFromMarketingIntake(marketingIntake, salesSnapshot)) {
        return;
    }

    if (fallbackCustomer) {
        document.getElementById('piCustomer').value = fallbackCustomer;
        window._pendingPiCustomer = fallbackCustomer;
        onPiCustomerChange();
    }

    maybeAutoSearchErpFromUrl();

    // New order: auto-fill is handled by addPoBlock â†’ autoFillPiNum

};

window.onNewOrder = function(orderId) {
    setPiContentVisible(true);
    _currentPiStep = 'sales';
    _marketingApproved = false;
    _summarySelectedPis = [];
    _masterEditGroups = [];
    resetPiFormFields();
    updatePrintLock('sales', false);
    if (orderId) {
        renderOrderPiOverview(orderId);
        document.getElementById('piNumber').value           = orderId + '-PI';
        document.getElementById('piNumDisplay').textContent = orderId + '-PI';
        // Pre-fill first block's PI number
        const firstPid = document.querySelectorAll('.po-block')[0]?.id.replace('block_','') || '';
        const el = document.getElementById('piNum_' + firstPid);
        if (el && !el.value) el.value = orderId + '-PI';
    } else {
        // Blank PI draft — the order is created when the first PI is saved.
        // Clear any leftover PIs list / saved badge from the previously loaded order.
        renderOrderPiOverview(null);
        refreshSavedPiBadge();
        const disp = document.getElementById('piNumDisplay');
        if (disp) disp.textContent = '-';
    }
    // Coming from a Commercial notification: sales.php?erp_order=NNN — auto-search
    // that ERP order into the first PO block. This used to live only in
    // onOrderLoad, which no longer runs on the clean-draft entry path.
    maybeAutoSearchErpFromUrl();
};

// Auto-search the ERP order passed as ?erp_order=… (from a notification click).
function maybeAutoSearchErpFromUrl() {
    const erpOrderFromUrl = new URLSearchParams(window.location.search).get('erp_order') || '';
    if (!erpOrderFromUrl || window._erpOrderAutoLoaded) return;
    window._erpOrderAutoLoaded = true;
    const firstPid = document.querySelector('.po-block')?.id.replace('block_', '') || '';
    const erpInput = document.getElementById('erpInput_' + firstPid);
    if (erpInput) {
        erpInput.value = erpOrderFromUrl;
        searchErp(firstPid, false);
    }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>

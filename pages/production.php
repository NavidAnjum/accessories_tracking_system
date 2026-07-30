<?php
$pageTitle   = 'Production';
$activePage  = 'production';
$navSection  = 'order';
$pageSubtitle = 'Production — confirm job is in production before raising PI.';
include __DIR__ . '/../includes/header.php';
?>

<style>
.prod-card {
    background: #fff;
    border-radius: 16px;
    border: 1.5px solid #e0e3ff;
    padding: 32px;
    max-width: 680px;
    margin: 0 auto 28px;
}

.prod-status-row {
    display: flex;
    gap: 14px;
    margin: 28px 0;
}

.prod-status-btn {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 28px 16px;
    border-radius: 14px;
    border: 2px solid #e0e3ff;
    background: #fafbff;
    cursor: pointer;
    transition: all .2s;
    font-family: inherit;
}
.prod-status-btn:hover { border-color: #6366f1; background: #f0f1ff; }

.prod-status-btn.active-not-started {
    border-color: #f59e0b;
    background: #fffbeb;
    box-shadow: 0 4px 20px rgba(245,158,11,.15);
}
.prod-status-btn.active-started {
    border-color: #10b981;
    background: #f0fdf4;
    box-shadow: 0 4px 20px rgba(16,185,129,.15);
}

.prod-status-icon {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.prod-status-btn.active-not-started .prod-status-icon { background: #fef3c7; }
.prod-status-btn.active-started      .prod-status-icon { background: #d1fae5; }
.prod-status-btn:not(.active-not-started):not(.active-started) .prod-status-icon { background: #eef2ff; }

.prod-status-label {
    font-size: 15px;
    font-weight: 700;
    color: #1e1e2e;
}
.prod-status-sub {
    font-size: 12px;
    color: #94a3b8;
    text-align: center;
    line-height: 1.5;
}

.prod-meta-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 18px;
}
.prod-meta-field label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #64748b;
    margin-bottom: 6px;
}
.prod-meta-field input,
.prod-meta-field textarea,
.prod-meta-field select {
    width: 100%;
    box-sizing: border-box;
    padding: 10px 13px;
    border: 1.5px solid #e2e8f0;
    border-radius: 9px;
    font-size: 13px;
    font-family: inherit;
    color: #1e1e2e;
    background: #fafbff;
    outline: none;
    transition: border-color .15s;
}
.prod-meta-field input:focus,
.prod-meta-field textarea:focus,
.prod-meta-field select:focus { border-color: #6366f1; }

.prod-timeline {
    background: #f8faff;
    border: 1.5px solid #e0e3ff;
    border-radius: 12px;
    padding: 16px 20px;
    margin-top: 20px;
}
.prod-timeline-item {
    display: flex;
    gap: 12px;
    align-items: flex-start;
    padding: 8px 0;
    border-bottom: 1px solid #eef0ff;
}
.prod-timeline-item:last-child { border-bottom: none; }
.prod-timeline-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #6366f1;
    flex-shrink: 0;
    margin-top: 3px;
}
.prod-timeline-dot.grey { background: #e2e8f0; }
.prod-timeline-text { font-size: 12px; color: #64748b; line-height: 1.5; }
.prod-timeline-text strong { color: #1e1e2e; display: block; }

.prod-info-row {
    display: flex;
    gap: 12px;
    align-items: center;
    padding: 14px 18px;
    background: #f1f5ff;
    border-radius: 10px;
    margin-bottom: 24px;
}
.prod-info-row svg { flex-shrink: 0; color: #6366f1; }
.prod-info-row span { font-size: 13px; color: #475569; line-height: 1.5; }
</style>

<section class="form-card" data-page="production">
    <div class="section-head">
        <div class="section-title">
            <span class="section-tag">Step 3</span>
            <h2>Production Entry</h2>
        </div>
        <div class="section-summary">
            <strong>Production Team</strong>
            <span>Confirm whether this order has gone into production before the PI is raised.</span>
        </div>
    </div>

    <div class="prod-card">

        <div class="prod-info-row">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span>Mark this order as <strong>Started</strong> once production has begun. The PI step becomes available after confirmation.</span>
        </div>

        <!-- Status toggle -->
        <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748b;margin-bottom:10px;">Production Status</div>
        <div class="prod-status-row">
            <button type="button" class="prod-status-btn" id="btnNotStarted" onclick="setStatus('not_started')">
                <div class="prod-status-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                </div>
                <div class="prod-status-label">Not Started</div>
                <div class="prod-status-sub">Production has not<br>yet begun for this order</div>
            </button>
            <button type="button" class="prod-status-btn" id="btnStarted" onclick="setStatus('started')">
                <div class="prod-status-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <div class="prod-status-label">Started</div>
                <div class="prod-status-sub">Production is underway —<br>order can proceed to PI</div>
            </button>
        </div>

        <input type="hidden" id="productionStatus" name="productionStatus" value="not_started">

        <!-- Meta fields -->
        <div class="prod-meta-grid">
            <div class="prod-meta-field">
                <label>Start Date</label>
                <input type="date" id="productionStartDate" name="productionStartDate">
            </div>
            <div class="prod-meta-field">
                <label>Expected Completion</label>
                <input type="date" id="productionExpectedDate" name="productionExpectedDate">
            </div>
            <div class="prod-meta-field" style="grid-column:1/-1;">
                <label>Production Notes</label>
                <textarea id="productionNotes" name="productionNotes" rows="3" placeholder="Any notes about this production run…"></textarea>
            </div>
        </div>

        <!-- Timeline log -->
        <div class="prod-timeline" id="productionTimeline" style="display:none;">
            <div style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#6366f1;margin-bottom:10px;">Activity Log</div>
            <div id="productionTimelineItems"></div>
        </div>
    </div>

    <div class="page-actions">
        <div class="page-actions-left">
            <button type="button" class="ghost-btn js-prev-page" data-prev-page="costing-review">← Previous</button>
        </div>
        <div class="page-actions-right">
            <button type="button" class="primary-btn js-next-page" data-next-page="sales" id="proceedToPI">
                Next: PI →
            </button>
        </div>
    </div>
</section>

<script>
// ── Status toggle ──────────────────────────────────────────────────────────
function setStatus(status) {
    document.getElementById('productionStatus').value = status;
    const btnNS = document.getElementById('btnNotStarted');
    const btnS  = document.getElementById('btnStarted');
    btnNS.className = 'prod-status-btn' + (status === 'not_started' ? ' active-not-started' : '');
    btnS.className  = 'prod-status-btn' + (status === 'started'     ? ' active-started'     : '');

    // Auto-fill start date when marking started
    if (status === 'started') {
        const sd = document.getElementById('productionStartDate');
        if (!sd.value) sd.value = new Date().toISOString().slice(0, 10);
    }
    refreshTimeline();
}

function refreshTimeline() {
    const status = document.getElementById('productionStatus').value;
    const sd     = document.getElementById('productionStartDate').value;
    const ed     = document.getElementById('productionExpectedDate').value;
    const tl     = document.getElementById('productionTimeline');
    const items  = document.getElementById('productionTimelineItems');
    const order  = window.getCurrentOrderId ? window.getCurrentOrderId() : '—';

    const rows = [];
    if (status === 'started' && sd) {
        rows.push({ dot: true,  label: 'Production Started', detail: 'Start date: ' + sd });
    } else {
        rows.push({ dot: false, label: 'Awaiting Start',     detail: 'Not yet begun' });
    }
    if (ed) rows.push({ dot: !!sd, label: 'Expected Completion', detail: ed });

    if (rows.length) {
        tl.style.display = 'block';
        items.innerHTML = rows.map(r =>
            `<div class="prod-timeline-item">
                <div class="prod-timeline-dot${r.dot ? '' : ' grey'}"></div>
                <div class="prod-timeline-text"><strong>${r.label}</strong>${r.detail}</div>
            </div>`
        ).join('');
    } else {
        tl.style.display = 'none';
    }
}

// ── On order load ──────────────────────────────────────────────────────────
window.onOrderLoad = function(res) {
    const prod = res.pages?.production || {};
    if (prod.productionStatus) setStatus(prod.productionStatus);
    else setStatus('not_started');
    refreshTimeline();
};

document.getElementById('productionStartDate').addEventListener('change', refreshTimeline);
document.getElementById('productionExpectedDate').addEventListener('change', refreshTimeline);

// Init default state
setStatus('not_started');
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>

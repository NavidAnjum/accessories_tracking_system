const APP_BASE = window.APP_BASE || ('/' + window.location.pathname.split('/')[1]);

const bindings = [
    "salesOrder",
    "buyerName",
    "customerPo",
    "customerName",
    "approvalStatus",
    "factoryStatus",
];

for (const id of bindings) {
    const input = document.getElementById(id);
    const target = document.querySelector(`[data-bind="${id}"]`);

    if (!input || !target) continue;

    const sync = () => {
        target.textContent = input.value || "-";
    };

    input.addEventListener("input", sync);
    input.addEventListener("change", sync);
    sync();
}

const tabs = document.querySelectorAll(".page-tab");
const screens = document.querySelectorAll(".page-screen");
const sectionBtns = document.querySelectorAll(".nav-section-btn");
const tabGroups = document.querySelectorAll(".nav-tab-group");
const feedbackBox = document.getElementById("actionFeedback");
const globalStatus = document.getElementById("globalStatus");

const pageStageMap = {
    "marketing-intake": "Marketing Intake",
    "costing-review": "Costing Review",
    "customer-profile": "Customer Profile",
    "buyer-master": "Buyer Master",
    "item-master": "Item Master",
    sales: "PO Search",
    marketing: "Marketing Approval",
    commercial: "PI Entry",
    "po-overview": "PO Status",
    "po-status": "Challan Sheet",
    packing: "Commercial Review",
    delivery: "Commercial Review",
    truck: "Commercial Review",
    origin: "Commercial Review",
    beneficiary: "Commercial Review",
    forwarding: "Forwarding",
    lc: "LC",
    exchange: "Bill of Exchange",
    approval: "Factory Release",
    review: "Factory Release",
};

// ── Order tracking & localStorage persistence ───────────────────────────────
const ED_INTAKE_KEY = 'ed_intake_state';
const ED_ORDERS_KEY = 'ed_orders';

const STEP_LABELS = {
    'dashboard':        'Dashboard',
    'marketing-intake': 'Marketing Intake',
    'costing-review':   'Costing Review',
    'sales':            'PI',
    'marketing':        'Marketing',
    'lc':               'LC',
    'po-overview':      'PO Status',
    'exchange':         'Bill of Exchange',
    'commercial':       'Commercial Invoice',
    'packing':          'Packing List',
    'delivery':         'Delivery Challan',
    'truck':            'Truck Challan',
    'origin':           'Certificate of Origin',
    'beneficiary':      "Beneficiary's Certificate",
    'forwarding':       'Forwarding',
    'po-status':        'Challan Sheet',
};

const STEP_ORDER = [
    'marketing-intake', 'costing-review', 'sales', 'marketing',
    'commercial', 'packing', 'delivery', 'truck', 'origin',
    'beneficiary', 'lc', 'forwarding',
];

function getOrdersList() {
    try { return JSON.parse(localStorage.getItem(ED_ORDERS_KEY) || '[]'); }
    catch { return []; }
}
function saveOrdersList(orders) { localStorage.setItem(ED_ORDERS_KEY, JSON.stringify(orders)); }

function generateOrderId() {
    // Sync fallback using localStorage only — used by collectIntakeState
    const orders = getOrdersList();
    const maxSeq = orders.reduce((m, o) => {
        const match = (o.id || '').match(/ZNZ(\d+)/);
        return match ? Math.max(m, parseInt(match[1])) : m;
    }, 0);
    return `ZNZ${String(maxSeq + 1).padStart(6, '0')}`;
}

async function generateOrderIdFromDB() {
    // Async version that checks the DB for the true max sequence
    try {
        const res = await fetch(APP_BASE + '/api/orders.php');
        if (res.ok) {
            const dbOrders = await res.json();
            const dbMax = (dbOrders || []).reduce((m, o) => {
                const match = (o.order_id || '').match(/ZNZ(\d+)/);
                return match ? Math.max(m, parseInt(match[1])) : m;
            }, 0);
            const lsOrders = getOrdersList();
            const lsMax = lsOrders.reduce((m, o) => {
                const match = (o.id || '').match(/ZNZ(\d+)/);
                return match ? Math.max(m, parseInt(match[1])) : m;
            }, 0);
            const maxSeq = Math.max(dbMax, lsMax);
            return `ZNZ${String(maxSeq + 1).padStart(6, '0')}`;
        }
    } catch (_) {
        // fall through
    }
    return generateOrderId();
}

function loadIntakeState() {
    try { return JSON.parse(localStorage.getItem(ED_INTAKE_KEY) || 'null'); }
    catch { return null; }
}

function collectIntakeState() {
    const saved = loadIntakeState();
    return {
        id:            saved?.id || generateOrderId(),
        customer:      document.getElementById('intakeCustomer')?.value      || saved?.customer      || '',
        salesperson:   document.getElementById('intakeSalesperson')?.value   || saved?.salesperson   || '',
        date:          document.getElementById('intakeDate')?.value          || saved?.date          || '',
        poNumber:      document.getElementById('intakePoNumber')?.value      || saved?.poNumber      || '',
        trimsNo:       document.getElementById('intakeTrimsNo')?.value       || saved?.trimsNo       || '',
        buyerCode:     document.getElementById('intakeBuyerSelect')?.value   || saved?.buyerCode     || '',
        withoutArl:    document.getElementById('intakeWithoutArl')?.checked  ?? saved?.withoutArl    ?? false,
        subject:       document.getElementById('intakeSubject')?.value       || saved?.subject       || '',
        paperQuality:  document.getElementById('intakePaperQuality')?.value  || saved?.paperQuality  || '',
        buyerEndName:  document.getElementById('intakeBuyerName')?.value     || saved?.buyerEndName  || '',
        design:        document.getElementById('intakeDesign')?.value        || saved?.design        || '',
        orderNo:       document.getElementById('intakeOrderNo')?.value       || saved?.orderNo       || '',
        type:          document.getElementById('intakeType')?.value          || saved?.type          || '',
        deliveryDate:  document.getElementById('intakeDeliveryDate')?.value  || saved?.deliveryDate  || '',
        notes:         document.getElementById('intakeMarketingNotes')?.value|| saved?.notes         || '',
        rows:          intakeRows.length ? JSON.parse(JSON.stringify(intakeRows)) : (saved?.rows || []),
        currentStep:   saved?.currentStep || 'marketing-intake',
        savedAt:       new Date().toISOString(),
    };
}

async function saveIntakeState(step) {
    // Legacy SPA function — localStorage only, no DB writes (DB is managed by save_page.php now)
    const state = collectIntakeState();
    if (step) state.currentStep = step;
    localStorage.setItem(ED_INTAKE_KEY, JSON.stringify(state));

    // Upsert into orders list (localStorage only)
    const orders = getOrdersList();
    const idx = orders.findIndex(o => o.id === state.id);
    if (idx >= 0) orders[idx] = state; else orders.unshift(state);
    saveOrdersList(orders);

    // DB write removed — orders/step are managed by save_page.php and the new PHP pages now
    renderDashboard();
}

function restoreIntakeFromState(state) {
    if (!state) return;
    const sv = (id, val) => { const el = document.getElementById(id); if (el) el.value = val || ''; };
    sv('intakeCustomer',      state.customer);
    if (state.customer) { renderBuyerSelect(state.customer); autoFillSalesperson(state.customer); }
    sv('intakeSalesperson',   state.salesperson);
    sv('intakeDate',          state.date);
    sv('intakePoNumber',      state.poNumber);
    sv('intakeTrimsNo',       state.trimsNo);
    if (intakeBuyerSelect && state.buyerCode) intakeBuyerSelect.value = state.buyerCode;
    const arl = document.getElementById('intakeWithoutArl');
    if (arl) arl.checked = state.withoutArl || false;
    sv('intakeSubject',       state.subject);
    sv('intakePaperQuality',  state.paperQuality);
    sv('intakeBuyerName',     state.buyerEndName);
    sv('intakeDesign',        state.design);
    sv('intakeOrderNo',       state.orderNo);
    sv('intakeType',          state.type);
    sv('intakeDeliveryDate',  state.deliveryDate);
    sv('intakeMarketingNotes',state.notes);
    if (state.rows?.length) {
        intakeRows.length = 0;
        state.rows.forEach(r => intakeRows.push(createIntakeRow(r)));
        renderIntakeRows();
    }
}

async function renderDashboard() {
    const body = document.getElementById('dashOrdersBody');
    if (!body) return;

    let orders = [];

    // Try to load from DB; merge with localStorage
    try {
        const res = await fetch(APP_BASE + '/api/orders.php');
        if (res.ok) {
            const dbOrders = await res.json();
            // Normalise DB row shape to match localStorage shape
            const dbNorm = (dbOrders || []).map(o => ({
                id:           o.order_id,
                customer:     o.customer_name,
                poNumber:     o.po_number,
                salesperson:  o.salesperson,
                buyerCode:    o.to_buyer,
                deliveryDate: o.delivery_date,
                currentStep:  o.current_step,
                savedAt:      o.updated_at,
                rows:         [],   // items not loaded in list view
            }));

            // Merge: DB is source of truth; supplement with any localStorage-only entries
            const lsOrders = getOrdersList();
            const dbIds = new Set(dbNorm.map(o => o.id));
            const lsOnly = lsOrders.filter(o => !dbIds.has(o.id));
            orders = [...dbNorm, ...lsOnly];

            // Refresh localStorage with merged list
            saveOrdersList(orders);
        } else {
            orders = getOrdersList();
        }
    } catch (_) {
        // Fetch failed — fall back to localStorage
        orders = getOrdersList();
    }

    if (!orders.length) {
        body.innerHTML = `<tr><td colspan="10" class="dash-empty">No orders yet — start from Marketing Intake.</td></tr>`;
        return;
    }

    body.innerHTML = orders.map(o => {
        const stepLabel  = STEP_LABELS[o.currentStep] || 'Marketing Intake';
        const stepIdx    = STEP_ORDER.indexOf(o.currentStep || 'marketing-intake');
        const pct        = Math.max(8, Math.round(((stepIdx + 1) / STEP_ORDER.length) * 100));
        const savedDate  = o.savedAt ? new Date(o.savedAt).toLocaleDateString('en-GB') : '-';
        return `
            <tr>
                <td><span class="znz-id" style="cursor:pointer;" onclick="loadOrderFromDashboard('${o.id}','${o.currentStep||'marketing-intake'}')" title="Click to open this order">${o.id || '-'}</span></td>
                <td>${o.customer || '-'}</td>
                <td>${o.poNumber || '-'}</td>
                <td>${o.salesperson || '-'}</td>
                <td>${o.buyerCode || '-'}</td>
                <td>${o.deliveryDate || '-'}</td>
                <td>${o.rows?.length || 0}</td>
                <td>
                    <div class="dash-step-wrap">
                        <span class="step-badge">${stepLabel}</span>
                        <div class="dash-progress"><div class="dash-progress-fill" style="width:${pct}%"></div></div>
                    </div>
                </td>
                <td>${savedDate}</td>
                <td class="dash-actions">
                    <button class="primary-btn ghost-btn--sm" onclick="loadOrderFromDashboard('${o.id}','${o.currentStep||'marketing-intake'}')">▶ Go to ${stepLabel}</button>
                    <button class="ghost-btn ghost-btn--sm dash-del-btn" onclick="deleteOrderFromDashboard('${o.id}')">Del</button>
                </td>
            </tr>`;
    }).join('');
}

// Map step id → PHP page filename
const STEP_PAGES = {
    'marketing-intake': 'marketing-intake.php',
    'costing-review':   'costing-review.php',
    'sales':            'sales.php',
    'marketing':        'marketing.php',
    'lc':               'lc.php',
    'po-overview':      'po-overview.php',
    'exchange':         'exchange.php',
    'commercial':       'commercial.php',
    'packing':          'packing.php',
    'delivery':         'delivery.php',
    'truck':            'truck.php',
    'origin':           'origin.php',
    'beneficiary':      'beneficiary.php',
    'forwarding':       'forwarding.php',
    'po-status':        'po-status.php',
};

function loadOrderFromDashboard(orderId, knownStep) {
    // Set sessionStorage — target page will load order data directly from DB
    sessionStorage.setItem('ats_current_order_id', orderId);

    const targetStep = knownStep || 'marketing-intake';
    const page = STEP_PAGES[targetStep] || 'marketing-intake.php';
    window.location.href = APP_BASE + '/pages/' + page;
}

function deleteOrderFromDashboard(orderId) {
    const orders = getOrdersList().filter(o => o.id !== orderId);
    saveOrdersList(orders);
    // Also clear active if it's the same
    const active = loadIntakeState();
    if (active?.id === orderId) localStorage.removeItem(ED_INTAKE_KEY);
    renderDashboard();
}

function startNewOrder() {
    localStorage.removeItem(ED_INTAKE_KEY);
    sessionStorage.removeItem('ats_current_order_id');

    // If on the standalone dashboard.php, navigate to Marketing Intake directly
    if (window.location.pathname.includes('dashboard.php')) {
        window.location.href = APP_BASE + '/pages/marketing-intake.php';
        return;
    }

    if (typeof intakeRows !== 'undefined') {
        intakeRows.length = 0;
        intakeRows.push(createIntakeRow());
        renderIntakeRows();
    }
    ['intakeCustomer','intakeSalesperson','intakeDate','intakePoNumber','intakeTrimsNo',
     'intakeSubject','intakePaperQuality','intakeBuyerName','intakeDesign','intakeOrderNo',
     'intakeType','intakeDeliveryDate','intakeMarketingNotes'].forEach(id => {
        const el = document.getElementById(id); if (el) el.value = '';
    });
    const arl = document.getElementById('intakeWithoutArl');
    if (arl) arl.checked = false;
    if (typeof renderBuyerSelect === 'function') renderBuyerSelect('');
    showPage('marketing-intake');
    setFeedback('New order started', 'A fresh Marketing Intake is ready.');
}
// ── savePageData — persist a page's form data to the DB ─────────────────────
async function savePageData(page, data) {
    const state = loadIntakeState();
    const orderId = state?.id;
    if (!orderId) return; // No active order — nothing to save
    try {
        const res = await fetch(APP_BASE + '/api/page_data.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ orderId, page, data }),
        });
        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            console.warn('savePageData error:', err);
        }
    } catch (e) {
        console.warn('savePageData fetch failed:', e);
    }
}

// Collect visible form fields from a page screen into a plain object
function collectPageData(pageId) {
    const screen = document.querySelector(`.page-screen[data-page="${pageId}"]`);
    if (!screen) return {};
    const data = {};
    screen.querySelectorAll('input[id], textarea[id], select[id]').forEach(el => {
        if (el.type === 'checkbox') {
            data[el.id] = el.checked;
        } else {
            data[el.id] = el.value;
        }
    });
    return data;
}
// ── end order tracking ───────────────────────────────────────────────────────

function setFeedback(title, text) {
    if (!feedbackBox) return;
    feedbackBox.innerHTML = `<strong>${title}</strong><span>${text}</span>`;
}

function showPage(pageId) {
    tabs.forEach((tab) => {
        tab.classList.toggle("active", tab.dataset.pageTarget === pageId);
    });

    screens.forEach((screen) => {
        screen.classList.toggle("active", screen.dataset.page === pageId);
    });

    if (globalStatus && pageStageMap[pageId]) {
        globalStatus.value = pageStageMap[pageId];
    }

    const activeTab = document.querySelector(`.page-tab[data-page-target="${pageId}"]`);
    if (activeTab) {
        setFeedback("Page changed", `Now showing ${activeTab.textContent}. The URL stays the same and the screen swaps inside this one workspace.`);
    }

    if (pageId === "po-status") {
        loadDefaultChallanDemo();
    }
    if (pageId === "costing-review") {
        syncCostingFromIntake();
    }
}

tabs.forEach((tab) => {
    tab.addEventListener("click", () => showPage(tab.dataset.pageTarget));
});

let activeSection = null;

function switchSection(sectionId) {
    // clicking the already-open section collapses it
    if (activeSection === sectionId) {
        activeSection = null;
        sectionBtns.forEach((btn) => btn.classList.remove("active"));
        tabGroups.forEach((group) => group.classList.add("hidden"));
        return;
    }
    activeSection = sectionId;
    sectionBtns.forEach((btn) => btn.classList.toggle("active", btn.dataset.section === sectionId));
    tabGroups.forEach((group) => group.classList.toggle("hidden", group.dataset.sectionGroup !== sectionId));
    // auto-show first tab in the section if none is already active
    const activeGroup = document.querySelector(`.nav-tab-group[data-section-group="${sectionId}"]`);
    if (activeGroup) {
        const hasActive = activeGroup.querySelector(".page-tab.active");
        if (!hasActive) {
            const firstTab = activeGroup.querySelector(".page-tab");
            if (firstTab) showPage(firstTab.dataset.pageTarget);
        }
    }
}

sectionBtns.forEach((btn) => {
    btn.addEventListener("click", () => switchSection(btn.dataset.section));
});

// On PHP pages footer.php handles js-next-page (with auto-save). Only handle here for SPA sections.
if (!document.body.dataset.page) {
    document.querySelectorAll(".js-next-page").forEach((button) => {
        button.addEventListener("click", () => {
            const nextPage = button.dataset.nextPage;
            if (!nextPage) return;
            const phpPage = STEP_PAGES[nextPage];
            if (phpPage) window.location.href = APP_BASE + '/pages/' + phpPage;
            else showPage(nextPage);
        });
    });
}

document.querySelectorAll(".js-prev-page").forEach((button) => {
    button.addEventListener("click", () => {
        const prevPage = button.dataset.prevPage;
        if (!prevPage) return;
        const phpPage = STEP_PAGES[prevPage];
        if (phpPage) window.location.href = APP_BASE + '/pages/' + phpPage;
        else showPage(prevPage);
    });
});

const erpOrders = [];

const buyerMaster = [];

const itemMaster = [];

// Party Master — salesperson → list of customer/party names
// Party name is the customer. Bold rows in the report = salesperson.
const partyMaster = [
    { salesperson: "Mr. Alamgir", party: "4A Yarn Dyeing Limited" },
    { salesperson: "Mr. Alamgir", party: "Brothers Fashion Ltd" },
    { salesperson: "Mr. Alamgir", party: "CBM International Limited" },
    { salesperson: "Mr. Alamgir", party: "Mars Stitch Limited" },
    { salesperson: "Mr. Alamgir", party: "Newage Appreal Ltd" },
    { salesperson: "Mr. Alamgir", party: "Temakaw Fashion Ltd" },
    { salesperson: "Mr. Alamgir", party: "Ayasha & Galeya Fashion ltd" },
    { salesperson: "Mr. Alamgir", party: "Bhorka Textile Limited" },
    { salesperson: "Mr. Alamgir", party: "Sunrise knit wear ltd" },
    { salesperson: "Mr. Asad",    party: "Alija Fashion Ltd" },
    { salesperson: "Mr. Asad",    party: "Prostar Apparels Ltd" },
    { salesperson: "Mr. Asad",    party: "Ram Apparels Ltd" },
    { salesperson: "Mr. Ashan Habib", party: "Classical Handmade Products Bd Ltd" },
    { salesperson: "Mr. Ashan Habib", party: "Debonair Bag & Luggage Ltd" },
    { salesperson: "Mr. Ashan Habib", party: "Debonair Padding & Quilting Solutions Ltd." },
    { salesperson: "Mr. Ashan Habib", party: "Karupannya Rangpur Ltd" },
    { salesperson: "Mr. Ashan Habib", party: "Stark Bag Industries Ltd" },
    { salesperson: "Mr. Jewel",   party: "Esquire knit composite ltd." },
    { salesperson: "Mr. Jewel",   party: "Mavis Garments Ltd" },
    { salesperson: "Mr. Mahfuz",  party: "Design Tex Knitwears Ltd" },
    { salesperson: "Mr. Mahfuz",  party: "Lida Textile & Dyeing Limited" },
    { salesperson: "Mr. Mahfuz",  party: "Liz Fashion Industry Limited" },
    { salesperson: "Mr. Mahfuz",  party: "Liz Fashion Industry Ltd" },
    { salesperson: "Mr. Mahfuz",  party: "LIZ Fashion Industry Ltd" },
    { salesperson: "Mr. Mahfuz",  party: "Power Hi-Tech Apparels Ltd" },
    { salesperson: "Mr. Mahfuz",  party: "Tarasima Apparels Limited" },
    { salesperson: "Mr. Mamun",   party: "Daeyu Bangladesh Ltd." },
    { salesperson: "Mr. Mamun",   party: "Ever Fashion Tongi Ltd." },
    { salesperson: "Mr. Mamun",   party: "Goodearth Apparel Ltd" },
    { salesperson: "Mr. Mamun",   party: "Life Textile (Pvt) Ltd" },
    { salesperson: "Mr. Mamun",   party: "Muazuddin Knit Fshion Ltd" },
    { salesperson: "Mr. Mamun",   party: "Multifabs Ltd" },
    { salesperson: "Mr. Mamun",   party: "Sharim Group" },
    { salesperson: "Mr. Mamun",   party: "Tex Zippers (BD) Ltd" },
    { salesperson: "Mr. Mamun",   party: "Tigerco Ltd" },
    { salesperson: "Mr. Rajib",   party: "Acs Textiles (BD) Ltd" },
    { salesperson: "Mr. Rajib",   party: "Mimo Cotton Zone Ltd" },
    { salesperson: "Mr. Rajib",   party: "Natural Indigo Ltd" },
    { salesperson: "Mr. Tanvir",  party: "Zaber & Zubair Fabrics Ltd." },
];

function getSalespersonForCustomer(customerName) {
    if (!customerName) return "";
    const match = partyMaster.find(p =>
        p.party.toLowerCase() === customerName.toLowerCase()
    );
    return match ? match.salesperson : "";
}

function autoFillSalesperson(customerName) {
    const sp = getSalespersonForCustomer(customerName);
    const field = document.getElementById("intakeSalesperson");
    if (field) field.value = sp;
}

function renderPartyMaster() {
    const body = document.getElementById("partyMasterBody");
    if (!body) return;
    // Group by salesperson
    const groups = {};
    partyMaster.forEach(p => {
        if (!groups[p.salesperson]) groups[p.salesperson] = [];
        groups[p.salesperson].push(p.party);
    });
    body.innerHTML = Object.entries(groups).map(([sp, parties]) =>
        parties.map((party, i) => `
            <tr>
                ${i === 0 ? `<td rowspan="${parties.length}" class="party-master-sp">${sp}</td>` : ""}
                <td>${party}</td>
                <td><button type="button" class="ghost-btn ghost-btn--sm" onclick="removePartyEntry('${sp.replace(/'/g,"\\'")}','${party.replace(/'/g,"\\'")}')">Remove</button></td>
            </tr>
        `).join("")
    ).join("");
}

function removePartyEntry(salesperson, party) {
    const idx = partyMaster.findIndex(p => p.salesperson === salesperson && p.party === party);
    if (idx !== -1) { partyMaster.splice(idx, 1); renderPartyMaster(); }
}

function openPartyModal() {
    const modal = document.getElementById("partyModal");
    if (!modal) return;
    // Populate salesperson select
    const spSel = document.getElementById("newPartySalesperson");
    if (spSel) {
        const existing = [...new Set(partyMaster.map(p => p.salesperson))];
        spSel.innerHTML = existing.map(s => `<option value="${s}">${s}</option>`).join("") +
            '<option value="__new__">+ Add new salesperson…</option>';
    }
    modal.classList.remove("hidden");
}

function closePartyModal() {
    document.getElementById("partyModal")?.classList.add("hidden");
}

function savePartyFromModal() {
    const spSel = document.getElementById("newPartySalesperson");
    const newSpInput = document.getElementById("newPartySalespersonName");
    const partyInput = document.getElementById("newPartyName");
    const sp = spSel?.value === "__new__"
        ? newSpInput?.value?.trim()
        : spSel?.value?.trim();
    const party = partyInput?.value?.trim();
    if (!sp || !party) return;
    partyMaster.push({ salesperson: sp, party });
    renderPartyMaster();
    if (partyInput) partyInput.value = "";
    closePartyModal();
    setFeedback("Party saved", `${party} assigned to ${sp}.`);
}

const searchInput = document.getElementById("globalOrderSearch");
const globalBuyer = document.getElementById("globalBuyer");
const searchButton = document.getElementById("searchErp");
const clearButton = document.getElementById("clearSearch");
const erpResultText = document.getElementById("erpResultText");
const challanPiSearch = document.getElementById("challanPiSearch");
const challanPiButton = document.getElementById("searchChallanPi");
const sampleOrderNumber = "";
const intakeBuyerSelect = document.getElementById("intakeBuyerSelect");
const intakeItemsBody = document.getElementById("intakeItemsBody");
const costingItemsBody = document.getElementById("costingItemsBody");
const buyerMasterBody = document.getElementById("buyerMasterBody");
const itemMasterBody = document.getElementById("itemMasterBody");
const buyerModal = document.getElementById("buyerModal");
const itemModal = document.getElementById("itemModal");
const newItemProductLine = document.getElementById("newItemProductLine");
const newItemNameInput = document.getElementById("newItemName");
const newItemNameList = document.getElementById("itemNameList");
const newItemGradeWrap = document.getElementById("newItemGradeWrap");
const newItemGrade = document.getElementById("newItemGrade");
const newItemGradeText = document.getElementById("newItemGradeText");
const newItemCombWrap = document.getElementById("newItemCombWrap");
const newItemComb = document.getElementById("newItemComb");
const newItemPrice = document.getElementById("newItemPrice");
const costingStatusText = document.getElementById("costingStatusText");

const salesFields = {
    piNumber: document.getElementById("piNumber"),
    salesOrder: document.getElementById("salesOrder"),
    buyerName: document.getElementById("buyerName"),
    customerName: document.getElementById("customerName"),
    customerPo: document.getElementById("customerPo"),
    productLine: document.getElementById("productLine"),
    requestedDateErp: document.getElementById("requestedDateErp"),
    orderStatus: document.getElementById("orderStatus"),
    buyerAddress: document.getElementById("buyerAddress"),
    consigneeBank: document.getElementById("consigneeBank"),
};

const salesItemsBody = document.getElementById("salesItemsBody");
const salesItemsTotalQty = document.getElementById("salesItemsTotalQty");
const salesItemsTotalAmount = document.getElementById("salesItemsTotalAmount");
const commercialItemsBody = document.getElementById("commercialItemsBody");
const commercialTotalQty = document.getElementById("commercialTotalQty");
const commercialTotalAmount = document.getElementById("commercialTotalAmount");
const poStatusItemsBody = document.getElementById("poStatusItemsBody");
const challanItemsBody = document.getElementById("challanItemsBody");
const packingItemsBody = document.getElementById("packingItemsBody");
const packingTotalQty = document.getElementById("packingTotalQty");
const deliveryItemsBody = document.getElementById("deliveryItemsBody");
const deliveryTotalQty = document.getElementById("deliveryTotalQty");
const truckItemsBody = document.getElementById("truckItemsBody");
const truckTotalQty = document.getElementById("truckTotalQty");
let activeErpOrder = null;
let intakeRowSeq = 1;

function getBuyerByCode(code) {
    return buyerMaster.find((buyer) => buyer.code === code);
}

function getGradesForItem(itemName) {
    return itemMaster.filter((item) => item.itemName === itemName);
}

function renderBuyerSelect(customerFilter) {
    if (!intakeBuyerSelect) return;
    const prevValue = intakeBuyerSelect.value;
    const filter = customerFilter !== undefined
        ? customerFilter
        : (document.getElementById("intakeCustomer")?.value || "");
    const pool = filter
        ? buyerMaster.filter(b => b.customer === filter)
        : buyerMaster;
    if (pool.length === 0) {
        intakeBuyerSelect.innerHTML = '<option value="">— No buyers for this customer —</option>';
        return;
    }
    intakeBuyerSelect.innerHTML = pool.map(b =>
        `<option value="${b.code}">${b.code} - ${b.name}</option>`
    ).join("");
    intakeBuyerSelect.value = prevValue && pool.find(b => b.code === prevValue)
        ? prevValue
        : pool[0]?.code || "";
}

function renderBuyerMaster() {
    if (!buyerMasterBody) return;
    buyerMasterBody.innerHTML = buyerMaster.map((buyer) => `
        <tr>
            <td>${buyer.customer || "—"}</td>
            <td>${buyer.code}</td>
            <td>${buyer.name}</td>
            <td>${buyer.address}</td>
        </tr>
    `).join("");
}

function renderItemMaster() {
    if (!itemMasterBody) return;
    itemMasterBody.innerHTML = itemMaster.map((item) => `
        <tr>
            <td>${item.productLine || ""}</td>
            <td>${item.itemName}</td>
            <td>${item.grade}</td>
            <td>${item.paperCombination || "N/A"}</td>
            <td>${item.price.toFixed(4)}</td>
        </tr>
    `).join("");
}

function populateItemNameList(productLine) {
    if (!newItemNameList) return;
    const items = (typeof PRODUCT_LINE_ITEMS !== "undefined" && PRODUCT_LINE_ITEMS[productLine]) || [];
    newItemNameList.innerHTML = items.map((name) => `<option value="${name.replace(/"/g, '&quot;')}"></option>`).join("");
}

function populateGrades() {
    if (!newItemGradeWrap || !newItemGrade) return;
    const isCarton = newItemProductLine?.value === "Carton";
    if (isCarton && typeof PAPER_GRADES !== "undefined") {
        // Carton: show all unique combinations; grade auto-fills on combo selection
        const allCombs = [...new Set(Object.values(PAPER_GRADES).flatMap((g) => g.combinations))];
        newItemComb.innerHTML = '<option value="">— Select Combination —</option>' +
            allCombs.map((c) => `<option value="${c}">${c}</option>`).join("");
        newItemCombWrap?.classList.remove("hidden");
        // Populate grade options (auto-filled, read-only feel)
        newItemGrade.innerHTML = '<option value="">— auto-filled from combination —</option>' +
            Object.keys(PAPER_GRADES).map((g) => {
                const ply = PAPER_GRADES[g].ply;
                return `<option value="${g}">${g} (${ply})</option>`;
            }).join("");
        newItemGrade.classList.remove("hidden");
        if (newItemGradeText) { newItemGradeText.style.display = "none"; newItemGradeText.value = ""; }
    } else {
        // Non-Carton: hide combination, show free-text grade
        newItemCombWrap?.classList.add("hidden");
        newItemComb.innerHTML = '<option value="">— Select Combination —</option>';
        newItemGrade.innerHTML = '<option value="">— auto-filled from combination —</option>';
        newItemGrade.classList.add("hidden");
        if (newItemGradeText) newItemGradeText.style.display = "";
    }
}

function populateCombinations() {
    // Auto-fill grade based on selected combination
    if (!newItemComb || !newItemGrade || typeof PAPER_GRADES === "undefined") return;
    const selectedComb = newItemComb.value;
    if (!selectedComb) { newItemGrade.value = ""; return; }
    for (const [grade, data] of Object.entries(PAPER_GRADES)) {
        if (data.combinations.includes(selectedComb)) {
            newItemGrade.value = grade;
            return;
        }
    }
    newItemGrade.value = "";
}

function openItemCreateModal() {
    if (!itemModal) return;
    if (newItemProductLine) newItemProductLine.value = "";
    if (newItemNameInput) newItemNameInput.value = "";
    if (newItemNameList) newItemNameList.innerHTML = "";
    if (newItemGrade) { newItemGrade.innerHTML = '<option value="">— Select Grade —</option>'; newItemGrade.classList.add("hidden"); }
    if (newItemGradeText) { newItemGradeText.style.display = "none"; newItemGradeText.value = ""; }
    if (newItemComb) newItemComb.innerHTML = '<option value="">— Select Combination —</option>';
    if (newItemCombWrap) newItemCombWrap.classList.add("hidden");
    if (newItemPrice) newItemPrice.value = "";
    itemModal.classList.remove("hidden");
}

function closeItemCreateModal() {
    itemModal?.classList.add("hidden");
}

function saveItemFromModal() {
    const productLine = newItemProductLine?.value?.trim();
    const itemName = newItemNameInput?.value?.trim();
    if (!productLine || !itemName) return;
    const isCarton = productLine === "Carton";
    const gradeVal = isCarton
        ? (newItemGrade?.value?.trim() || "")
        : (newItemGradeText?.value?.trim() || "");
    const grade = gradeVal || "N/A";
    const paperCombination = (isCarton && gradeVal && newItemComb?.value?.trim()) ? newItemComb.value.trim() : "N/A";
    const price = parseFloat(newItemPrice?.value) || 0;
    itemMaster.push({ productLine, itemName, grade, paperCombination, price });
    renderItemMaster();
    closeItemCreateModal();
}

function createIntakeRow(row = {}) {
    return {
        id: row.id || `intake-${intakeRowSeq++}`,
        colour: row.colour || "",
        orderNo: row.orderNo || "",
        productNo: row.productNo || "",
        batchNo: row.batchNo || "",
        productLine: row.productLine || "",
        itemName: row.itemName || "",
        artSize: row.artSize || "",
        grade: row.grade || "",
        paperCombination: row.paperCombination || "",
        setQty: row.setQty || "",
        gasset: row.gasset || "",
        setPerQty: row.setPerQty || "",
        reqQty: row.reqQty || "",
        calQty: row.calQty || "",
        qty: row.qty || "",
        price: row.price || "",
    };
}

const intakeRows = [
    createIntakeRow({ productLine: "Carton", itemName: "Carton - 62x54x31 CM", grade: "Grade 1", qty: "1096", price: "1.5776" }),
    createIntakeRow({ productLine: "Carton", itemName: "Carton - 23x10.5x14",  grade: "Grade 1", qty: "7036", price: "0.5733" }),
];

function calcIntakeRowTotal(row) {
    const q = parseFloat(row.qty) || 0;
    const p = parseFloat(row.price) || 0;
    return q * p;
}

function buildIntakeItemCell(row) {
    const allPLs = (typeof PRODUCT_LINE_ITEMS !== "undefined") ? Object.keys(PRODUCT_LINE_ITEMS).sort() : [];
    const plOpts = allPLs.map(pl =>
        `<option value="${pl}" ${pl === row.productLine ? "selected" : ""}>${pl}</option>`
    ).join("");

    const currentItems = (row.productLine && PRODUCT_LINE_ITEMS?.[row.productLine]) || [];
    const itemOpts = currentItems.map(n =>
        `<option value="${n}" ${n === row.itemName ? "selected" : ""}>${n}</option>`
    ).join("");

    const isCarton = row.productLine === "Carton";
    const allGrades = (typeof PAPER_GRADES !== "undefined") ? Object.keys(PAPER_GRADES) : [];
    const gradeOpts = allGrades.map(g =>
        `<option value="${g}" ${g === row.grade ? "selected" : ""}>${g} (${PAPER_GRADES[g].ply})</option>`
    ).join("");

    const currentCombs = (row.grade && PAPER_GRADES?.[row.grade]?.combinations) || [];
    const combOpts = currentCombs.map(c =>
        `<option value="${c}" ${c === row.paperCombination ? "selected" : ""}>${c}</option>`
    ).join("");

    const itemHidden  = row.productLine ? "" : " hidden";
    const gradeHidden = (isCarton && row.itemName) ? "" : " hidden";
    const combHidden  = (isCarton && row.grade) ? "" : " hidden";

    return `
        <div class="intake-item-cell">
            <select class="table-select intake-pl-select">
                <option value="">— Product Line —</option>${plOpts}
            </select>
            <select class="table-select intake-item-select${itemHidden}">
                <option value="">— Item —</option>${itemOpts}
            </select>
            <select class="table-select intake-grade-select${gradeHidden}">
                <option value="">— Grade —</option>${gradeOpts}
            </select>
            <select class="table-select intake-comb-select${combHidden}">
                <option value="">— Combination —</option>${combOpts}
            </select>
        </div>`;
}

function renderIntakeRows() {
    if (!intakeItemsBody) return;
    intakeItemsBody.innerHTML = intakeRows.map((row, index) => {
        const total = calcIntakeRowTotal(row);
        return `
            <tr data-intake-row="${row.id}">
                <td>${index + 1}</td>
                <td><input class="table-entry" data-field="colour" value="${row.colour}" placeholder="Colour"></td>
                <td><input class="table-entry" data-field="orderNo" value="${row.orderNo}" placeholder="Order No"></td>
                <td><input class="table-entry" data-field="productNo" value="${row.productNo}" placeholder="Prod No"></td>
                <td><input class="table-entry" data-field="batchNo" value="${row.batchNo}" placeholder="Batch No"></td>
                <td class="intake-item-td">${buildIntakeItemCell(row)}</td>
                <td><input class="table-entry" data-field="artSize" value="${row.artSize}" placeholder="e.g. 62x54x31 CM"></td>
                <td><input class="table-entry num" data-field="setQty" value="${row.setQty}" placeholder="0"></td>
                <td><input class="table-entry num" data-field="gasset" value="${row.gasset}" placeholder="0"></td>
                <td><input class="table-entry num" data-field="setPerQty" value="${row.setPerQty}" placeholder="0"></td>
                <td><input class="table-entry num" data-field="reqQty" value="${row.reqQty}" placeholder="0"></td>
                <td><input class="table-entry num" data-field="calQty" value="${row.calQty}" placeholder="0"></td>
                <td><input class="table-entry num" data-field="qty" value="${row.qty}" placeholder="0"></td>
                <td><input class="table-entry num" data-field="price" value="${row.price}" placeholder="0.0000"></td>
                <td class="intake-row-total">$${total.toFixed(2)}</td>
                <td><button type="button" class="intake-del-btn" data-intake-del="${row.id}">&#x2715;</button></td>
            </tr>
        `;
    }).join("");
    attachIntakeRowListeners();
    updateIntakeTotals();
}

function updateIntakeTotals() {
    const totalQty = intakeRows.reduce((s, r) => s + (parseFloat(r.qty) || 0), 0);
    const totalAmt = intakeRows.reduce((s, r) => s + calcIntakeRowTotal(r), 0);
    const tq = document.getElementById("intakeTotalOrderQty");
    const ta = document.getElementById("intakeTotalPrice");
    if (tq) tq.textContent = totalQty;
    if (ta) ta.textContent = `$${totalAmt.toFixed(2)}`;
}

function populateIntakeCustomer() {
    const sel = document.getElementById("intakeCustomer");
    if (!sel) return;
    const customers = [...new Set(buyerMaster.map((b) => b.customer).filter(Boolean))];
    sel.innerHTML = '<option value="">— Select Customer —</option>' +
        customers.map((c) => `<option value="${c}">${c}</option>`).join("");
}

function attachIntakeRowListeners() {
    if (!intakeItemsBody) return;
    intakeItemsBody.querySelectorAll("tr").forEach((rowEl, index) => {
        const row = intakeRows[index];
        if (!row) return;

        // ── Product Line → populate items, show/hide grade ─────────────────
        rowEl.querySelector(".intake-pl-select")?.addEventListener("change", (e) => {
            row.productLine = e.target.value;
            row.itemName = "";
            row.grade = "";
            row.paperCombination = "";

            const itemSel  = rowEl.querySelector(".intake-item-select");
            const gradeSel = rowEl.querySelector(".intake-grade-select");
            const combSel  = rowEl.querySelector(".intake-comb-select");

            // Populate item options for this product line
            const items = PRODUCT_LINE_ITEMS?.[row.productLine] || [];
            itemSel.innerHTML = '<option value="">— Item —</option>' +
                items.map(n => `<option value="${n}">${n}</option>`).join("");
            itemSel.classList.toggle("hidden", !row.productLine);

            // Reset and hide grade & combo
            gradeSel.value = "";
            gradeSel.classList.add("hidden");
            combSel.value = "";
            combSel.classList.add("hidden");

            // Pre-populate grade options if Carton
            if (row.productLine === "Carton") {
                gradeSel.innerHTML = '<option value="">— Grade —</option>' +
                    Object.keys(PAPER_GRADES).map(g =>
                        `<option value="${g}">${g} (${PAPER_GRADES[g].ply})</option>`
                    ).join("");
            }

            updateIntakeTotals();
        });

        // ── Item → auto-fill price, show grade if Carton ──────────────────
        rowEl.querySelector(".intake-item-select")?.addEventListener("change", (e) => {
            row.itemName = e.target.value;
            row.grade = "";
            row.paperCombination = "";

            const match = itemMaster.find(i => i.itemName === row.itemName);
            if (match) {
                row.price = `${match.price}`;
                const priceInput = rowEl.querySelector("[data-field='price']");
                if (priceInput) priceInput.value = match.price;
            }

            const gradeSel = rowEl.querySelector(".intake-grade-select");
            const combSel  = rowEl.querySelector(".intake-comb-select");
            gradeSel.value = "";
            combSel.value = "";
            combSel.classList.add("hidden");
            gradeSel.classList.toggle("hidden", row.productLine !== "Carton");

            const totalCell = rowEl.querySelector(".intake-row-total");
            if (totalCell) totalCell.textContent = `$${calcIntakeRowTotal(row).toFixed(2)}`;
            updateIntakeTotals();
        });

        // ── Grade → populate combinations ─────────────────────────────────
        rowEl.querySelector(".intake-grade-select")?.addEventListener("change", (e) => {
            row.grade = e.target.value;
            row.paperCombination = "";

            const combSel = rowEl.querySelector(".intake-comb-select");
            const combs = (row.grade && PAPER_GRADES?.[row.grade]?.combinations) || [];
            combSel.innerHTML = '<option value="">— Combination —</option>' +
                combs.map(c => `<option value="${c}">${c}</option>`).join("");
            combSel.classList.toggle("hidden", !row.grade);
        });

        // ── Combination ────────────────────────────────────────────────────
        rowEl.querySelector(".intake-comb-select")?.addEventListener("change", (e) => {
            row.paperCombination = e.target.value;
        });

        // ── Numeric + text inputs ──────────────────────────────────────────
        rowEl.querySelectorAll("[data-field]").forEach((input) => {
            input.addEventListener("input", () => {
                row[input.dataset.field] = input.value;
                if (["qty", "price"].includes(input.dataset.field)) {
                    const totalCell = rowEl.querySelector(".intake-row-total");
                    if (totalCell) totalCell.textContent = `$${calcIntakeRowTotal(row).toFixed(2)}`;
                    updateIntakeTotals();
                }
            });
        });

        // ── Delete row ─────────────────────────────────────────────────────
        rowEl.querySelector(".intake-del-btn")?.addEventListener("click", () => {
            const delId = rowEl.querySelector(".intake-del-btn").dataset.intakeDel;
            const idx = intakeRows.findIndex((r) => r.id === delId);
            if (idx !== -1) { intakeRows.splice(idx, 1); renderIntakeRows(); }
        });
    });
}

function syncCostingFromIntake() {
    // Read from DOM if on marketing-intake page, otherwise fall back to localStorage
    const s = loadIntakeState() || {};
    const dv = (id, key) => document.getElementById(id)?.value || s[key] || '';
    const po          = dv('intakePoNumber',       'poNumber');
    const customer    = dv('intakeCustomer',        'customer');
    const salesperson = dv('intakeSalesperson',     'salesperson');
    const buyerCode   = document.getElementById('intakeBuyerSelect')?.value || s.buyerCode || '';
    const buyer       = getBuyerByCode(buyerCode);
    const date        = dv('intakeDate',            'date');
    const trimsNo     = dv('intakeTrimsNo',         'trimsNo');
    const subject     = dv('intakeSubject',         'subject');
    const paperQuality= dv('intakePaperQuality',    'paperQuality');
    const buyerName   = dv('intakeBuyerName',       'buyerEndName');
    const design      = dv('intakeDesign',          'design');
    const orderNo     = dv('intakeOrderNo',         'orderNo');
    const type        = dv('intakeType',            'type');
    const deliveryDate= dv('intakeDeliveryDate',    'deliveryDate');
    const notes       = dv('intakeMarketingNotes',  'notes');
    const withoutArl  = document.getElementById('intakeWithoutArl')?.checked
        ? 'Yes' : (s.withoutArl ? 'Yes' : 'No');
    const toBuyer     = buyer ? `${buyer.code} — ${buyer.name}` : (buyerCode || '');

    // Status strip
    const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val || "-"; };
    set("costingPoText", po);
    set("costingCustomerText", customer);
    set("costingSalespersonText", salesperson);
    set("costingBuyerText", buyer?.code || "");

    // Mirror fields
    const setVal = (id, val) => { const el = document.getElementById(id); if (el) el.value = val || ""; };
    setVal("costingDate", date);
    setVal("costingTrimsNo", trimsNo);
    setVal("costingToBuyer", toBuyer);
    setVal("costingPaperQuality", paperQuality);
    setVal("costingBuyerName", buyerName);
    setVal("costingDesign", design);
    setVal("costingOrderNo", orderNo);
    setVal("costingType", type);
    setVal("costingDeliveryDate", deliveryDate);
    setVal("costingWithoutArl", withoutArl);
    setVal("costingSubject", subject);
    setVal("costingMarketingNotes", notes);

    // Item rows — use saved rows from localStorage if on a different page
    const activeRows = intakeRows.length ? intakeRows : (s.rows || []);
    if (costingItemsBody) {
        costingItemsBody.innerHTML = activeRows.map((row, index) => `
            <tr>
                <td>${index + 1}</td>
                <td>${row.colour || ""}</td>
                <td>${row.orderNo || ""}</td>
                <td>${row.productNo || ""}</td>
                <td>${row.batchNo || ""}</td>
                <td>${row.productLine || ""}</td>
                <td>${row.itemName || ""}</td>
                <td>${row.artSize || ""}</td>
                <td>${row.grade || ""}</td>
                <td>${row.paperCombination || ""}</td>
                <td>${row.qty || ""}</td>
                <td>${row.price || ""}</td>
                <td><input class="table-entry costing-price-input" data-index="${index}" value="${row.price || ""}"></td>
                <td>
                    <select class="table-select costing-status-select" data-index="${index}">
                        <option>Pending</option>
                        <option>Revised</option>
                        <option>Approved</option>
                    </select>
                </td>
            </tr>
        `).join("");
    }
}

function syncPiFromIntake() {
    const buyer = getBuyerByCode(intakeBuyerSelect?.value || "");
    const po = document.getElementById("intakePoNumber")?.value || "";
    if (salesFields.customerPo) salesFields.customerPo.value = po;
    if (salesFields.buyerName) salesFields.buyerName.value = buyer?.code || "";
    if (salesFields.customerName) salesFields.customerName.value = buyer?.name || "";
    if (salesFields.buyerAddress) salesFields.buyerAddress.value = buyer?.address || "";
    if (salesFields.productLine) salesFields.productLine.value = intakeRows[0]?.itemName || "";
    renderSalesItems(intakeRows.map((row, index) => ({
        sl: `${index + 1}`,
        description: row.itemName,
        ply: row.grade,
        qty: row.qty,
        price: row.price,
        amount: `${((parseFloat(row.qty || "0") || 0) * (parseFloat(row.price || "0") || 0)).toFixed(2)}`,
    })));
    syncPoOverviewFromSources();
    syncCommercialFromSources();
    syncPackingFromSources();
    syncDeliveryFromSources();
    syncTruckFromSources();
    syncOriginFromSources();
    syncBeneficiaryFromSources();
    syncForwardingFromSources();
    syncExchangeFromSources();
}

function openBuyerCreateModal() {
    // Pre-fill customer from whatever is selected in the intake form
    const currentCustomer = document.getElementById("intakeCustomer")?.value || "";
    const customerInput = document.getElementById("newBuyerCustomer");
    if (customerInput && currentCustomer) customerInput.value = currentCustomer;
    buyerModal?.classList.remove("hidden");
}

function closeBuyerCreateModal() {
    buyerModal?.classList.add("hidden");
}

async function saveBuyerFromModal() {
    const customer = document.getElementById("newBuyerCustomer")?.value?.trim();
    const code = document.getElementById("newBuyerCode")?.value?.trim();
    const name = document.getElementById("newBuyerName")?.value?.trim();
    const address = document.getElementById("newBuyerAddress")?.value?.trim();
    if (!code || !name) return;
    buyerMaster.push({ customer: customer || "", code, name, address });
    // Re-populate customer list in case this is a new customer
    populateIntakeCustomer();
    // If the new buyer has a customer, set that customer in intake and filter buyers
    if (customer) {
        const custSel = document.getElementById("intakeCustomer");
        if (custSel) custSel.value = customer;
        renderBuyerSelect(customer);
        autoFillSalesperson(customer);
    } else {
        renderBuyerSelect();
    }
    renderBuyerMaster();
    if (intakeBuyerSelect) intakeBuyerSelect.value = code;
    closeBuyerCreateModal();
    setFeedback("Buyer created", `${code} was added to Buyer Master and selected.`);

    // Persist to DB
    try {
        await fetch(APP_BASE + '/api/buyers.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ buyerCode: code, buyerName: name, customer: customer || '', address: address || '' }),
        });
    } catch (_) {}
}

function formatDateDisplay(value) {
    if (!value) return "";
    const [year, month, day] = value.split("-");
    if (!year || !month || !day) return value;
    return `${day}.${month}.${year}`;
}

function getLineItemsData() {
    if (!salesItemsBody) return [];

    return Array.from(salesItemsBody.querySelectorAll("tr")).map((row) => {
        const inputs = row.querySelectorAll("input");
        return {
            sl: inputs[0]?.value || "",
            description: inputs[1]?.value || "",
            ply: inputs[2]?.value || "",
            qty: parseFloat(inputs[3]?.value || "0") || 0,
            uom: "PCS",
            price: parseFloat(inputs[4]?.value || "0") || 0,
            amount: parseFloat(inputs[5]?.value || "0") || 0,
            deliveryDate: "",
            challan: "",
        };
    });
}

function renderItems(items) {
    renderSalesItems(items);
    syncPoOverviewFromSources();
    syncPoStatusFromSources();
    syncCommercialFromSources();
    syncPackingFromSources();
    syncDeliveryFromSources();
    syncTruckFromSources();
    syncOriginFromSources();
    syncBeneficiaryFromSources();
    syncForwardingFromSources();
    syncExchangeFromSources();
}

function clearDocumentText(ids) {
    ids.forEach((id) => {
        const el = document.getElementById(id);
        if (!el) return;
        el.textContent = "";
    });
}

function clearAllWorkflowData() {
    document.querySelectorAll('.page-screen input, .page-screen textarea').forEach((field) => {
        field.value = "";
    });

    document.querySelectorAll('.page-screen select').forEach((field) => {
        field.selectedIndex = 0;
    });

    if (salesItemsBody) {
        salesItemsBody.innerHTML = "";
    }
    if (commercialItemsBody) {
        commercialItemsBody.innerHTML = "";
    }
    if (poStatusItemsBody) {
        poStatusItemsBody.innerHTML = "";
    }
    if (challanItemsBody) {
        challanItemsBody.innerHTML = "";
    }
    if (packingItemsBody) {
        packingItemsBody.innerHTML = "";
    }
    if (deliveryItemsBody) {
        deliveryItemsBody.innerHTML = "";
    }
    if (truckItemsBody) {
        truckItemsBody.innerHTML = "";
    }

    if (salesItemsTotalQty) salesItemsTotalQty.textContent = "";
    if (salesItemsTotalAmount) salesItemsTotalAmount.textContent = "";
    if (commercialTotalQty) commercialTotalQty.textContent = "";
    if (commercialTotalAmount) commercialTotalAmount.textContent = "";
    if (packingTotalQty) packingTotalQty.textContent = "";
    if (deliveryTotalQty) deliveryTotalQty.textContent = "";
    if (truckTotalQty) truckTotalQty.textContent = "";

    clearDocumentText([
        "poStatusPoText", "poStatusStageText", "poStatusProducedText", "poStatusTotalText",
        "challanCustomerText", "challanSheetDateText", "challanQaCustomerLeft", "challanTotalQty",
        "commercialBeneficiaryName", "commercialBeneficiaryAddress", "commercialFactoryAddress", "commercialLcNoText",
        "commercialLcDateText", "commercialIssuingBankName", "commercialIssuingBankAddress", "commercialAdvisingBankName",
        "commercialAdvisingBankAddress", "commercialConsigneeName", "commercialConsigneeAddress", "commercialConsigneeBankName",
        "commercialConsigneeBankAddress", "commercialBuyerName", "commercialApplicantsText",
        "packingBeneficiaryName", "packingBeneficiaryAddress", "packingFactoryAddress", "packingConsigneeName",
        "packingConsigneeAddress", "packingAdvisingBankName", "packingAdvisingBankAddress", "packingConsigneeBankName",
        "packingConsigneeBankAddress", "packingDetailsText", "packingLcText", "packingApplicantsText",
        "packingContractText", "packingProformaText", "packingCarrierText", "packingFooterCompany",
        "deliveryInvoiceNo", "deliveryDateText", "deliveryBeneficiaryName", "deliveryBeneficiaryAddress",
        "deliveryFactoryAddress", "deliveryConsigneeName", "deliveryConsigneeAddress", "deliveryAdvisingBankName",
        "deliveryAdvisingBankAddress", "deliveryConsigneeBankName", "deliveryConsigneeBankAddress", "deliveryBuyerName",
        "deliveryPackingText", "deliveryLcText", "deliveryApplicantsText", "deliveryContractText",
        "deliveryProformaText", "deliveryCarrierText", "deliveryFooterCompany",
        "truckBeneficiaryName", "truckBeneficiaryAddress", "truckFactoryAddress", "truckConsigneeName",
        "truckConsigneeAddress", "truckAdvisingBankName", "truckAdvisingBankAddress", "truckConsigneeBankName",
        "truckConsigneeBankAddress", "truckBuyerName", "truckPackingText", "truckLcText", "truckApplicantsText",
        "truckContractText", "truckProformaText", "truckCarrierText", "truckFooterCompany",
        "originStatementText", "originApplicantsText", "originContractText", "originFooterCompany",
        "beneficiaryStatementOne", "beneficiaryStatementTwo", "beneficiaryFooterCompany",
        "forwardingDateText", "forwardingReferenceText", "forwardingManagerText", "forwardingBankNameText",
        "forwardingBankAddressText", "forwardingAmountText", "forwardingLcNoText", "forwardingLcDateText",
        "forwardingCustomerText", "forwardingBodyAmountText", "forwardingProformaText", "forwardingProformaDateText"
    ]);

    if (globalBuyer) {
        globalBuyer.value = "";
    }

    activeErpOrder = null;

    ["piNumber", "salesOrder", "buyerName", "customerPo", "approvalStatus", "factoryStatus"].forEach((id) => {
        const input = document.getElementById(id);
        if (input) {
            input.dispatchEvent(new Event("input"));
            input.dispatchEvent(new Event("change"));
        }
    });
}

function renderSalesItems(items) {
    if (!salesItemsBody) return;

    salesItemsBody.innerHTML = items.map((item) => `
        <tr>
            <td><input value="${item.sl}" readonly></td>
            <td><input value="${item.description}" readonly></td>
            <td><input value="${item.ply}" readonly></td>
            <td><input value="${item.qty}" readonly></td>
            <td><input value="${item.price}" readonly></td>
            <td><input value="${item.amount}" readonly></td>
        </tr>
    `).join("");

    updateSalesItemTotals();
}

function updateSalesItemTotals() {
    const items = getLineItemsData();
    const totalQty = items.reduce((sum, item) => sum + item.qty, 0);
    const totalAmount = items.reduce((sum, item) => sum + item.amount, 0);

    if (salesItemsTotalQty) {
        salesItemsTotalQty.textContent = `${totalQty}`;
    }

    if (salesItemsTotalAmount) {
        salesItemsTotalAmount.textContent = totalAmount.toFixed(2);
    }
}

function syncPoOverviewFromSources() {
    const items = getLineItemsData();
    const totalQty = items.reduce((sum, item) => sum + item.qty, 0);
    const poNumber = document.getElementById("customerPo")?.value || "";
    const currentStage = document.getElementById("poStatusStage")?.value || "";

    const setText = (id, value) => {
        const el = document.getElementById(id);
        if (el) el.textContent = value || "";
    };

    const corrugationStatuses = ["Running", "Pending", "Done"];
    const deliveryStatuses = ["Waiting", "Ready", "Partial"];
    const statusRows = items.map((item, index) => {
        const producedQty = Math.max(0, Math.floor(item.qty * (0.28 + (((index + 2) * 17) % 35) / 100)));
        return {
            ...item,
            corrugation: corrugationStatuses[index % corrugationStatuses.length],
            delivery: deliveryStatuses[(index + 1) % deliveryStatuses.length],
            producedQty,
        };
    });
    const totalProducedQty = statusRows.reduce((sum, item) => sum + item.producedQty, 0);

    if (poStatusItemsBody) {
        poStatusItemsBody.innerHTML = statusRows.map((item) => `
            <tr>
                <td>${item.sl}</td>
                <td>${item.description}</td>
                <td>${item.ply}</td>
                <td>${item.qty}</td>
                <td>${item.corrugation}</td>
                <td>${item.delivery}</td>
                <td>${item.producedQty}</td>
            </tr>
        `).join("");
    }

    setText("poStatusPoText", poNumber);
    setText("poStatusStageText", currentStage);
    setText("poStatusProducedText", `${totalProducedQty}`);
    setText("poStatusTotalText", `${totalQty}`);

    const producedInput = document.getElementById("poStatusProducedQty");
    if (producedInput && document.activeElement !== producedInput) {
        producedInput.value = `${totalProducedQty}`;
    }
}

function syncPoStatusFromSources() {
    const setText = (id, value) => {
        const el = document.getElementById(id);
        if (el) el.textContent = value || "";
    };
    const order = activeErpOrder;
    const customerName = document.getElementById("customerName")?.value || "";
    const challanDate = formatDateDisplay(order?.challanDate || "");
    const sourceItems = order?.items?.length ? order.items : getLineItemsData();
    const rows = order?.challanRows?.length
        ? order.challanRows
        : sourceItems.map((item) => ({
              piNo: document.getElementById("piNumber")?.value || "",
              orderRef: document.getElementById("customerPo")?.value || "",
              description: item.description,
              deliveryDate: item.deliveryDate || "",
              qty: `${item.qty}`,
              challanNo: item.challan || "",
              inspectionResult: "",
          }));
    const totalQty = rows.reduce((sum, item) => sum + (parseFloat(item.qty || "0") || 0), 0);

    if (challanItemsBody) {
        challanItemsBody.innerHTML = rows.map((item, index) => `
            <tr>
                <td>${item.piNo || (index === 0 ? (document.getElementById("piNumber")?.value || "") : "")}</td>
                <td>${item.orderRef || ""}</td>
                <td>${item.description || ""}</td>
                <td>${formatDateDisplay(item.deliveryDate || "")}</td>
                <td>${item.qty || ""}</td>
                <td>${item.challanNo || ""}</td>
                <td>${item.inspectionResult || ""}</td>
            </tr>
        `).join("");
    }

    setText("challanCustomerText", customerName);
    setText("challanSheetDateText", challanDate);
    setText("challanQaCustomerLeft", customerName);
    setText("challanTotalQty", `${totalQty}`);
}

function syncCommercialFromSources() {
    const items = getLineItemsData();
    const totalQty = items.reduce((sum, item) => sum + item.qty, 0);
    const totalAmount = items.reduce((sum, item) => sum + item.amount, 0);
    const buyerName = document.getElementById("buyerName")?.value || "";
    const customerName = document.getElementById("customerName")?.value || "";
    const buyerAddress = document.getElementById("buyerAddress")?.value || "";
    const consigneeBank = document.getElementById("consigneeBank")?.value || "";
    const lcNo = document.getElementById("masterLcNo")?.value || "";
    const lcDate = formatDateDisplay(document.getElementById("masterLcDate")?.value || "");
    const invoiceDate = formatDateDisplay(document.getElementById("invoiceDate")?.value || "") || formatDateDisplay(document.getElementById("exchangeDate")?.value || "");
    const advisingBankName = document.getElementById("payToBankName")?.value || "";
    const advisingBankAddress = document.getElementById("payToBankAddress")?.value || "";
    const issuingBankName = consigneeBank.split(",")[0] || consigneeBank;
    const applicantBank = document.getElementById("applicantBank")?.value || "";
    const contractNo = document.getElementById("exportSalesContractNo")?.value || "";
    const contractDate = formatDateDisplay(document.getElementById("exportSalesContractDate")?.value || "");
    const applicantsText = `Export Sales Contract No. ${contractNo} Dated ${contractDate}, Applicants IRC No. ${document.getElementById("applicantIrc")?.value || ""}, Applicants TIN No. ${document.getElementById("applicantTin")?.value || ""}, Applicants Vat/bin No. ${document.getElementById("applicantVatBin")?.value || ""}, Applicants Bank Bin No. ${document.getElementById("applicantBankBin")?.value || ""}, Bond License no. ${document.getElementById("bondLicenseNo")?.value || ""}, Beneficiary's Vat/Bin: ${document.getElementById("beneficiaryVatBin")?.value || ""} and H.S Code No: ${document.getElementById("hsCodeMaster")?.value || ""}.`;

    const setText = (id, value) => {
        const el = document.getElementById(id);
        if (el) el.textContent = value || "";
    };

    if (commercialItemsBody) {
        commercialItemsBody.innerHTML = `
            <tr class="doc-buyer-row">
                <td colspan="5"><strong>BUYER:</strong> ${buyerName}</td>
            </tr>
        ` + items.map((item) => `
            <tr>
                <td>${item.sl}</td>
                <td>${item.description} - ${item.ply}</td>
                <td>${item.qty}</td>
                <td>${item.price.toFixed(4)}</td>
                <td>${item.amount.toFixed(2)}</td>
            </tr>
        `).join("");
    }

    if (commercialTotalQty) commercialTotalQty.textContent = `${totalQty}`;
    if (commercialTotalAmount) commercialTotalAmount.textContent = totalAmount.toFixed(2);

    setText("commercialBeneficiaryName", "Zaber & Zubair Accessories Ltd.");
    setText("commercialBeneficiaryAddress", "H/O: 115-120 Motijheel. C/A, Dhaka-1000 bangladesh.");
    setText("commercialFactoryAddress", "Factory: Mawna, Sreepur, Gazipur.");
    setText("commercialLcNoText", lcNo);
    setText("commercialLcDateText", lcDate);
    setText("commercialIssuingBankName", issuingBankName);
    setText("commercialIssuingBankAddress", applicantBank);
    setText("commercialAdvisingBankName", advisingBankName);
    setText("commercialAdvisingBankAddress", advisingBankAddress);
    setText("commercialConsigneeName", customerName);
    setText("commercialConsigneeAddress", buyerAddress);
    setText("commercialConsigneeBankName", issuingBankName);
    setText("commercialConsigneeBankAddress", consigneeBank);
    setText("commercialBuyerName", buyerName);
    setText("commercialApplicantsText", applicantsText);
}

function syncPackingFromSources() {
    const items = getLineItemsData();
    const totalQty = items.reduce((sum, item) => sum + item.qty, 0);
    const buyerName = document.getElementById("buyerName")?.value || "";

    if (packingItemsBody) {
        packingItemsBody.innerHTML = `
            <tr class="doc-buyer-row">
                <td colspan="4"><strong>BUYER:</strong> ${buyerName}</td>
            </tr>
        ` + items.map((item) => `
            <tr>
                <td>${item.sl}</td>
                <td>${item.description}</td>
                <td>${item.ply}</td>
                <td>${item.qty}</td>
            </tr>
        `).join("");
    }

    if (packingTotalQty) {
        packingTotalQty.textContent = `${totalQty}`;
    }

    const setText = (id, value) => {
        const el = document.getElementById(id);
        if (el) el.textContent = value || "";
    };

    const applicantName = document.getElementById("applicantName")?.value || "";
    const beneficiaryOfficeAddress = document.getElementById("beneficiaryOfficeAddress")?.value || "";
    const factoryAddress = document.getElementById("factoryAddress")?.value || "";
    const customerName = document.getElementById("customerName")?.value || "";
    const buyerAddress = document.getElementById("buyerAddress")?.value || "";
    const payToBankName = document.getElementById("payToBankName")?.value || "";
    const advisingBankAddress = document.getElementById("advisingBankAddress")?.value || "";
    const consigneeBank = document.getElementById("consigneeBank")?.value || "";
    const packingDetails = document.getElementById("packingDetailsMaster")?.value || "";
    const lcNo = document.getElementById("masterLcNo")?.value || "";
    const lcDate = formatDateDisplay(document.getElementById("masterLcDate")?.value || "");
    const contractNo = document.getElementById("exportSalesContractNo")?.value || "";
    const contractDate = formatDateDisplay(document.getElementById("exportSalesContractDate")?.value || "");
    const invoiceNo = document.getElementById("invoiceNo")?.value || "";
    const proformaDate = formatDateDisplay(document.getElementById("proformaDate")?.value || "");
    const carrier = document.getElementById("carrierNameMaster")?.value || "By Truck";
    const applicantsLine = `Applicants IRC No. ${document.getElementById("applicantIrc")?.value || ""}, Applicants TIN No. ${document.getElementById("applicantTin")?.value || ""}, Applicants Vat/bin No. ${document.getElementById("applicantVatBin")?.value || ""}, Applicants Bank Bin No. ${document.getElementById("applicantBankBin")?.value || ""}, Bond License No. ${document.getElementById("bondLicenseNo")?.value || ""}, Beneficiary's Vat/Bin: ${document.getElementById("beneficiaryVatBin")?.value || ""} and H.S Code No: ${document.getElementById("hsCodeMaster")?.value || ""}.`;

    setText("packingBeneficiaryName", applicantName);
    setText("packingBeneficiaryAddress", beneficiaryOfficeAddress);
    setText("packingFactoryAddress", `Factory: ${factoryAddress}`);
    setText("packingConsigneeName", customerName);
    setText("packingConsigneeAddress", buyerAddress);
    setText("packingAdvisingBankName", payToBankName);
    setText("packingAdvisingBankAddress", advisingBankAddress);
    setText("packingConsigneeBankName", consigneeBank.split(",")[0] || consigneeBank);
    setText("packingConsigneeBankAddress", consigneeBank);
    setText("packingDetailsText", packingDetails);
    setText("packingLcText", `${lcNo} Dated ${lcDate}`);
    setText("packingApplicantsText", applicantsLine);
    setText("packingContractText", `${contractNo} Dated ${contractDate}`);
    setText("packingProformaText", `${invoiceNo} Dated ${proformaDate}`);
    setText("packingCarrierText", carrier);
    setText("packingFooterCompany", applicantName);
}

function syncDeliveryFromSources() {
    const items = getLineItemsData();
    const totalQty = items.reduce((sum, item) => sum + item.qty, 0);
    const buyerName = document.getElementById("buyerName")?.value || "";

    if (deliveryItemsBody) {
        deliveryItemsBody.innerHTML = `
            <tr class="doc-buyer-row">
                <td colspan="4"><strong>BUYER:</strong> ${buyerName}</td>
            </tr>
        ` + items.map((item) => `
            <tr>
                <td>${item.sl}</td>
                <td>${item.description}</td>
                <td>${item.ply}</td>
                <td>${item.qty}</td>
            </tr>
        `).join("");
    }

    if (deliveryTotalQty) {
        deliveryTotalQty.textContent = `${totalQty}`;
    }

    const setText = (id, value) => {
        const el = document.getElementById(id);
        if (el) el.textContent = value || "";
    };

    const applicantName = document.getElementById("applicantName")?.value || "";
    const beneficiaryOfficeAddress = document.getElementById("beneficiaryOfficeAddress")?.value || "";
    const factoryAddress = document.getElementById("factoryAddress")?.value || "";
    const customerName = document.getElementById("customerName")?.value || "";
    const buyerAddress = document.getElementById("buyerAddress")?.value || "";
    const payToBankName = document.getElementById("payToBankName")?.value || "";
    const advisingBankAddress = document.getElementById("advisingBankAddress")?.value || "";
    const consigneeBank = document.getElementById("consigneeBank")?.value || "";
    const packingDetails = document.getElementById("packingDetailsMaster")?.value || "";
    const lcNo = document.getElementById("masterLcNo")?.value || "";
    const lcDate = formatDateDisplay(document.getElementById("masterLcDate")?.value || "");
    const contractNo = document.getElementById("exportSalesContractNo")?.value || "";
    const contractDate = formatDateDisplay(document.getElementById("exportSalesContractDate")?.value || "");
    const invoiceNo = document.getElementById("invoiceNo")?.value || "";
    const invoiceDate = formatDateDisplay(document.getElementById("exchangeDate")?.value || "");
    const proformaDate = formatDateDisplay(document.getElementById("proformaDate")?.value || "");
    const carrier = document.getElementById("carrierNameMaster")?.value || "By Truck";
    const applicantsLine = `Applicants IRC No. ${document.getElementById("applicantIrc")?.value || ""}, Applicants TIN No. ${document.getElementById("applicantTin")?.value || ""}, Applicants Vat/bin No. ${document.getElementById("applicantVatBin")?.value || ""}, Applicants Bank Bin No. ${document.getElementById("applicantBankBin")?.value || ""}, Bond License No. ${document.getElementById("bondLicenseNo")?.value || ""}, Beneficiary's Vat/Bin: ${document.getElementById("beneficiaryVatBin")?.value || ""} and H.S Code No: ${document.getElementById("hsCodeMaster")?.value || ""}.`;

    setText("deliveryInvoiceNo", invoiceNo);
    setText("deliveryDateText", invoiceDate);
    setText("deliveryBeneficiaryName", applicantName);
    setText("deliveryBeneficiaryAddress", beneficiaryOfficeAddress);
    setText("deliveryFactoryAddress", `Factory: ${factoryAddress}`);
    setText("deliveryConsigneeName", customerName);
    setText("deliveryConsigneeAddress", buyerAddress);
    setText("deliveryAdvisingBankName", payToBankName);
    setText("deliveryAdvisingBankAddress", advisingBankAddress);
    setText("deliveryConsigneeBankName", consigneeBank.split(",")[0] || consigneeBank);
    setText("deliveryConsigneeBankAddress", consigneeBank);
    setText("deliveryBuyerName", buyerName);
    setText("deliveryPackingText", packingDetails);
    setText("deliveryLcText", `${lcNo} Dated ${lcDate}`);
    setText("deliveryApplicantsText", applicantsLine);
    setText("deliveryContractText", `${contractNo} Dated ${contractDate}`);
    setText("deliveryProformaText", `${invoiceNo} Dated ${proformaDate}`);
    setText("deliveryCarrierText", carrier);
    setText("deliveryFooterCompany", applicantName);
}

function syncTruckFromSources() {
    const items = getLineItemsData();
    const totalQty = items.reduce((sum, item) => sum + item.qty, 0);
    const buyerName = document.getElementById("buyerName")?.value || "";

    if (truckItemsBody) {
        truckItemsBody.innerHTML = `
            <tr class="doc-buyer-row">
                <td colspan="4"><strong>BUYER:</strong> ${buyerName}</td>
            </tr>
        ` + items.map((item) => `
            <tr>
                <td>${item.sl}</td>
                <td>${item.description}</td>
                <td>${item.ply}</td>
                <td>${item.qty}</td>
            </tr>
        `).join("");
    }

    if (truckTotalQty) {
        truckTotalQty.textContent = `${totalQty}`;
    }

    const setText = (id, value) => {
        const el = document.getElementById(id);
        if (el) el.textContent = value || "";
    };

    const applicantName = document.getElementById("applicantName")?.value || "";
    const beneficiaryOfficeAddress = document.getElementById("beneficiaryOfficeAddress")?.value || "";
    const factoryAddress = document.getElementById("factoryAddress")?.value || "";
    const customerName = document.getElementById("customerName")?.value || "";
    const buyerAddress = document.getElementById("buyerAddress")?.value || "";
    const payToBankName = document.getElementById("payToBankName")?.value || "";
    const advisingBankAddress = document.getElementById("advisingBankAddress")?.value || "";
    const consigneeBank = document.getElementById("consigneeBank")?.value || "";
    const packingDetails = document.getElementById("packingDetailsMaster")?.value || "";
    const lcNo = document.getElementById("masterLcNo")?.value || "";
    const lcDate = formatDateDisplay(document.getElementById("masterLcDate")?.value || "");
    const contractNo = document.getElementById("exportSalesContractNo")?.value || "";
    const contractDate = formatDateDisplay(document.getElementById("exportSalesContractDate")?.value || "");
    const invoiceNo = document.getElementById("invoiceNo")?.value || "";
    const proformaDate = formatDateDisplay(document.getElementById("proformaDate")?.value || "");
    const carrier = document.getElementById("carrierNameMaster")?.value || "By Truck";
    const applicantsLine = `Applicants IRC No. ${document.getElementById("applicantIrc")?.value || ""}, Applicants TIN No. ${document.getElementById("applicantTin")?.value || ""}, Applicants Vat/bin No. ${document.getElementById("applicantVatBin")?.value || ""}, Applicants Bank Bin No. ${document.getElementById("applicantBankBin")?.value || ""}, Bond License No. ${document.getElementById("bondLicenseNo")?.value || ""}, Beneficiary's Vat/Bin: ${document.getElementById("beneficiaryVatBin")?.value || ""} and H.S Code No: ${document.getElementById("hsCodeMaster")?.value || ""}.`;

    setText("truckBeneficiaryName", applicantName);
    setText("truckBeneficiaryAddress", beneficiaryOfficeAddress);
    setText("truckFactoryAddress", `Factory: ${factoryAddress}`);
    setText("truckConsigneeName", customerName);
    setText("truckConsigneeAddress", buyerAddress);
    setText("truckAdvisingBankName", payToBankName);
    setText("truckAdvisingBankAddress", advisingBankAddress);
    setText("truckConsigneeBankName", consigneeBank.split(",")[0] || consigneeBank);
    setText("truckConsigneeBankAddress", consigneeBank);
    setText("truckBuyerName", buyerName);
    setText("truckPackingText", packingDetails);
    setText("truckLcText", `${lcNo} Dated ${lcDate}`);
    setText("truckApplicantsText", applicantsLine);
    setText("truckContractText", `${contractNo} Dated ${contractDate}`);
    setText("truckProformaText", `${invoiceNo} Dated ${proformaDate}`);
    setText("truckCarrierText", carrier);
    setText("truckFooterCompany", applicantName);
}

function syncOriginFromSources() {
    const setText = (id, value) => {
        const el = document.getElementById(id);
        if (el) el.textContent = value || "";
    };

    const applicantName = document.getElementById("applicantName")?.value || "";
    const lcNo = document.getElementById("masterLcNo")?.value || "";
    const lcDate = formatDateDisplay(document.getElementById("masterLcDate")?.value || "");
    const invoiceNo = document.getElementById("invoiceNo")?.value || "";
    const proformaDate = formatDateDisplay(document.getElementById("proformaDate")?.value || "");
    const contractNo = document.getElementById("exportSalesContractNo")?.value || "";
    const contractDate = formatDateDisplay(document.getElementById("exportSalesContractDate")?.value || "");
    const applicantsLine = `Applicants IRC No. ${document.getElementById("applicantIrc")?.value || ""}, Applicants TIN No. ${document.getElementById("applicantTin")?.value || ""}, Applicants Vat/bin No. ${document.getElementById("applicantVatBin")?.value || ""}, Applicants Bank Bin No. ${document.getElementById("applicantBankBin")?.value || ""}, Bond License no. ${document.getElementById("bondLicenseNo")?.value || ""}, Beneficiary's Vat/Bin: ${document.getElementById("beneficiaryVatBin")?.value || ""} and H.S Code No: ${document.getElementById("hsCodeMaster")?.value || ""}.`;

    setText(
        "originStatementText",
        `This is to certify that the goods, which are delivered under L/C No. ${lcNo} Dated ${lcDate} as per Proforma Invoice No. ${invoiceNo} Dated ${proformaDate} is of Bangladesh Origin.`
    );
    setText("originApplicantsText", applicantsLine);
    setText("originContractText", `Sales Contract No : ${contractNo} Dated ${contractDate}.`);
    setText("originFooterCompany", applicantName);
}

function syncBeneficiaryFromSources() {
    const setText = (id, value) => {
        const el = document.getElementById(id);
        if (el) el.textContent = value || "";
    };

    const items = getLineItemsData();
    const totalQty = items.reduce((sum, item) => sum + item.qty, 0);
    const totalAmount = items.reduce((sum, item) => sum + item.amount, 0);
    const applicantName = document.getElementById("applicantName")?.value || "";
    const customerName = document.getElementById("customerName")?.value || "";
    const buyerAddress = document.getElementById("buyerAddress")?.value || "";
    const consigneeBank = document.getElementById("consigneeBank")?.value || "";
    const lcNo = document.getElementById("masterLcNo")?.value || "";
    const lcDate = formatDateDisplay(document.getElementById("masterLcDate")?.value || "");
    const invoiceNo = document.getElementById("invoiceNo")?.value || "";
    const proformaDate = formatDateDisplay(document.getElementById("proformaDate")?.value || "");
    const contractNo = document.getElementById("exportSalesContractNo")?.value || "";
    const contractDate = formatDateDisplay(document.getElementById("exportSalesContractDate")?.value || "");
    const applicantIrc = document.getElementById("applicantIrc")?.value || "";
    const applicantTin = document.getElementById("applicantTin")?.value || "";
    const applicantVat = document.getElementById("applicantVatBin")?.value || "";
    const applicantBankBin = document.getElementById("applicantBankBin")?.value || "";
    const bondLicense = document.getElementById("bondLicenseNo")?.value || "";
    const beneficiaryVat = document.getElementById("beneficiaryVatBin")?.value || "";
    const hsCode = document.getElementById("hsCodeMaster")?.value || "";

    const amountText = totalAmount ? totalAmount.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : "";
    const qtyText = totalQty ? totalQty.toLocaleString("en-US") : "";

    setText(
        "beneficiaryStatementOne",
        `We hereby confirm that we have supplied Accessories for 100% export oriented garments industry: ${qtyText} cones / pcs total amount of US $ ${amountText} all other details as per pro-forma invoice No. ${invoiceNo} Dated ${proformaDate}. To The ${customerName}, ${buyerAddress}. Against their ${consigneeBank} L/C No. ${lcNo} Dated ${lcDate} under Applicants IRC No. ${applicantIrc}, Applicants TIN No. ${applicantTin}, Applicants Vat/bin No. ${applicantVat}, Applicants Bank Bin No. ${applicantBankBin}, Bond License no. ${bondLicense}, Beneficiary's Vat/Bin: ${beneficiaryVat} and H.S Code No: ${hsCode}. Sales Contract No: ${contractNo} Dated ${contractDate} for exporting readymade garments.`
    );

    setText(
        "beneficiaryStatementTwo",
        `We do hereby undertake that the said accessories shipment from : Beneficiary's factory to applicant factory warehouse. We also certified that quantity, quality, rate specification & all other terms & conditions are as per suppliers pro-forma invoice No. ${invoiceNo} Dated ${proformaDate} any short and defective goods to be replaced by us on free of cost.`
    );

    setText("beneficiaryFooterCompany", applicantName);
}

function syncForwardingFromSources() {
    const setText = (id, value) => {
        const el = document.getElementById(id);
        if (el) el.textContent = value || "";
    };

    const totalAmount = getLineItemsData().reduce((sum, item) => sum + item.amount, 0);
    const amountText = totalAmount ? totalAmount.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : "";
    const referenceNo = document.getElementById("invoiceNo")?.value || "";
    const lcNo = document.getElementById("masterLcNo")?.value || "";
    const lcDate = formatDateDisplay(document.getElementById("masterLcDate")?.value || "");
    const customerName = document.getElementById("customerName")?.value || "";
    const bankName = document.getElementById("payToBankName")?.value || "";
    const bankAddress = document.getElementById("payToBankAddress")?.value || "";
    const letterDate = formatDateDisplay(document.getElementById("exchangeDate")?.value || "");
    const proformaNo = document.getElementById("proformaNo")?.value || referenceNo;
    const proformaDate = formatDateDisplay(document.getElementById("proformaDate")?.value || "");

    setText("forwardingDateText", letterDate);
    setText("forwardingReferenceText", referenceNo);
    setText("forwardingManagerText", "The Manager");
    setText("forwardingBankNameText", bankName);
    setText("forwardingBankAddressText", bankAddress);
    setText("forwardingAmountText", amountText);
    setText("forwardingLcNoText", lcNo);
    setText("forwardingLcDateText", lcDate);
    setText("forwardingCustomerText", customerName);
    setText("forwardingBodyAmountText", amountText);
    setText("forwardingProformaText", proformaNo);
    setText("forwardingProformaDateText", proformaDate);
}

function addItemRow() {
    if (!salesItemsBody) return;

    const rowCount = salesItemsBody.querySelectorAll("tr").length + 1;
    const row = document.createElement("tr");
    row.innerHTML = `
        <td><input value="${rowCount}"></td>
        <td><input value=""></td>
        <td><input value=""></td>
        <td><input value="0"></td>
        <td><input value="0.0000"></td>
        <td><input value="0.00"></td>
    `;
    salesItemsBody.appendChild(row);
    attachSalesItemListeners();
    updateSalesItemTotals();
    syncPackingFromSources();
    syncDeliveryFromSources();
    syncTruckFromSources();
    syncOriginFromSources();
    syncBeneficiaryFromSources();
    syncExchangeFromSources();
}

function attachSalesItemListeners() {
    if (!salesItemsBody) return;

    salesItemsBody.querySelectorAll("input").forEach((input) => {
        if (input.dataset.bound === "1") return;
        input.dataset.bound = "1";
        input.addEventListener("input", () => {
            updateSalesItemTotals();
            syncPoOverviewFromSources();
            syncPackingFromSources();
            syncDeliveryFromSources();
            syncTruckFromSources();
            syncOriginFromSources();
            syncBeneficiaryFromSources();
            syncForwardingFromSources();
            syncExchangeFromSources();
        });
        input.addEventListener("change", () => {
            updateSalesItemTotals();
            syncPoOverviewFromSources();
            syncPackingFromSources();
            syncDeliveryFromSources();
            syncTruckFromSources();
            syncOriginFromSources();
            syncBeneficiaryFromSources();
            syncForwardingFromSources();
            syncExchangeFromSources();
        });
    });
}

function applyErpOrder(order) {
    if (salesFields.piNumber) {
        salesFields.piNumber.value = order.piNumber || "";
    }
    salesFields.salesOrder.value = order.salesOrder;
    salesFields.buyerName.value = order.buyerName;
    salesFields.customerName.value = order.customerName;
    salesFields.customerPo.value = order.customerPo;
    salesFields.productLine.value = order.productLine;
    salesFields.requestedDateErp.value = order.requestedDateErp;
    salesFields.orderStatus.value = order.orderStatus;
    salesFields.buyerAddress.value = order.buyerAddress;
    salesFields.consigneeBank.value = order.consigneeBank;
    erpResultText.textContent = `ERP matched customer PO ${order.customerPo || "-"} and loaded the same order result, buyer, consignee bank, and ${order.items.length} item row(s).`;
    renderSalesItems(order.items);
    syncPoOverviewFromSources();
    syncCommercialFromSources();
    syncPackingFromSources();
    syncDeliveryFromSources();
    syncTruckFromSources();
    syncOriginFromSources();
    syncBeneficiaryFromSources();
    syncForwardingFromSources();

    ["piNumber", "salesOrder", "buyerName", "customerPo"].forEach((id) => {
        const input = document.getElementById(id);
        if (!input) return;
        input.dispatchEvent(new Event("input"));
    });
}

const exchangeFieldIds = [
    "masterLcNo",
    "masterLcDate",
    "applicantBank",
    "exchangeAmount",
    "exchangeDate",
    "lcTenorMaster",
    "payToBankName",
    "payToBankAddress",
    "tenorWordsMaster",
    "exportSalesContractNo",
    "exportSalesContractDate",
    "applicantIrc",
    "applicantTin",
    "applicantVatBin",
    "applicantBankBin",
    "bondLicenseNo",
    "beneficiaryVatBin",
    "hsCodeMaster",
    "invoiceNo",
    "invoiceDate",
    "proformaNo",
    "proformaDate",
];

function syncExchangeFromSources() {
    const totalAmount = getLineItemsData().reduce((sum, item) => sum + item.amount, 0);

    const source = {
        masterLcNo: document.getElementById("masterLcNo")?.value || "",
        masterLcDate: document.getElementById("masterLcDate")?.value || "",
        applicantBank: document.getElementById("applicantBank")?.value || "",
        exchangeAmount: document.getElementById("exchangeAmount")?.value || totalAmount.toFixed(2),
        exchangeDate: document.getElementById("exchangeDate")?.value || "",
        lcTenorMaster: document.getElementById("lcTenorMaster")?.value || "",
        tenorWordsMaster: document.getElementById("tenorWordsMaster")?.value || "",
        payToBankName: document.getElementById("payToBankName")?.value || "",
        payToBankAddress: document.getElementById("payToBankAddress")?.value || "",
        exportSalesContractNo: document.getElementById("exportSalesContractNo")?.value || "",
        exportSalesContractDate: document.getElementById("exportSalesContractDate")?.value || "",
        applicantIrc: document.getElementById("applicantIrc")?.value || "",
        applicantTin: document.getElementById("applicantTin")?.value || "",
        applicantVatBin: document.getElementById("applicantVatBin")?.value || "",
        applicantBankBin: document.getElementById("applicantBankBin")?.value || "",
        bondLicenseNo: document.getElementById("bondLicenseNo")?.value || "",
        beneficiaryVatBin: document.getElementById("beneficiaryVatBin")?.value || "",
        hsCodeMaster: document.getElementById("hsCodeMaster")?.value || "",
    };

    const words = source.tenorWordsMaster || "amount in words not entered";
    const lcDateDisplay = formatDateDisplay(source.masterLcDate);
    const contractDateDisplay = formatDateDisplay(source.exportSalesContractDate);
    const preview = [
        `Drawn under Letter of Credit No. ${source.masterLcNo} Dated ${lcDateDisplay} of ${source.applicantBank}`,
        `Exchange for USD ${source.exchangeAmount}    Date: ${formatDateDisplay(source.exchangeDate)}`,
        `At ${source.lcTenorMaster} of this First of exchange (Second of the same tenor unpaid) please pay to the order of ${source.payToBankName}, ${source.payToBankAddress}. The same of USD: ${words}. Export Sales Contract No. ${source.exportSalesContractNo} Dated ${contractDateDisplay}, Applicants IRC No. ${source.applicantIrc}, Applicants TIN No. ${source.applicantTin}, Applicants Vat/bin No. ${source.applicantVatBin}, Applicants Bank Bin No. ${source.applicantBankBin}, Bond License no. ${source.bondLicenseNo}, Beneficiary's Vat/Bin: ${source.beneficiaryVatBin} and H.S Code No: ${source.hsCodeMaster}.`,
    ].join("\n\n");

    const previewBox = document.getElementById("exchangePreviewText");
    if (previewBox && document.activeElement !== previewBox) {
        previewBox.value = preview;
    }
}

function searchErpOrder() {
    // Read from page-level input first, fall back to legacy global search input
    const piInput = document.getElementById("piPoSearchInput");
    const rawQuery = piInput?.value || searchInput?.value || "";
    const query = rawQuery.trim().toLowerCase();
    if (!query) return;

    const found = erpOrders.find((order) =>
        order.customerPo.toLowerCase().includes(query) ||
        order.buyerName.toLowerCase().includes(query)
    );

    if (!found) {
        if (erpResultText) erpResultText.textContent = `No ERP match found for "${rawQuery.trim()}". Try customer PO or buyer code.`;
        setFeedback("ERP not found", `No test ERP data matched "${rawQuery.trim()}". Try customer PO or buyer name.`);
        return;
    }

    applyErpOrder(found);
    setFeedback("ERP order loaded", `Customer PO ${found.customerPo || "-"} loaded — buyer ${found.buyerName || "-"}, ${found.items.length} line item(s).`);
}

function searchChallanByPi() {
    const query = (challanPiSearch?.value || "").trim().toLowerCase();
    if (!query) return;

    const found = erpOrders.find((order) => (order.piNumber || "").toLowerCase().includes(query));

    if (!found) {
        setFeedback("PI not found", `No challan data matched PI ${challanPiSearch?.value || "-"}.`);
        return;
    }

    activeErpOrder = found;
    if (salesFields.piNumber) {
        salesFields.piNumber.value = found.piNumber || "";
    }
    if (challanPiSearch) {
        challanPiSearch.value = found.piNumber || "";
    }
    syncPoStatusFromSources();
    setFeedback("Challan loaded", `PI ${found.piNumber || "-"} loaded the challan sheet rows from ERP.`);
}

function loadDefaultChallanDemo() {
    // demo removed
}

if (searchButton) {
    searchButton.addEventListener("click", searchErpOrder);
}

if (searchInput) {
    searchInput.addEventListener("keydown", (event) => {
        if (event.key === "Enter") { event.preventDefault(); searchErpOrder(); }
    });
}

// PI page inline search bar
document.getElementById("piSearchBtn")?.addEventListener("click", searchErpOrder);
document.getElementById("piClearBtn")?.addEventListener("click", () => {
    const inp = document.getElementById("piPoSearchInput");
    if (inp) inp.value = "";
    if (erpResultText) erpResultText.textContent = "Enter a sample customer PO and click Search ERP to load order details and item rows.";
});
document.getElementById("piPoSearchInput")?.addEventListener("keydown", (e) => {
    if (e.key === "Enter") { e.preventDefault(); searchErpOrder(); }
});

if (challanPiButton) {
    challanPiButton.addEventListener("click", searchChallanByPi);
}

if (challanPiSearch) {
    challanPiSearch.addEventListener("keydown", (event) => {
        if (event.key === "Enter") {
            event.preventDefault();
            searchChallanByPi();
        }
    });
}

if (clearButton) {
    clearButton.addEventListener("click", () => {
        clearAllWorkflowData();
        if (searchInput) searchInput.value = '';
        if (challanPiSearch) challanPiSearch.value = '';
        if (erpResultText) erpResultText.textContent = "Enter a customer PO and click Search ERP to load order details.";
        setFeedback("Search cleared", "The form is empty again.");
    });
}


const workflowActions = {
    "save-sales": {
        title: "PO result kept",
        text: () => `PO ${salesFields.customerPo.value || "-"} result was kept as the base order for workflow review.`,
    },
    "save-intake": {
        title: "Marketing intake saved",
        text: () => `Marketing intake for PO ${document.getElementById("intakePoNumber")?.value || "-"} was saved.`,
    },
    "send-costing": {
        title: "Sent to costing",
        text: () => "Marketing sent the selected buyer, items, grades, and prices to Costing for review.",
        run: () => {
            saveIntakeState('costing-review');
            syncCostingFromIntake();
            if (costingStatusText) costingStatusText.textContent = "Waiting For Costing";
        },
        after: () => showPage("costing-review"),
    },
    "send-back-costing": {
        title: "Returned to marketing",
        text: () => "Costing revised the prices and sent them back to Marketing for another review loop.",
        run: () => {
            document.querySelectorAll(".costing-price-input").forEach((input) => {
                const index = Number(input.dataset.index || "0");
                if (intakeRows[index]) {
                    intakeRows[index].price = input.value;
                }
            });
            if (costingStatusText) costingStatusText.textContent = "Sent Back To Marketing";
            renderIntakeRows();
        },
        after: () => showPage("marketing-intake"),
    },
    "approve-costing": {
        title: "Costing approved",
        text: () => "Costing approved the submitted prices, and the order is ready to move into PI.",
        run: () => {
            document.querySelectorAll(".costing-price-input").forEach((input) => {
                const index = Number(input.dataset.index || "0");
                if (intakeRows[index]) {
                    intakeRows[index].price = input.value;
                }
            });
            if (costingStatusText) costingStatusText.textContent = "Approved For PI";
            syncPiFromIntake();
        },
        after: () => showPage("sales"),
    },
    "refresh-erp": {
        title: "ERP refresh complete",
        text: () => `PO ${salesFields.customerPo.value || "-"} was refreshed from the local ERP test result.`,
        run: searchErpOrder,
    },
    "generate-challan": {
        title: "Challan sheet prepared",
        text: () => `Challan sheet for PI ${salesFields.piNumber?.value || "-"} was rebuilt from the ERP delivery rows.`,
        run: syncPoStatusFromSources,
    },
    "submit-commercial": {
        title: "Sent to commercial LC",
        text: () => "Marketing approved the PI and sent it to Commercial for LC work.",
        after: () => showPage("lc"),
    },
    "save-pi": {
        title: "PI draft saved",
        text: () => `PI draft ${document.getElementById("invoiceNo")?.value || "-"} was saved for checking.`,
    },
    "submit-check": {
        title: "Submitted to marketing",
        text: () => "PI number was sent to Marketing for approval.",
        after: () => showPage("marketing"),
    },
    "validate-lc": {
        title: "LC validation passed",
        text: () => `LC ${document.getElementById("masterLcNo")?.value || "-"} was reviewed before Bill of Exchange preparation.`,
    },
    "mark-checked": {
        title: "LC marked checked",
        text: () => "Commercial compliance check was marked as completed.",
    },
    "generate-bill": {
        title: "Bill of Exchange prepared",
        text: () => "Bill of Exchange preview was built from the PO result, PI, and Bill of Exchange input details.",
        run: syncExchangeFromSources,
    },
    "save-bill": {
        title: "Bill draft saved",
        text: () => "Bill of Exchange draft was saved for final review.",
    },
    "add-item": {
        title: "New line added",
        text: () => "A blank line item row was added for test entry.",
        run: addItemRow,
    },
    "recalculate-total": {
        title: "Line values refreshed",
        text: () => "Line item totals were recalculated and shared document values were refreshed.",
        run: () => {
            updateSalesItemTotals();
            syncPoOverviewFromSources();
            syncPoStatusFromSources();
            syncCommercialFromSources();
            syncPackingFromSources();
            syncExchangeFromSources();
        },
    },
    "generate-packing": {
        title: "Packing list prepared",
        text: () => "Packing list page was rebuilt from the PO result, Bill of Exchange inputs, PI, and LC data.",
        run: syncPackingFromSources,
    },
    "confirm-packing": {
        title: "Packing confirmed",
        text: () => "Packing details were checked and are ready for the next export-document steps.",
    },
    "generate-delivery": {
        title: "Delivery challan prepared",
        text: () => "Delivery challan page was rebuilt from the PO result, packing, Bill of Exchange inputs, and PI data.",
        run: syncDeliveryFromSources,
    },
    "confirm-delivery": {
        title: "Delivery confirmed",
        text: () => "Delivery challan details were checked and are ready for the next step.",
    },
    "generate-truck": {
        title: "Truck challan prepared",
        text: () => "Truck challan page was rebuilt from the PO result, packing, delivery, Bill of Exchange inputs, and PI data.",
        run: syncTruckFromSources,
    },
    "confirm-truck": {
        title: "Truck challan confirmed",
        text: () => "Truck challan details were checked and are ready for the next step.",
    },
    "generate-origin": {
        title: "Certificate prepared",
        text: () => "Certificate of Origin was rebuilt from LC, contract, proforma invoice, applicant, and HS code data.",
        run: syncOriginFromSources,
    },
    "confirm-origin": {
        title: "Certificate confirmed",
        text: () => "Certificate of Origin details were checked and are ready for the next step.",
    },
    "generate-beneficiary": {
        title: "Beneficiary certificate prepared",
        text: () => "Beneficiary's Certificate was rebuilt from order totals, invoice, LC, applicant, consignee, contract, and HS code data.",
        run: syncBeneficiaryFromSources,
    },
    "confirm-beneficiary": {
        title: "Beneficiary certificate confirmed",
        text: () => "Beneficiary's Certificate details were checked and are ready for the next step.",
    },
    "generate-forwarding": {
        title: "Forwarding prepared",
        text: () => "Forwarding letter was rebuilt from PI, LC, amount, and bank details.",
        run: syncForwardingFromSources,
    },
    "confirm-forwarding": {
        title: "Forwarding confirmed",
        text: () => "Forwarding document details were checked and are ready before factory release.",
    },
    "send-factory": {
        title: "Sent to factory",
        text: () => "Approved commercial package was passed to factory for production planning.",
    },
    "hold-order": {
        title: "Order placed on hold",
        text: () => "This test workflow is now marked as hold pending correction or review.",
    },
    "approve-order": {
        title: "Order approved",
        text: () => "Marketing and commercial checks are done. The order is approved for factory release.",
        run: () => {
            const approval = document.getElementById("approvalStatus");
            if (approval) {
                approval.value = "Approved";
                approval.dispatchEvent(new Event("change"));
            }
        },
    },
    "back-edit": {
        title: "Returned to edit mode",
        text: () => "Use the earlier pages to change order, PI, Bill of Exchange inputs, LC, or document details.",
        after: () => showPage("sales"),
    },
    "submit-final": {
        title: "Final submission complete",
        text: () => "The whole ED workflow pack was submitted as a final static test run.",
    },
    "generate-docs": {
        title: "Document pack generated",
        text: () => "Commercial Invoice, Packing List, Delivery Challan, Truck Challan, Bill of Exchange, and export forms are marked ready.",
    },
};

// Map of workflow actions to the page they save data for
const ACTION_PAGE_MAP = {
    'send-costing':       'marketing-intake',
    'send-back-costing':  'costing-review',
    'approve-costing':    'costing-review',
    'save-pi':            'sales',
    'submit-check':       'sales',
    'submit-commercial':  'marketing',
    'validate-lc':        'lc',
    'mark-checked':       'lc',
    'generate-bill':      'exchange',
    'save-bill':          'exchange',
    'confirm-packing':    'packing',
    'confirm-delivery':   'delivery',
    'confirm-truck':      'truck',
    'confirm-origin':     'origin',
    'confirm-beneficiary':'beneficiary',
    'confirm-forwarding': 'forwarding',
    'save-intake':        'marketing-intake',
};

document.querySelectorAll(".workflow-btn").forEach((button) => {
    button.addEventListener("click", () => {
        const config = workflowActions[button.dataset.action];
        if (!config) return;

        if (typeof config.run === "function") {
            config.run();
        }

        button.classList.add("is-done");
        setFeedback(config.title, typeof config.text === "function" ? config.text() : config.text);

        if (typeof config.after === "function") {
            config.after();
        }

        // Persist page data to DB for applicable actions
        const pageName = ACTION_PAGE_MAP[button.dataset.action];
        if (pageName) {
            savePageData(pageName, collectPageData(pageName));
        }
    });
});

document.getElementById("addIntakeItem")?.addEventListener("click", () => {
    intakeRows.push(createIntakeRow());
    renderIntakeRows();
});

document.getElementById("openBuyerModal")?.addEventListener("click", openBuyerCreateModal);
document.getElementById("openBuyerMasterModal")?.addEventListener("click", openBuyerCreateModal);
document.getElementById("closeBuyerModal")?.addEventListener("click", closeBuyerCreateModal);
document.getElementById("saveBuyerModal")?.addEventListener("click", saveBuyerFromModal);

document.getElementById("openItemModal")?.addEventListener("click", openItemCreateModal);
document.getElementById("closeItemModal")?.addEventListener("click", closeItemCreateModal);
document.getElementById("saveItemModal")?.addEventListener("click", saveItemFromModal);
newItemProductLine?.addEventListener("change", () => {
    populateItemNameList(newItemProductLine.value);
    populateGrades();
});
newItemComb?.addEventListener("change", () => populateCombinations());

exchangeFieldIds.forEach((id) => {
    const el = document.getElementById(id);
    if (!el) return;
    el.addEventListener("input", () => {
        syncCommercialFromSources();
        syncPackingFromSources();
        syncDeliveryFromSources();
        syncTruckFromSources();
        syncOriginFromSources();
        syncBeneficiaryFromSources();
        syncForwardingFromSources();
        syncExchangeFromSources();
    });
    el.addEventListener("change", () => {
        syncCommercialFromSources();
        syncPackingFromSources();
        syncDeliveryFromSources();
        syncTruckFromSources();
        syncOriginFromSources();
        syncBeneficiaryFromSources();
        syncForwardingFromSources();
        syncExchangeFromSources();
    });
});

["piNumber", "customerPo", "customerName", "poStatusStage", "poStatusProducedQty"].forEach((id) => {
    const el = document.getElementById(id);
    if (!el) return;
    el.addEventListener("input", () => {
        syncPoOverviewFromSources();
        syncPoStatusFromSources();
    });
    el.addEventListener("change", () => {
        syncPoOverviewFromSources();
        syncPoStatusFromSources();
    });
});

const exchangeDate = document.getElementById("exchangeDate");
if (exchangeDate) {
    exchangeDate.addEventListener("input", () => {
        syncCommercialFromSources();
        syncForwardingFromSources();
        syncExchangeFromSources();
    });
    exchangeDate.addEventListener("change", () => {
        syncCommercialFromSources();
        syncForwardingFromSources();
        syncExchangeFromSources();
    });
}

clearAllWorkflowData();
const intakePoNumber = document.getElementById("intakePoNumber");
const intakeMarketingNotes = document.getElementById("intakeMarketingNotes");
const costingNotes = document.getElementById("costingNotes");
populateIntakeCustomer();
renderBuyerMaster();
renderItemMaster();
renderPartyMaster();
renderDashboard();

// Restore last intake from localStorage (works on any page)
const _savedIntake = loadIntakeState();
if (_savedIntake) {
    restoreIntakeFromState(_savedIntake);
} else {
    renderBuyerSelect();
    renderIntakeRows();
}

syncCostingFromIntake();

// Customer → filter buyers + auto-fill salesperson
document.getElementById("intakeCustomer")?.addEventListener("change", (e) => {
    const customer = e.target.value;
    renderBuyerSelect(customer);
    autoFillSalesperson(customer);
});

// Dashboard buttons
document.getElementById("dashNewOrder")?.addEventListener("click", startNewOrder);
document.getElementById("dashNewOrderTop")?.addEventListener("click", startNewOrder);
if (erpResultText) {
    erpResultText.textContent = "Enter a customer PO and click Search ERP to load order details.";
}
attachSalesItemListeners();
loadDefaultChallanDemo();
syncPoOverviewFromSources();
syncPoStatusFromSources();
syncCommercialFromSources();
syncOriginFromSources();
syncBeneficiaryFromSources();
syncForwardingFromSources();

// ── Customer Profile Master ─────────────────────────────────────────────────
const CP_KEY = 'ed_customer_profiles';

const cpTypeBadge = (type) => {
    const map = { Regular:'regular', Premium:'premium', New:'new', Strategic:'strategic' };
    const cls = map[type] || 'regular';
    return `<span class="cp-type-badge cp-type-${cls}">${type}</span>`;
};

function cpLoadProfiles() {
    return JSON.parse(localStorage.getItem(CP_KEY) || '[]');
}
function cpSaveProfiles(list) {
    localStorage.setItem(CP_KEY, JSON.stringify(list));
}

function cpRenderList(filter) {
    const tbody = document.getElementById('customerProfileBody');
    if (!tbody) return;
    let list = cpLoadProfiles();
    if (filter) {
        const q = filter.toLowerCase();
        list = list.filter(c =>
            (c.company||'').toLowerCase().includes(q) ||
            (c.chairman||'').toLowerCase().includes(q) ||
            (c.mobile||'').toLowerCase().includes(q)
        );
    }
    if (list.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;color:#9ca3af;padding:20px;">No customers yet. Use the form below to add one.</td></tr>`;
        return;
    }
    tbody.innerHTML = list.map((c, i) => `
        <tr>
            <td>${i + 1}</td>
            <td><strong>${c.company || '—'}</strong></td>
            <td>${c.type ? cpTypeBadge(c.type) : '—'}</td>
            <td>${c.chairman || '—'}</td>
            <td>${c.mobile || '—'}</td>
            <td><span class="cp-stage-badge">${c.status === 'draft' ? 'Draft' : 'Completed ✓'}</span></td>
            <td>${c.submitted || '—'}</td>
            <td><button class="cp-action-btn" data-cp-view="${i}">View</button></td>
        </tr>`).join('');

    tbody.querySelectorAll('[data-cp-view]').forEach(btn => {
        btn.addEventListener('click', () => cpViewProfile(parseInt(btn.dataset.cpView)));
    });
}

function cpViewProfile(idx) {
    const list = cpLoadProfiles();
    const c = list[idx];
    if (!c) return;
    document.getElementById('cpCompanyName').value = c.company || '';
    document.getElementById('cpType').value       = c.type    || '';
    document.getElementById('cpChairman').value   = c.chairman|| '';
    document.getElementById('cpMobile').value     = c.mobile  || '';
    document.getElementById('cpEmail').value      = c.email   || '';
    document.getElementById('cpAddress').value    = c.address || '';
    document.getElementById('cpCreditLimit').value= c.credit  || '';
    document.getElementById('cpPaymentTerms').value=c.terms   || '';
    document.getElementById('cpBank').value       = c.bank    || '';
    document.getElementById('cpNotes').value      = c.notes   || '';
    document.getElementById('newCustomerFormWrap').scrollIntoView({ behavior: 'smooth' });
}

function cpCollectForm(status) {
    return {
        company:  document.getElementById('cpCompanyName').value.trim(),
        type:     document.getElementById('cpType').value,
        chairman: document.getElementById('cpChairman').value.trim(),
        mobile:   document.getElementById('cpMobile').value.trim(),
        email:    document.getElementById('cpEmail').value.trim(),
        address:  document.getElementById('cpAddress').value.trim(),
        credit:   document.getElementById('cpCreditLimit').value,
        terms:    document.getElementById('cpPaymentTerms').value.trim(),
        bank:     document.getElementById('cpBank').value.trim(),
        notes:    document.getElementById('cpNotes').value.trim(),
        status,
        submitted: new Date().toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' }),
    };
}

function cpClearForm() {
    ['cpCompanyName','cpType','cpChairman','cpMobile','cpEmail','cpAddress','cpCreditLimit','cpPaymentTerms','cpBank','cpNotes']
        .forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
}

// Run setup — script is at end of body so DOM is ready
cpRenderList();

const cpSearchBtn   = document.getElementById('cpSearchBtn');
const cpSearchInput = document.getElementById('cpSearchInput');
const cpCloseFormBtn= document.getElementById('cpCloseFormBtn');
const cpCancelBtn   = document.getElementById('cpCancelBtn');
const cpSaveDraftBtn= document.getElementById('cpSaveDraftBtn');
const cpSubmitBtn   = document.getElementById('cpSubmitBtn');
const cpFormWrap    = document.getElementById('newCustomerFormWrap');

if (cpSearchBtn) cpSearchBtn.addEventListener('click', () => cpRenderList(cpSearchInput.value));
if (cpSearchInput) cpSearchInput.addEventListener('keydown', e => { if (e.key === 'Enter') cpRenderList(cpSearchInput.value); });

if (cpCloseFormBtn) cpCloseFormBtn.addEventListener('click', () => {
    if (cpFormWrap) cpFormWrap.style.display = cpFormWrap.style.display === 'none' ? '' : 'none';
});
if (cpCancelBtn) cpCancelBtn.addEventListener('click', () => {
    cpClearForm();
    if (cpFormWrap) cpFormWrap.style.display = 'none';
});
if (cpSaveDraftBtn) cpSaveDraftBtn.addEventListener('click', () => {
    const d = cpCollectForm('draft');
    if (!d.company) { alert('Company name is required.'); return; }
    const list = cpLoadProfiles();
    list.push(d);
    cpSaveProfiles(list);
    cpRenderList();
    cpClearForm();
});
if (cpSubmitBtn) cpSubmitBtn.addEventListener('click', () => {
    const d = cpCollectForm('completed');
    if (!d.company) { alert('Company name is required.'); return; }
    const list = cpLoadProfiles();
    list.push(d);
    cpSaveProfiles(list);
    cpRenderList();
    cpClearForm();
    if (cpFormWrap) cpFormWrap.style.display = 'none';
});
setFeedback("Workflow ready", "Start with Marketing Intake, send the selected buyer and item pricing to Costing, and move to PI only after Costing approval.");

// common.js — shared master data loaded from / saved to localStorage

const ED_BUYERS_KEY = 'ed_buyerMaster';
const ED_ITEMS_KEY  = 'ed_itemMaster';

const defaultBuyerMaster = [
    { customer: "LIZ Fashion Industry Ltd", code: "LMR", name: "LIZ Fashion Industry Ltd", address: "Purba Chandra, Shafipur, Kaliakoir, Gazipur." },
    { customer: "Zaber & Zubair Fabrics Ltd.", code: "IKEA-2627", name: "Zaber & Zubair Fabrics Ltd. - Home", address: "Buyer address to be confirmed from ERP customer master." },
];

const defaultItemMaster = [
    { productLine: "Carton", itemName: "Carton - 62x54x31 CM",  grade: "Grade 1",  paperCombination: "L190+SCF160+L190+SCF160+L190",  price: 1.5776 },
    { productLine: "Carton", itemName: "Carton - 23x10.5x14",   grade: "Grade 1",  paperCombination: "L190+SCF160+L160+SCF160+L190",  price: 0.5733 },
    { productLine: "Carton", itemName: "Top Bottom - 22x9.5",   grade: "Grade 11", paperCombination: "L190+SCF160+L190",               price: 0.0449 },
    { productLine: "Drawstring", itemName: "100% Polyester Filament 3 MM Round Drawstring Raw Off White", grade: "N/A", paperCombination: "N/A", price: 0.0554 },
];

function loadBuyerMaster() {
    try { return JSON.parse(localStorage.getItem(ED_BUYERS_KEY)) || defaultBuyerMaster; }
    catch { return defaultBuyerMaster; }
}

function loadItemMaster() {
    try { return JSON.parse(localStorage.getItem(ED_ITEMS_KEY)) || defaultItemMaster; }
    catch { return defaultItemMaster; }
}

function saveBuyerMaster(data) {
    localStorage.setItem(ED_BUYERS_KEY, JSON.stringify(data));
}

function saveItemMaster(data) {
    localStorage.setItem(ED_ITEMS_KEY, JSON.stringify(data));
}

// Exposed globals used by script.js
window.buyerMaster = loadBuyerMaster();
window.itemMaster  = loadItemMaster();

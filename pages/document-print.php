<?php
$doc = preg_replace('/[^a-z\-]/', '', $_GET['doc'] ?? 'packing');
$validDocs = ['sales-contract','packing','delivery','truck','origin','beneficiary','forwarding','bank-forwarding'];
if (!in_array($doc, $validDocs, true)) $doc = 'packing';
$titles = [
    'sales-contract' => 'Sales Contract Print',
    'packing' => 'Packing List Print',
    'delivery' => 'Delivery Challan Print',
    'truck' => 'Truck Challan Print',
    'origin' => 'Certificate of Origin Print',
    'beneficiary' => "Beneficiary's Certificate Print",
    'forwarding' => 'Forwarding Print',
    'bank-forwarding' => 'Bank Forwarding Print',
];
$activePage = $doc;
$pageTitle = $titles[$doc];
$navSection = 'order';
include __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/print-brand.php';
?>
<style>
.doc-brand-header { margin-bottom: 8px; }
.doc-ctrl{background:#1e1e3a;padding:14px 24px;display:flex;gap:16px;align-items:center;flex-wrap:wrap}
.doc-ctrl-label{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#a5b4fc}
.doc-print-btn{margin-left:auto;background:#22c55e;color:#fff;border:none;border-radius:8px;padding:10px 28px;font-size:13px;font-weight:700;cursor:pointer}
.doc-print-btn:hover{background:#16a34a}
.doc-excel-btn{background:#2563eb;color:#fff;border:none;border-radius:8px;padding:10px 22px;font-size:13px;font-weight:700;cursor:pointer}
.doc-excel-btn:hover{background:#1d4ed8}
#docWrap{background:#d1d5db;padding:28px 0;min-height:520px}
.doc-page{position:relative;box-sizing:border-box;width:210mm;min-height:297mm;max-width:820px;margin:0 auto 18px;background:#fff;box-shadow:0 4px 24px rgba(0,0,0,.14);padding:14mm 14mm 12mm;font-family:'Times New Roman',Times,serif;color:#111;font-size:10px;line-height:1.22;overflow:hidden}
.doc-page:not(:last-child){break-after:page;page-break-after:always}
.doc-page:last-child{break-after:auto;page-break-after:auto}
.doc-page .zzal-print-brand--footer{position:static;margin:0!important;margin-top:auto!important;padding-top:6px!important}
.doc-item-page{height:297mm;display:flex;flex-direction:column}
.doc-cont-meta{display:flex;justify-content:space-between;gap:20px;margin:2px 0 5px;padding-bottom:3px;border-bottom:1px solid #333}
.doc-empty{text-align:center;padding:60px 20px;color:#94a3b8;font-family:sans-serif}
.doc-head{display:block;margin:0 0 6px}
.doc-logo,.doc-company{display:none}
.doc-title-wrap{text-align:center;border-bottom:0;padding-bottom:0}
.doc-title{margin:0;font-size:11px;font-weight:700;text-transform:uppercase;text-decoration:underline}
.doc-meta-line{display:flex;justify-content:space-between;gap:20px;margin:4px 0 6px;font-size:10px}
.doc-topbox{width:100%;border-collapse:collapse;margin-bottom:8px}
.doc-topbox td{border:1px solid #333;vertical-align:top;width:50%;padding:4px 6px}
.doc-grid-block div{margin-bottom:1px}
.doc-buyer{margin:4px 0 3px;font-weight:700}
.doc-table{width:100%;border-collapse:collapse;margin-top:4px}
.doc-table th,.doc-table td{border:1px solid #333;padding:3px 5px;vertical-align:top}
.doc-table th{text-align:center;font-weight:700}
.center{text-align:center}.right{text-align:right}
.doc-note-list{margin-top:8px}
.doc-note-row{display:grid;grid-template-columns:140px 1fr;gap:8px;margin-bottom:2px}
.doc-sign-row{margin-top:90px;display:grid;grid-template-columns:1fr 1fr;gap:40px}
.doc-sign-line{width:120px;border-top:1px solid #000;margin-top:28px;margin-bottom:4px}
.doc-sign-right{text-align:right}.doc-sign-right .doc-sign-line{margin-left:auto}
.doc-bottom-bar{margin-top:56px;padding-top:4px;border-top:1px solid #000;font-size:9px;text-align:center}
.doc-letter-body{min-height:760px}
.doc-letter-p{margin:16px 0}
.sc-contract-title{text-align:center;font-family:Arial,Helvetica,sans-serif;font-size:18px;font-weight:800;text-decoration:underline;margin:2px 0 20px}
.sc-contract-meta{display:flex;justify-content:space-between;gap:20px;font:700 15px Arial,Helvetica,sans-serif;margin:0 3px 26px}
.sc-contract-body{font:12px/1.35 Arial,Helvetica,sans-serif}
.sc-contract-row{display:grid;grid-template-columns:22px 255px 12px 1fr;align-items:start;margin-bottom:8px;break-inside:avoid}
.sc-contract-no{text-align:center}.sc-contract-label{padding-right:8px}.sc-contract-value{white-space:pre-wrap}
.sc-contract-row.sc-strong{font-weight:800}.sc-contract-row.sc-space{margin-top:15px}
.sc-contract-accept{font:12px Arial,Helvetica,sans-serif;margin-top:26px}
.sc-contract-signatures{margin-top:18px;display:grid;grid-template-columns:1fr 1fr;gap:70px;text-align:center;font:700 12px Arial,Helvetica,sans-serif}
.sc-contract-signbox{min-height:105px;display:flex;flex-direction:column;justify-content:flex-end;align-items:center}
.sc-contract-signbox img{height:72px;max-width:230px;object-fit:contain;margin-bottom:3px}
/* §7 — anchor signature + footer to the bottom of the page (COO / Beneficiary) */
.doc-page.doc-letter-page{display:flex;flex-direction:column}
.doc-letter-page .doc-letter-body{flex:1;display:flex;flex-direction:column;min-height:0}
.doc-letter-page .doc-letter-sign{margin-top:auto}
<?php if (in_array($doc, ['origin', 'beneficiary'], true)): ?>
.doc-letter-page .doc-letter-sign{margin-top:55mm}
<?php endif; ?>
.doc-letter-page .doc-bottom-bar{margin-top:12px}
@media print{@page{size:A4;margin:0}.doc-ctrl,nav.page-nav,.order-id-bar,.no-print{display:none!important}html,body{width:210mm!important;min-height:0!important;margin:0!important;padding:0!important;overflow:visible!important;background:#fff!important}.app-shell,.form-stack{display:block!important;margin:0!important;padding:0!important;background:#fff!important}.form-stack>*:not(#docWrap){display:none!important}#docWrap{display:block!important;background:none!important;padding:0!important;margin:0!important;width:210mm!important;min-height:0!important}#docPages{display:block!important;margin:0!important;padding:0!important}.doc-page{box-shadow:none;box-sizing:border-box;margin:0;max-width:210mm;width:210mm!important;height:297mm!important;min-height:297mm!important;padding:14mm 14mm 12mm!important;overflow:hidden;display:flex;flex-direction:column;break-inside:avoid;page-break-inside:avoid}.doc-page:not(:last-child){break-after:page;page-break-after:always}.doc-page:last-child{break-after:auto!important;page-break-after:auto!important}}
</style>

<div class="doc-ctrl no-print">
    <span class="doc-ctrl-label"><?php echo htmlspecialchars($titles[$doc], ENT_QUOTES, 'UTF-8'); ?></span>
    <button type="button" class="doc-excel-btn" onclick="downloadDocExcel()">Download Excel</button>
    <button type="button" class="doc-print-btn" onclick="window.print()">Print / Save PDF</button>
</div>

<div id="docWrap">
    <div id="docPages"><div class="doc-empty">Load an order to generate this document.</div></div>
</div>

<script>
const DOC_TYPE = <?php echo json_encode($doc, JSON_UNESCAPED_SLASHES); ?>;
const DOC_COMPANY_NAME = 'Zaber & Zubair Accessories Ltd.';
const DOC_BRAND_HEADER = <?php echo json_encode(zzal_print_brand_header(), JSON_UNESCAPED_SLASHES); ?>;
const DOC_BRAND_FOOTER = <?php echo json_encode(zzal_print_brand_footer(), JSON_UNESCAPED_SLASHES); ?>;
function esc(val){return String(val??'-').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;')}
function splitLines(val){return String(val||'').split(/\r?\n/).map(v=>v.trim()).filter(Boolean)}
function lines(val){return splitLines(val).join('<br>')}
function money(num,digits=2){const n=parseFloat(num||0)||0;return n.toLocaleString('en-US',{minimumFractionDigits:digits,maximumFractionDigits:digits})}
function qtyFmt(num){const n=parseFloat(num||0)||0;return Number.isInteger(n)?money(n,0):money(n,2)}
function fmtDate(val,sep='/'){if(!val)return '-';if(/^\d{4}-\d{2}-\d{2}$/.test(val)){const[y,m,d]=val.split('-');return `${d}${sep}${m}${sep}${y}`}return val}
function plain(val){return splitLines(val).join(', ')}
function amountWords(n){const parsed=parseFloat(n||0)||0;const ones=['Zero','One','Two','Three','Four','Five','Six','Seven','Eight','Nine','Ten','Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen','Seventeen','Eighteen','Nineteen'];const tens=['','','Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];const scales=['','Thousand','Million','Billion'];const chunk=x=>{x=Math.floor(x);if(x===0)return'';if(x<20)return ones[x];if(x<100)return tens[Math.floor(x/10)]+(x%10?' '+ones[x%10]:'');return ones[Math.floor(x/100)]+' Hundred'+(x%100?' '+chunk(x%100):'')};const fullWords=x=>{x=Math.floor(x);if(x===0)return'Zero';const parts=[];let scale=0;while(x>0){const piece=x%1000;if(piece){parts.unshift(chunk(piece)+(scales[scale]?' '+scales[scale]:''))}x=Math.floor(x/1000);scale++}return parts.join(' ').trim()};let centsTotal=Math.round(parsed*100);let dollars=Math.floor(centsTotal/100);let cents=centsTotal%100;if(cents===100){dollars+=1;cents=0}let out=fullWords(dollars)+' USD';if(cents)out+=' and '+fullWords(cents)+' Cents';return out+' Only'}
function resolvePos(res){const lc=res.pages?.lc||{};const exch=res.pages?.exchange||(res.pages.exchange={});if(lc.lcExportSalesContractNo)exch.exportSalesContractNo=lc.lcExportSalesContractNo;if(lc.lcExportSalesContractDate)exch.exportSalesContractDate=lc.lcExportSalesContractDate;const resolved=window.atsResolveDisplayPos?window.atsResolveDisplayPos(res):{pos:res.pages?.sales?.pos||[]};return resolved.pos||[]}
function getCommon(res){const order=res.order||{};const sales=res.pages?.sales||{};const comm=res.pages?.commercial||{};const exch=res.pages?.exchange||{};const lc=res.pages?.lc||{};const doc=res.pages?.[DOC_TYPE]||{};const pos=resolvePos(res);const buyer=[...new Set(pos.map(p=>p.buyer).filter(Boolean))].join(', ')||order.buyer_name||'';const customer=order.customer_name||comm.commercialConsigneeName||sales.customer||'';const applicantName=lc.lcApplicantName||customer;const applicantAddress=lc.lcApplicantAddress||comm.commercialConsigneeAddress||sales.buyerAddress||'';const applicantInfo=[applicantName,applicantAddress].filter(Boolean).join(', ');const beneficiary=lc.lcBeneficiaryName||comm.commercialBeneficiaryName||DOC_COMPANY_NAME;const beneficiaryAddress=lc.lcBeneficiaryAddress||comm.commercialBeneficiaryAddress||'';const factoryAddress=lc.lcFactoryAddress||comm.commercialFactoryAddress||'';const advisingBank=lc.reimbursementBank||comm.commercialAdvisingBank||exch.payToBankAddress||exch.payToBankName||'';const consigneeBank=lc.negotiatingBeneficiaryBank||comm.commercialConsigneeBankAddress||exch.beneficiaryBankAddress||'';const lcNo=exch.masterLcNo||lc.lcNumber||'';const lcDate=exch.masterLcDate||lc.lcDate||'';const contract=exch.exportSalesContractNo||lc.lcNumber||'';const contractDate=exch.exportSalesContractDate||lc.lcDate||'';const proforma=comm.proformaNo||sales.piNum||'';const proformaDate=comm.proformaDate||sales.piDate||'';const carrier=exch.carrierNameMaster||comm.commercialCarrier||'By Truck';const packing=exch.packingDetailsMaster||'Standard Poly Packing Rolls';const amount=exch.exchangeAmount||String(comm.commercialTotalAmount||'').replace(/[^\d.]/g,'')||0;const applicantsParts=[];if(exch.beneficiaryVatBin)applicantsParts.push("Beneficiary's Vat/Bin: "+exch.beneficiaryVatBin);if(exch.hsCodeMaster)applicantsParts.push('H.S Code No: '+exch.hsCodeMaster);const applicantLine=applicantsParts.join(' and ');return{order,sales,comm,exch,lc,doc,pos,buyer,customer,applicantName,applicantAddress,applicantInfo,beneficiary,beneficiaryAddress,factoryAddress,advisingBank,consigneeBank,lcNo,lcDate,contract,contractDate,proforma,proformaDate,carrier,packing,amount,applicantLine}}
function buildItemRows(pos){let totalQty=0;const rows=[];pos.forEach(po=>{(po.items||[]).forEach(item=>{const qty=parseFloat(item.qty||0)||0;totalQty+=qty;rows.push({desc:item.desc||item.itemName||'-',ply:item.ply||'-',qty})})});return{rows,totalQty}}
function paginateDocumentItems(rows){
    if(!rows.length||rows.length<=18)return[rows];
    const pages=[];
    const firstTake=rows.length<=28?rows.length-10:28;
    pages.push(rows.slice(0,firstTake));
    let offset=firstTake;
    const continuationRows=38;
    // Keep room on the last sheet for totals, document notes, EPZ/PI references,
    // signatures and the fixed footer, like the Single PI continuation page.
    const finalPageRows=12;
    while(rows.length-offset>finalPageRows){
        const remaining=rows.length-offset;
        const take=remaining<=finalPageRows*2?Math.ceil(remaining/2):Math.min(continuationRows,remaining-finalPageRows);
        pages.push(rows.slice(offset,offset+take));
        offset+=take;
    }
    pages.push(rows.slice(offset));
    return pages;
}

function renderPackingStylePaged(titleText,res){
    const c=getCommon(res),built=buildItemRows(c.pos),rows=built.rows,totalQty=built.totalQty;
    const pages=paginateDocumentItems(rows);
    const noteRows=[['Packing',c.packing],['L/C No.',c.lcNo?(c.lcNo+(c.lcDate?' Dated '+fmtDate(c.lcDate,'.'):'')):'-'],["Beneficiary's Vat/bin",c.applicantLine||'-'],[DOC_TYPE==='delivery'?'Export L/C No.':'Export Sales Contract No.',c.contract?(c.contract+(c.contractDate?' Dated '+fmtDate(c.contractDate,'.'):'')):'-'],['Proforma Invoice No',c.proforma?(c.proforma+(c.proformaDate?' Dated '+fmtDate(c.proformaDate,'.'):'')):'-'],['Carrier',c.carrier||'-']];
    const dateText=DOC_TYPE==='delivery'?fmtDate(c.comm.invoiceDate||c.proformaDate||c.order.created_at?.slice(0,10)||'', '.'):'';
    const refText=DOC_TYPE==='delivery'?(c.comm.invoiceNo||c.proforma||'-'):(c.proforma||'-');
    let startIndex=0;
    return pages.map((pageRows,pageIndex)=>{
        const isLastPage=pageIndex===pages.length-1;
        const itemRows=pageRows.map((row,idx)=>`<tr><td class="center">${startIndex+idx+1}</td><td>${esc(row.desc)}</td><td class="right">${esc(qtyFmt(row.qty))}</td></tr>`).join('')||'<tr><td colspan="3" class="center">No items found</td></tr>';
        startIndex+=pageRows.length;
        const firstDetails=pageIndex===0?`${DOC_TYPE==='delivery'?`<div class="doc-meta-line"><div>Ref No: ${esc(refText)}</div><div>Date: ${esc(dateText)}</div></div>`:''}<table class="doc-topbox"><tr><td><div class="doc-grid-block"><div><strong>Beneficiary:</strong></div><div>${lines(c.beneficiaryAddress)}</div><div>${lines(c.factoryAddress)}</div><div style="margin-top:4px;"><strong>Advising Bank:</strong></div><div>${lines(c.advisingBank)}</div></div></td><td><div class="doc-grid-block"><div><strong>Consignee:</strong></div><div>${esc(c.customer)}</div><div>${lines(c.comm.commercialConsigneeAddress||c.sales.buyerAddress||'')}</div><div style="margin-top:4px;"><strong>Consignee's Bank:</strong></div><div>${lines(c.consigneeBank)}</div></div></td></tr></table><div class="doc-buyer">BUYER: ${esc(c.buyer||'-')}</div>`:`<div class="doc-cont-meta"><span><strong>${DOC_TYPE==='delivery'?'Ref No':'PI No'}:</strong> ${esc(refText)}</span><span><strong>Date:</strong> ${esc(dateText||fmtDate(c.proformaDate,'.'))}</span></div>`;
        const finalBlocks=isLastPage?`${DOC_TYPE==='delivery'?'<div style="margin-top:4px;">Freight prepaid</div>':''}<div class="doc-note-list">${noteRows.map(([label,val])=>`<div class="doc-note-row"><div>${esc(label)}</div><div>${esc(val||'-')}</div></div>`).join('')}</div><div class="doc-sign-row"><div><img src="<?= BASE_PATH ?>/AKM.png" alt="Authorised Signature" style="height:100px;max-width:300px;object-fit:contain;display:block;"></div><div class="doc-sign-right" style="display:flex;flex-direction:column;justify-content:space-between;"><div>Goods received in good condition</div><div><div class="doc-sign-line"></div><div>Signature of Consignee with Seal</div></div></div></div>`:'';
        return `<div class="doc-page doc-item-page"><div class="doc-brand-header">${DOC_BRAND_HEADER}</div><div class="doc-head"><div class="doc-logo">ZZAL</div><div class="doc-title-wrap"><div class="doc-company">${esc(DOC_COMPANY_NAME)}</div><div class="doc-title">${esc(titleText)}</div></div></div>${firstDetails}<table class="doc-table"><thead><tr><th style="width:42px;">SL NO.</th><th>Description of Goods</th><th style="width:86px;">Quantity${DOC_TYPE==='truck'?'/Cone':''}</th></tr></thead><tbody>${itemRows}${isLastPage?`<tr><td colspan="2" class="right"><strong>Total</strong></td><td class="right"><strong>${esc(qtyFmt(totalQty))}</strong></td></tr>`:''}</tbody></table>${finalBlocks}${DOC_BRAND_FOOTER}</div>`;
    }).join('');
}
function renderPackingStyle(titleText,res){const c=getCommon(res);const built=buildItemRows(c.pos);const rows=built.rows;const totalQty=built.totalQty;const noteRows=[['Packing',c.packing],['L/C No.',c.lcNo?(c.lcNo+(c.lcDate?' Dated '+fmtDate(c.lcDate,'.'):'')):'-'],["Beneficiary's Vat/bin",c.applicantLine||'-'],[DOC_TYPE==='delivery'?'Export L/C No.':'Export Sales Contract No.',c.contract?(c.contract+(c.contractDate?' Dated '+fmtDate(c.contractDate,'.'):'')):'-'],['Proforma Invoice No',c.proforma?(c.proforma+(c.proformaDate?' Dated '+fmtDate(c.proformaDate,'.'):'')):'-'],['Carrier',c.carrier||'-']];const dateText=DOC_TYPE==='delivery'?fmtDate(c.comm.invoiceDate||c.proformaDate||c.order.created_at?.slice(0,10)||'', '.'):'';const refText=DOC_TYPE==='delivery'?(c.comm.invoiceNo||c.proforma||'-'):'';return `<div class="doc-page"><div class="doc-brand-header">${DOC_BRAND_HEADER}</div><div class="doc-head"><div class="doc-logo">ZZAL</div><div class="doc-title-wrap"><div class="doc-company">${esc(DOC_COMPANY_NAME)}</div><div class="doc-title">${esc(titleText)}</div></div></div>${DOC_TYPE==='delivery'?`<div class="doc-meta-line"><div>Ref No: ${esc(refText)}</div><div>Date: ${esc(dateText)}</div></div>`:''}<table class="doc-topbox"><tr><td><div class="doc-grid-block"><div><strong>Beneficiary:</strong></div><div>${lines(c.beneficiaryAddress)}</div><div>${lines(c.factoryAddress)}</div><div style="margin-top:4px;"><strong>Advising Bank:</strong></div><div>${lines(c.advisingBank)}</div></div></td><td><div class="doc-grid-block"><div><strong>Consignee:</strong></div><div>${esc(c.customer)}</div><div>${lines(c.comm.commercialConsigneeAddress||c.sales.buyerAddress||'')}</div><div style="margin-top:4px;"><strong>Consignee's Bank:</strong></div><div>${lines(c.consigneeBank)}</div></div></td></tr></table><div class="doc-buyer">BUYER: ${esc(c.buyer||'-')}</div><table class="doc-table"><thead><tr><th style="width:42px;">SL NO.</th><th>Description of Goods</th><th style="width:86px;">Quantity${DOC_TYPE==='truck'?'/Cone':''}</th></tr></thead><tbody>${rows.map((row,idx)=>`<tr><td class="center">${idx+1}</td><td>${esc(row.desc)}</td><td class="right">${esc(qtyFmt(row.qty))}</td></tr>`).join('')||'<tr><td colspan="3" class="center">No items found</td></tr>'}<tr><td colspan="2" class="right"><strong>Total</strong></td><td class="right"><strong>${esc(qtyFmt(totalQty))}</strong></td></tr></tbody></table>${DOC_TYPE==='delivery'?'<div style="margin-top:4px;">Freight prepaid</div>':''}<div class="doc-note-list">${noteRows.map(([label,val])=>`<div class="doc-note-row"><div>${esc(label)}</div><div>${esc(val||'-')}</div></div>`).join('')}</div><div class="doc-sign-row"><div><img src="<?= BASE_PATH ?>/AKM.png" alt="For Zaber & Zubair Accessories Ltd. — Authorised Signature" style="height:100px;max-width:300px;object-fit:contain;display:block;"></div><div class="doc-sign-right" style="display:flex;flex-direction:column;justify-content:space-between;"><div>Goods received in good condition</div><div><div class="doc-sign-line"></div><div>Signature of Consignee with Seal</div></div></div></div>${DOC_BRAND_FOOTER}</div>`}
function renderOrigin(res){const c=getCommon(res);const statement='This is to certify that the goods, which are delivered under '+(c.lcNo?'L/C No. '+c.lcNo+(c.lcDate?' Dated '+fmtDate(c.lcDate,'.'):''):'')+(c.proforma?' as per Proforma Invoice No. '+c.proforma+(c.proformaDate?' Dated '+fmtDate(c.proformaDate,'.'):''):'')+' is of Bangladesh Origin.';const line2=(c.exch.beneficiaryVatBin?"Beneficiary's Vat/Bin: "+c.exch.beneficiaryVatBin+' and ':'')+(c.exch.hsCodeMaster?'H.S Code No: '+c.exch.hsCodeMaster+'.':'');const contract=c.contract?(c.contract+(c.contractDate?' Dated '+fmtDate(c.contractDate,'.'):'')):'-';return `<div class="doc-page doc-letter-page"><div class="doc-brand-header">${DOC_BRAND_HEADER}</div><div class="doc-head"><div class="doc-logo">ZZAL</div><div class="doc-title-wrap"><div class="doc-company">${esc(DOC_COMPANY_NAME)}</div><div class="doc-title">Certificate of Origin</div></div></div><div class="doc-letter-body"><p class="doc-letter-p">${esc(statement)}</p><p class="doc-letter-p">${esc(line2)}</p><p class="doc-letter-p">Export Sales Contract No. : ${esc(contract)}</p><p class="doc-letter-p">For and of behalf of,</p><div class="doc-letter-sign"><img src="<?= BASE_PATH ?>/AKM.png" alt="For Zaber & Zubair Accessories Ltd. — Authorised Signature" style="height:100px;max-width:300px;object-fit:contain;display:block;"></div></div>${DOC_BRAND_FOOTER}</div>`}
function renderBeneficiary(res){const c=getCommon(res);const qty=c.comm.commercialTotalQty||qtyFmt(buildItemRows(c.pos).totalQty);const amt=c.amount?money(c.amount,2):'0.00';const bank=plain(c.consigneeBank||'');const statement1='We hereby confirm that we have supplied Accessories for 100% export oriented garments industry '+qty+' cones / pcs total amount of US $ '+amt+' all other details as per pro-forma invoice No. '+(c.proforma||'-')+(c.proformaDate?' Dated '+fmtDate(c.proformaDate,'.'):'')+'. To The '+(c.customer||'-')+(bank?' against their '+bank:'')+(c.lcNo?' L/C No. '+c.lcNo+(c.lcDate?' Dated '+fmtDate(c.lcDate,'.'):''):'')+'.';const statement2="We do hereby undertake that the said accessories shipment from : Beneficiary's factory to applicant factory warehouse. We also certified that quantity, quality, rate specification & all other terms & conditions are as per suppliers pro-forma invoice No. "+(c.proforma||'-')+(c.proformaDate?' Dated '+fmtDate(c.proformaDate,'.'):'')+' any short and defective goods to be replaced by us on free of cost.';return `<div class="doc-page doc-letter-page"><div class="doc-brand-header">${DOC_BRAND_HEADER}</div><div class="doc-head"><div class="doc-logo">ZZAL</div><div class="doc-title-wrap"><div class="doc-company">${esc(DOC_COMPANY_NAME)}</div><div class="doc-title">Beneficiary's Certificate</div></div></div><div class="doc-letter-body"><p class="doc-letter-p">${esc(statement1)}</p><p class="doc-letter-p">${esc(statement2)}</p><div class="doc-letter-sign"><div>For &amp; on behalf of</div><img src="<?= BASE_PATH ?>/AKM.png" alt="For Zaber & Zubair Accessories Ltd. — Authorised Signature" style="height:100px;max-width:300px;object-fit:contain;display:block;"></div></div>${DOC_BRAND_FOOTER}</div>`}
function renderForwarding(res){const c=getCommon(res);const f=c.doc||{};const rows=[['1','Bill of Exchange',f.forwardingQty1||'2 Copies'],['2','Commercial Invoice',f.forwardingQty2||'1 Copy'],['3','Packing List',f.forwardingQty3||'1 Copy'],['4','Delivery Challan',f.forwardingQty4||'1 Copy'],['5','Certificate of Origin',f.forwardingQty5||'1 Copy'],['6','Beneficiary Certificate',f.forwardingQty6||'1 Copy'],['7','Truck Challan',f.forwardingQty7||'1 Copy'],['8','Delivery Challan Original Copy',f.forwardingQty8||'1 Copy'],['9','L/C Copy & PI',f.forwardingQty9||'1 Copy'],['10',f.forwardingExtraDesc1||'',f.forwardingExtraQty1||''],['11',f.forwardingExtraDesc2||'',f.forwardingExtraQty2||''],['12',f.forwardingExtraDesc3||'',f.forwardingExtraQty3||''],['13',f.forwardingExtraDesc4||'',f.forwardingExtraQty4||'']].filter(r=>r[1]||r[2]);return `<div class="doc-page"><div class="doc-brand-header">${DOC_BRAND_HEADER}</div><div class="doc-head"><div class="doc-logo">ZZAL</div><div class="doc-title-wrap"><div class="doc-company">${esc(DOC_COMPANY_NAME)}</div><div class="doc-title">Document Check List</div></div></div><div class="doc-meta-line"><div>Document Submit Date: ${esc(fmtDate(f.forwardingSubmitDate||'', '.'))}</div></div><div class="doc-note-list" style="margin-bottom:16px;"><div class="doc-note-row"><div>Customer Name</div><div>${esc(c.customer||'-')}</div></div><div class="doc-note-row"><div>LC No.</div><div>${esc(c.lcNo||'-')}</div></div><div class="doc-note-row"><div>LC Date</div><div>${esc(fmtDate(c.lcDate||'', '.'))}</div></div><div class="doc-note-row"><div>Value</div><div>US $ ${esc(money(c.amount,2))}</div></div><div class="doc-note-row"><div>Document Value</div><div>US $ ${esc(money(c.amount,2))}</div></div></div><table class="doc-table"><thead><tr><th style="width:42px;">SL</th><th>Description</th><th style="width:160px;">Requirement Qty</th></tr></thead><tbody>${rows.map(r=>`<tr><td class="center">${esc(r[0])}</td><td>${esc(r[1])}</td><td>${esc(r[2])}</td></tr>`).join('')}</tbody></table><div style="margin-top:90px;"><img src="<?= BASE_PATH ?>/AKM.png" alt="For Zaber & Zubair Accessories Ltd. — Authorised Signature" style="height:100px;max-width:300px;object-fit:contain;display:block;"></div>${DOC_BRAND_FOOTER}</div>`}
function renderBankForwarding(res){const c=getCommon(res);const amount=parseFloat(c.amount||0)||0;const fwd=res.pages?.['bank-forwarding']||{};const rows=[['1','Bill of Exchange'],['2','Commercial Invoice'],['3','Packing List'],['4','Delivery Challan'],['5','Certificate of Origin'],['6','Beneficiary Certificate'],['7','Truck Challan'],['8','Mushok Challan 6.3'],['9','Others']];const defaults=[['2 Copies','2 Copies'],['1 Copy','6 Copies'],['1 Copy','3 Copies'],['1 Copy','4 Copies'],['1 Copy','2 Copies'],['1 Copy','2 Copies'],['1 Copy','0 Copy'],['1 Copy','0 Copy'],['1 Copy','0 Copy']];return `<div class="doc-page"><div class="doc-brand-header">${DOC_BRAND_HEADER}</div><div class="doc-head"><div class="doc-logo">ZZAL</div><div class="doc-title-wrap"><div class="doc-company">${esc(DOC_COMPANY_NAME)}</div><div class="doc-title">Bank Forwarding</div></div></div><div class="doc-meta-line"><div>Date: ${esc(fmtDate(fwd.forwardingDate||c.comm.invoiceDate||c.proformaDate||'', '.'))}</div><div>Reference No.: ${esc(c.comm.invoiceNo||c.proforma||'-')}</div></div><div style="margin:14px 0 18px;"><div>To</div><div><strong>The Manager,</strong></div><div><strong>${esc(c.exch.payToBankName||'-')}</strong></div><div>${lines(c.exch.payToBankAddress||'')}</div></div><div style="margin-bottom:14px;font-weight:700;">Subject: Application for the following negotiation documents for US $ ${esc(money(amount,2))} Against Letter of Credit No. ${esc(c.lcNo||'-')} dated ${esc(fmtDate(c.lcDate||'', '.'))} of ${esc(c.customer||'-')}.</div><div style="margin-bottom:12px;">Dear Sir,<br>We hereby submit the following documents for negotiation of US $ ${esc(money(amount,2))} (${esc(c.exch.tenorWordsMaster||amountWords(amount))}) delivery of Garments accessories as per proforma Invoice No. ${esc(c.proforma||'-')} Dated ${esc(fmtDate(c.proformaDate||'', '.'))}.</div><table class="doc-table"><thead><tr><th style="width:42px;">SL</th><th>Description</th><th style="width:140px;">Advising Bank</th><th style="width:140px;">Consignee's Bank</th></tr></thead><tbody>${rows.map((row,idx)=>`<tr><td class="center">${row[0]}</td><td>${esc(row[1])}</td><td class="center">${esc(defaults[idx][0])}</td><td class="center">${esc(defaults[idx][1])}</td></tr>`).join('')}</tbody></table><div style="margin-top:16px;">So we request you to negotiate the above stated matter as early as possible.<br>Thanking you,<br>Sincerely yours.<br></div><div style="margin-top:8px;"><img src="<?= BASE_PATH ?>/AKM.png" alt="For Zaber & Zubair Accessories Ltd. — Authorised Signature" style="height:100px;max-width:300px;object-fit:contain;display:block;"></div>${DOC_BRAND_FOOTER}</div>`}
function epzReferenceBlock(res){const lc=res.pages?.lc||{};if(lc.lcZoneType!=='epz')return'';return `<div class="doc-note-list" style="margin-top:10px;padding:7px 9px;border:1px solid #cbd5e1;"><div class="doc-note-row"><div>EXP No.</div><div>${esc(lc.lcExpNo||'-')}</div></div><div class="doc-note-row"><div>EXP Date</div><div>${esc(fmtDate(lc.lcExpDate||'', '.'))}</div></div><div class="doc-note-row"><div>IP No.</div><div>${esc(lc.lcIpNo||'-')}</div></div><div class="doc-note-row"><div>IP Date</div><div>${esc(fmtDate(lc.lcIpDate||'', '.'))}</div></div></div>`}
function addLcApplicantInformation(html,res){if(!['packing','delivery','truck','origin'].includes(DOC_TYPE))return html;const c=getCommon(res);const applicant=`<div class="doc-note-row"><div>Applicant Information</div><div>${esc(c.applicantInfo||'-')}</div></div>`;if(DOC_TYPE==='origin'){return html.replace('<p class="doc-letter-p">Export Sales Contract No.',`<p class="doc-letter-p"><strong>Applicant Information:</strong> ${esc(c.applicantInfo||'-')}</p><p class="doc-letter-p">Export Sales Contract No.`)}return html.replace('<div class="doc-note-list">','<div class="doc-note-list">'+applicant)}
function insertBeforeLast(html,needle,addition){const index=html.lastIndexOf(needle);return index<0?html:html.slice(0,index)+addition+html.slice(index)}
function addEpzReferences(html,res){const block=epzReferenceBlock(res);const withEpz=block?insertBeforeLast(html,DOC_BRAND_FOOTER,block):html;return addLcApplicantInformation(withEpz,res)}
function piReferenceBlock(res){const s=window.atsResolveOrderPiSummary?window.atsResolveOrderPiSummary(res):{numbers:[],total:0,count:0};if(!s.count&&!s.total)return'';return `<div class="doc-note-list" style="margin-top:10px;padding:7px 9px;border:1px solid #a5b4fc;"><div class="doc-note-row"><div>PI Numbers Included</div><div>${esc((s.numbers||[]).join(' / ')||'-')}</div></div><div class="doc-note-row"><div>Total PI Value (USD)</div><div>$ ${esc(money(s.total,2))}</div></div></div>`}
function addPiReferences(html,res){const block=piReferenceBlock(res);return block?insertBeforeLast(html,DOC_BRAND_FOOTER,block):html}
function renderSalesContract(res){
    const c=getCommon(res),s=c.doc||{};
    const safeLines=value=>String(value||'-').split(/\r?\n/).map(line=>esc(line)).join('<br>');
    const row=(no,label,value,cls='')=>`<div class="sc-contract-row ${cls}"><div class="sc-contract-no">${esc(no)}</div><div class="sc-contract-label">${esc(label)}</div><div>:</div><div class="sc-contract-value">${safeLines(value)}</div></div>`;
    const buyer=[s.scBuyerName,s.scBuyerAddress,s.scBuyerBin?'Bin No : '+s.scBuyerBin:''].filter(Boolean).join('\n');
    const buyerBank=[s.scBuyerBankName,s.scBuyerBankAddress,s.scBuyerBankAccount?'Bank Account No- '+s.scBuyerBankAccount:'',s.scBuyerBankSwift?'Bank Swift-BIC No- '+s.scBuyerBankSwift:''].filter(Boolean).join('\n');
    const supplier=[s.scSupplierName,s.scSupplierFactory?'Factory: '+s.scSupplierFactory:'',s.scSupplierOffice?'H/O: '+s.scSupplierOffice:''].filter(Boolean).join('\n');
    const supplierBank=[s.scSupplierBankName,s.scSupplierBankDetails].filter(Boolean).join('\n');
    const buyerSc=[s.scBuyerContractNo,s.scBuyerContractDate?'Date. '+fmtDate(s.scBuyerContractDate,'.'):''].filter(Boolean).join(' ');
    const amount=parseFloat(s.scTotalValue||0)||0;
    const epz=c.lc.lcZoneType==='epz' ? row('','EXP No. & Date',`${c.lc.lcExpNo||'-'} - ${fmtDate(c.lc.lcExpDate||'', '.')}`)+row('','IP No. & Date',`${c.lc.lcIpNo||'-'} - ${fmtDate(c.lc.lcIpDate||'', '.')}`) : '';
    return `<div class="doc-page"><div class="doc-brand-header">${DOC_BRAND_HEADER}</div><div class="sc-contract-title">SALES CONTRACT</div><div class="sc-contract-meta"><div>Contract No: ${esc(s.scContractNo||'-')}</div><div>Date. ${esc(fmtDate(s.scContractDate||'', '.'))}</div></div><div class="sc-contract-body">${row('1','Buyer / Importer Name & Address',buyer)}${row('2','Buyer / Importer Bank Detail',buyerBank,'sc-space')}${row('3','Supplier Name & Address',supplier,'sc-space')}${row('4','Supplier Bank Name & Address',supplierBank,'sc-space')}${row('5','Description of goods',s.scGoodsDescription,'sc-space')}${row('6','Buyer S/C No',buyerSc)}${epz}${row('7','Shipment Validity',fmtDate(s.scShipmentValidity||'', '.'))}${row('8','Expiry Validity',fmtDate(s.scExpiryValidity||'', '.'))}${row('9','Goods Quantity',s.scGoodsQuantity)}${row('10','Price',s.scPrice)}${row('11','Total Value','USD. '+money(amount,2),'sc-strong')}${row('','',s.scTotalWords||amountWords(amount))}${row('12','Terms of Payment',s.scPaymentTerms)}${row('13','Delivery Terms',s.scDeliveryTerms)}${row('14','Delivery to',s.scDeliveryTo)}${row('15','H.S Code',s.scHsCode,'sc-strong')}${row('16','Other Terms & Condition',s.scOtherTerms)}<div class="sc-contract-accept">Configuration for Acceptance of the Above By Endorsing the Same.</div><div class="sc-contract-signatures"><div class="sc-contract-signbox"><div>Confirmed by the buyer</div></div><div class="sc-contract-signbox"><img src="<?= BASE_PATH ?>/AKM.png" alt="Authorised Signature"><div>Confirmed by the seller.</div></div></div></div>${DOC_BRAND_FOOTER}</div>`;
}
function renderPackingList(res){
    const packing=res.pages?.packing||{};
    const commercial=res.pages?.commercial||{};
    const sales=res.pages?.sales||{};
    const isEpz=res.pages?.lc?.lcZoneType==='epz';
    const packingListNo=packing.packingListNo||commercial.invoiceNo||commercial.proformaNo||sales.piNum||'-';
    const html=renderPackingStylePaged('Packing List',res);
    if(!isEpz)return html;
    const meta=`<div class="doc-meta-line"><div>Packing List No: ${esc(packingListNo)}</div><div>Truck No: ${esc(packing.packingTruckNo||'-')}</div></div>`;
    return html.replace('<table class="doc-topbox">',meta+'<table class="doc-topbox">');
}
function renderDoc(res){if(DOC_TYPE==='sales-contract')return addPiReferences(renderSalesContract(res),res);let html;if(DOC_TYPE==='packing')html=renderPackingList(res);else if(DOC_TYPE==='delivery')html=renderPackingStylePaged('Delivery Challan',res);else if(DOC_TYPE==='truck')html=renderPackingStylePaged('Truck Challan',res);else if(DOC_TYPE==='origin')html=renderOrigin(res);else if(DOC_TYPE==='beneficiary')html=renderBeneficiary(res);else if(DOC_TYPE==='forwarding')html=renderForwarding(res);else if(DOC_TYPE==='bank-forwarding')html=renderBankForwarding(res);else return '<div class="doc-empty">Unsupported document.</div>';return addPiReferences(addEpzReferences(html,res),res)}
let _docExcelDone = false;
function downloadDocExcel(){atsDownloadExcelFromElement({elementId:'docPages',filename:DOC_TYPE+'-'+((window.getCurrentOrderId&&window.getCurrentOrderId())||'document'),title:document.title})}
window.onOrderLoad=function(res){const holder=document.getElementById('docPages');if(!holder)return;holder.innerHTML=renderDoc(res);if(atsShouldAutoExcel()&&!_docExcelDone){_docExcelDone=true;setTimeout(downloadDocExcel,250)}}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>

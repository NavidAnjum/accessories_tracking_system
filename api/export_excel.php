<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

function excel_fail(int $code, string $message): void
{
    http_response_code($code);
    header('Content-Type: text/plain; charset=UTF-8');
    echo $message;
    exit;
}

function excel_xml($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_XML1, 'UTF-8');
}

function excel_file_name(string $value): string
{
    $value = preg_replace('/[\\\\\/:*?"<>|]+/', ' ', $value);
    $value = preg_replace('/\s+/', ' ', trim((string) $value));
    return $value !== '' ? $value : 'document';
}

function excel_sheet_name(string $value, array &$used): string
{
    $name = preg_replace('/[\\\\\/:*?\[\]]+/', ' ', $value);
    $name = preg_replace('/\s+/', ' ', trim((string) $name));
    $name = $name !== '' ? $name : 'Sheet';
    $name = mb_substr($name, 0, 31);
    $base = $name;
    $i = 2;
    while (in_array(mb_strtolower($name), $used, true)) {
        $suffix = ' ' . $i;
        $name = mb_substr($base, 0, max(1, 31 - mb_strlen($suffix))) . $suffix;
        $i++;
    }
    $used[] = mb_strtolower($name);
    return $name;
}

function excel_decode_image(string $src): array
{
    if (!preg_match('#^data:image/(png|jpe?g);base64,(.+)$#s', $src, $m)) {
        excel_fail(422, 'Unsupported image format for Excel export.');
    }
    $ext = strtolower($m[1]);
    $ext = $ext === 'jpeg' ? 'jpg' : $ext;
    $bin = base64_decode($m[2], true);
    if ($bin === false) {
        excel_fail(422, 'Invalid image data for Excel export.');
    }
    return [$ext, $bin];
}

function excel_sheet_xml(int $sheetIndex): string
{
    return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
        . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
        . '<dimension ref="A1"/>'
        . '<sheetViews><sheetView workbookViewId="0"/></sheetViews>'
        . '<sheetFormatPr defaultRowHeight="15"/>'
        . '<cols>'
        . '<col min="1" max="26" width="14" customWidth="1"/>'
        . '</cols>'
        . '<sheetData><row r="1"><c r="A1" t="inlineStr"><is><t></t></is></c></row></sheetData>'
        . '<pageMargins left="0.2" right="0.2" top="0.3" bottom="0.3" header="0.1" footer="0.1"/>'
        . '<drawing r:id="rId1"/>'
        . '</worksheet>';
}

function excel_drawing_xml(int $imageIndex, int $width, int $height): string
{
    $cx = max(1, (int) round($width * 9525));
    $cy = max(1, (int) round($height * 9525));
    return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<xdr:wsDr xmlns:xdr="http://schemas.openxmlformats.org/drawingml/2006/spreadsheetDrawing" '
        . 'xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" '
        . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
        . '<xdr:oneCellAnchor>'
        . '<xdr:from><xdr:col>0</xdr:col><xdr:colOff>0</xdr:colOff><xdr:row>0</xdr:row><xdr:rowOff>0</xdr:rowOff></xdr:from>'
        . '<xdr:ext cx="' . $cx . '" cy="' . $cy . '"/>'
        . '<xdr:pic>'
        . '<xdr:nvPicPr>'
        . '<xdr:cNvPr id="' . $imageIndex . '" name="Picture ' . $imageIndex . '"/>'
        . '<xdr:cNvPicPr/>'
        . '</xdr:nvPicPr>'
        . '<xdr:blipFill><a:blip r:embed="rId1"/><a:stretch><a:fillRect/></a:stretch></xdr:blipFill>'
        . '<xdr:spPr>'
        . '<a:xfrm><a:off x="0" y="0"/><a:ext cx="' . $cx . '" cy="' . $cy . '"/></a:xfrm>'
        . '<a:prstGeom prst="rect"><a:avLst/></a:prstGeom>'
        . '</xdr:spPr>'
        . '</xdr:pic>'
        . '<xdr:clientData/>'
        . '</xdr:oneCellAnchor>'
        . '</xdr:wsDr>';
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    excel_fail(405, 'Method not allowed.');
}

if (!class_exists('ZipArchive')) {
    excel_fail(500, 'ZipArchive is not available on this server.');
}

$payload = json_decode(file_get_contents('php://input') ?: '', true);
if (!is_array($payload)) {
    excel_fail(400, 'Invalid request payload.');
}

$title = trim((string) ($payload['title'] ?? 'Document'));
$filename = excel_file_name((string) ($payload['filename'] ?? $title ?: 'document'));
$pages = $payload['pages'] ?? null;
if (!is_array($pages) || !$pages) {
    excel_fail(422, 'No pages were provided for Excel export.');
}

$tmpBase = tempnam(sys_get_temp_dir(), 'ats_xlsx_');
if ($tmpBase === false) {
    excel_fail(500, 'Could not allocate temporary export file.');
}
$xlsxPath = $tmpBase . '.xlsx';
@unlink($tmpBase);

$zip = new ZipArchive();
if ($zip->open($xlsxPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    excel_fail(500, 'Could not create Excel workbook.');
}

$sheetUsed = [];
$sheetEntries = [];
$workbookRels = [];
$contentOverrides = [
    '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>',
    '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>',
    '<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>'
];
$contentDefaults = [
    'rels' => 'application/vnd.openxmlformats-package.relationships+xml',
    'xml'  => 'application/xml'
];
$sheetNames = [];

foreach (array_values($pages) as $idx => $page) {
    if (!is_array($page)) {
        $zip->close();
        @unlink($xlsxPath);
        excel_fail(422, 'Invalid page entry for Excel export.');
    }

    $pageNumber = $idx + 1;
    $width = max(1, (int) round((float) ($page['width'] ?? 1)));
    $height = max(1, (int) round((float) ($page['height'] ?? 1)));
    [$ext, $binary] = excel_decode_image((string) ($page['src'] ?? ''));

    $sheetLabel = $title !== '' ? $title : 'Document';
    if (count($pages) > 1) {
        $sheetLabel .= ' ' . $pageNumber;
    }
    $sheetName = excel_sheet_name($sheetLabel, $sheetUsed);
    $sheetNames[] = $sheetName;

    $imagePath = "xl/media/image{$pageNumber}.{$ext}";
    $drawingPath = "xl/drawings/drawing{$pageNumber}.xml";
    $drawingRelPath = "xl/drawings/_rels/drawing{$pageNumber}.xml.rels";
    $sheetPath = "xl/worksheets/sheet{$pageNumber}.xml";
    $sheetRelPath = "xl/worksheets/_rels/sheet{$pageNumber}.xml.rels";

    $zip->addFromString($imagePath, $binary);
    $zip->addFromString($drawingPath, excel_drawing_xml($pageNumber, $width, $height));
    $zip->addFromString(
        $drawingRelPath,
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
        . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/image' . $pageNumber . '.' . $ext . '"/>'
        . '</Relationships>'
    );
    $zip->addFromString($sheetPath, excel_sheet_xml($pageNumber));
    $zip->addFromString(
        $sheetRelPath,
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
        . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/drawing" Target="../drawings/drawing' . $pageNumber . '.xml"/>'
        . '</Relationships>'
    );

    $sheetEntries[] = '<sheet name="' . excel_xml($sheetName) . '" sheetId="' . $pageNumber . '" r:id="rId' . $pageNumber . '"/>';
    $workbookRels[] = '<Relationship Id="rId' . $pageNumber . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . $pageNumber . '.xml"/>';
    $contentOverrides[] = '<Override PartName="/' . $sheetPath . '" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
    $contentOverrides[] = '<Override PartName="/' . $drawingPath . '" ContentType="application/vnd.openxmlformats-officedocument.drawing+xml"/>';
    if (!isset($contentDefaults[$ext])) {
        $contentDefaults[$ext] = $ext === 'png' ? 'image/png' : 'image/jpeg';
    }
}

$zip->addFromString(
    '[Content_Types].xml',
    '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
    . implode('', array_map(
        static fn($ext, $type) => '<Default Extension="' . excel_xml($ext) . '" ContentType="' . excel_xml($type) . '"/>',
        array_keys($contentDefaults),
        array_values($contentDefaults)
    ))
    . implode('', $contentOverrides)
    . '</Types>'
);

$zip->addFromString(
    '_rels/.rels',
    '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
    . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
    . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
    . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
    . '</Relationships>'
);

$zip->addFromString(
    'xl/workbook.xml',
    '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
    . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
    . '<bookViews><workbookView xWindow="0" yWindow="0" windowWidth="24000" windowHeight="14000"/></bookViews>'
    . '<sheets>' . implode('', $sheetEntries) . '</sheets>'
    . '</workbook>'
);

$zip->addFromString(
    'xl/_rels/workbook.xml.rels',
    '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
    . implode('', $workbookRels)
    . '</Relationships>'
);

$zip->addFromString(
    'docProps/app.xml',
    '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" '
    . 'xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">'
    . '<Application>ATS Export</Application>'
    . '<DocSecurity>0</DocSecurity>'
    . '<ScaleCrop>false</ScaleCrop>'
    . '<HeadingPairs><vt:vector size="2" baseType="variant">'
    . '<vt:variant><vt:lpstr>Worksheets</vt:lpstr></vt:variant>'
    . '<vt:variant><vt:i4>' . count($sheetNames) . '</vt:i4></vt:variant>'
    . '</vt:vector></HeadingPairs>'
    . '<TitlesOfParts><vt:vector size="' . count($sheetNames) . '" baseType="lpstr">'
    . implode('', array_map(static fn($name) => '<vt:lpstr>' . excel_xml($name) . '</vt:lpstr>', $sheetNames))
    . '</vt:vector></TitlesOfParts>'
    . '</Properties>'
);

$isoNow = gmdate('Y-m-d\TH:i:s\Z');
$zip->addFromString(
    'docProps/core.xml',
    '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" '
    . 'xmlns:dc="http://purl.org/dc/elements/1.1/" '
    . 'xmlns:dcterms="http://purl.org/dc/terms/" '
    . 'xmlns:dcmitype="http://purl.org/dc/dcmitype/" '
    . 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
    . '<dc:title>' . excel_xml($title !== '' ? $title : 'Document') . '</dc:title>'
    . '<dc:creator>ATS Export</dc:creator>'
    . '<cp:lastModifiedBy>ATS Export</cp:lastModifiedBy>'
    . '<dcterms:created xsi:type="dcterms:W3CDTF">' . $isoNow . '</dcterms:created>'
    . '<dcterms:modified xsi:type="dcterms:W3CDTF">' . $isoNow . '</dcterms:modified>'
    . '</cp:coreProperties>'
);

$zip->close();

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . rawurlencode($filename) . '.xlsx"');
header('Content-Length: ' . filesize($xlsxPath));
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
readfile($xlsxPath);
@unlink($xlsxPath);
exit;

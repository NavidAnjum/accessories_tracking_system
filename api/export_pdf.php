<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

function pdfFail(int $code, string $message): void
{
    http_response_code($code);
    header('Content-Type: text/plain; charset=UTF-8');
    echo $message;
    exit;
}

function pdfFilename(string $value): string
{
    $value = preg_replace('/[\\\\\/:*?"<>|]+/', '-', trim($value));
    $value = preg_replace('/\s+/', '-', $value);
    return trim($value, '-.') ?: 'PI';
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') pdfFail(405, 'Method not allowed.');
if (!function_exists('imagecreatefromstring')) pdfFail(500, 'PDF image support is unavailable.');

$rawPayload = file_get_contents('php://input') ?: '';
if ($rawPayload === '' && PHP_SAPI === 'cli') $rawPayload = file_get_contents('php://stdin') ?: '';
$payload = json_decode($rawPayload, true);
$pages = $payload['pages'] ?? null;
if (!is_array($pages) || !$pages) pdfFail(422, 'No PI pages were provided.');

$slices = [];
$a4Ratio = 297 / 210;
foreach ($pages as $page) {
    $src = is_array($page) ? (string)($page['src'] ?? '') : '';
    if (!preg_match('#^data:image/(?:png|jpe?g);base64,(.+)$#s', $src, $m)) {
        pdfFail(422, 'Invalid PI page image.');
    }
    $binary = base64_decode($m[1], true);
    $image = $binary !== false ? @imagecreatefromstring($binary) : false;
    if (!$image) pdfFail(422, 'Could not read a PI page image.');

    $width = imagesx($image);
    $height = imagesy($image);
    $pageHeight = max(1, (int)round($width * $a4Ratio));
    for ($top = 0; $top < $height; $top += $pageHeight) {
        $sliceHeight = min($pageHeight, $height - $top);
        $canvas = imagecreatetruecolor($width, $sliceHeight);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefill($canvas, 0, 0, $white);
        imagecopy($canvas, $image, 0, 0, 0, $top, $width, $sliceHeight);
        ob_start();
        imagejpeg($canvas, null, 92);
        $jpeg = ob_get_clean();
        imagedestroy($canvas);
        if ($jpeg === false) {
            imagedestroy($image);
            pdfFail(500, 'Could not encode a PI page.');
        }
        $slices[] = ['jpeg' => $jpeg, 'width' => $width, 'height' => $sliceHeight];
    }
    imagedestroy($image);
}

if (!$slices) pdfFail(422, 'The PI contained no printable pages.');

$pageWidth = 595.28;
$pageHeight = 841.89;
$objects = [];
$kids = [];
$objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';

foreach ($slices as $index => $slice) {
    $pageObject = 3 + ($index * 3);
    $imageObject = $pageObject + 1;
    $contentObject = $pageObject + 2;
    $imageName = 'Im' . ($index + 1);
    $kids[] = $pageObject . ' 0 R';

    $drawHeight = min($pageHeight, $pageWidth * ($slice['height'] / $slice['width']));
    $y = $pageHeight - $drawHeight;
    $content = sprintf("q %.2F 0 0 %.2F 0 %.2F cm /%s Do Q", $pageWidth, $drawHeight, $y, $imageName);

    $objects[$pageObject] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595.28 841.89] '
        . '/Resources << /XObject << /' . $imageName . ' ' . $imageObject . ' 0 R >> >> '
        . '/Contents ' . $contentObject . ' 0 R >>';
    $objects[$imageObject] = '<< /Type /XObject /Subtype /Image /Width ' . $slice['width']
        . ' /Height ' . $slice['height'] . ' /ColorSpace /DeviceRGB /BitsPerComponent 8 '
        . '/Filter /DCTDecode /Length ' . strlen($slice['jpeg']) . " >>\nstream\n"
        . $slice['jpeg'] . "\nendstream";
    $objects[$contentObject] = '<< /Length ' . strlen($content) . " >>\nstream\n" . $content . "\nendstream";
}

$objects[2] = '<< /Type /Pages /Count ' . count($kids) . ' /Kids [' . implode(' ', $kids) . '] >>';
ksort($objects);

$pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
$offsets = [0];
foreach ($objects as $number => $object) {
    $offsets[$number] = strlen($pdf);
    $pdf .= $number . " 0 obj\n" . $object . "\nendobj\n";
}
$xref = strlen($pdf);
$count = count($objects) + 1;
$pdf .= "xref\n0 {$count}\n0000000000 65535 f \n";
for ($i = 1; $i < $count; $i++) {
    $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
}
$pdf .= "trailer\n<< /Size {$count} /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";

$filename = pdfFilename((string)($payload['filename'] ?? 'PI')) . '.pdf';
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($pdf));
header('Cache-Control: no-store');
echo $pdf;

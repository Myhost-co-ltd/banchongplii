<?php
require __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);
$options->set('fontDir', __DIR__ . '/../storage/fonts');
$options->set('fontCache', __DIR__ . '/../storage/fonts/cache');
$options->set('tempDir', __DIR__ . '/../storage/fonts/cache');
$options->set('defaultFont', 'NotoSansThai');

$dompdf = new Dompdf($options);

$html = '<!doctype html><html><head><meta charset="utf-8"/>';
$html .= "<style>@font-face { font-family: 'NotoSansThai'; src: url('file:///" . str_replace('\\','/', realpath(__DIR__ . '/../storage/fonts/NotoSansThai-Regular.ttf')) . "') format('truetype'); }
body { font-family: 'NotoSansThai', sans-serif; }</style></head><body>";
$html .= "<h1>ทดสอบฟอนต์ไทย NotoSansThai</h1><p>สวัสดีครับ นี่คือการทดสอบฟอนต์ไทย</p>";
$html .= '</body></html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');

try {
    $dompdf->render();
    file_put_contents(__DIR__ . '/test_output.pdf', $dompdf->output());
    echo "PDF generated: tools/test_output.pdf\n";
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

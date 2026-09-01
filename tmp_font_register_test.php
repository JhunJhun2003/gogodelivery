<?php
require "vendor/autoload.php";
$dompdf = new Dompdf\Dompdf();
$fontPath = __DIR__ . "/public/fonts/NotoSansMyanmar-Regular.ttf";
$dompdf->getFontMetrics()->registerFont([
    "family" => "Noto Sans Myanmar",
    "style" => "normal",
    "weight" => "normal",
], $fontPath);
$html = <<<'HTML'
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><style>body { font-family: "Noto Sans Myanmar", sans-serif; font-size: 22px; }</style></head><body>မြန်မာ</body></html>
HTML;
$dompdf->loadHtml($html);
$dompdf->setPaper("A4", "landscape");
$dompdf->render();
file_put_contents("storage/app/test_myanmar_registered.pdf", $dompdf->output());
echo "REGISTERED_PDF_OK\n";
?>

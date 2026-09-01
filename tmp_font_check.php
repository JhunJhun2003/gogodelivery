<?php
require "vendor/autoload.php";
$html = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
@font-face {
  font-family: "Noto Sans Myanmar";
  src: url("file:///C:/Users/Admin/Desktop/no end/gogodelivery/public/fonts/NotoSansMyanmar-Regular.ttf") format("truetype");
}
body { font-family: "Noto Sans Myanmar", "DejaVu Sans", sans-serif; }
</style>
</head>
<body><div style="font-size:24px;">မြန်မာ</div></body>
</html>
HTML;
$dompdf = new Dompdf\Dompdf();
$dompdf->set_option("defaultFont", "Noto Sans Myanmar");
$dompdf->loadHtml($html);
$dompdf->setPaper("A4", "landscape");
$dompdf->render();
file_put_contents("storage/app/test_myanmar.pdf", $dompdf->output());
echo "PDF_OK\n";
?>

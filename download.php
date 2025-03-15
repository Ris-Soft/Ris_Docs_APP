<?php
if (!empty($_GET['url'])) {
    $filename = $_GET['url'];
} else {
    $filename = 'install.php';
}
$content = file_get_contents($filename);
// 设置强制下载的HTTP头
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Content-Length: " . strlen($content));
echo $content;
exit;
?>
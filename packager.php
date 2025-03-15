<?php
require('assets/function.php');
echo '欲打包版本:'.$localVersion.'<br>';
// 打包安装包
$excludeFiles = array('packager.php','RisDocs_Full.zip','RisDocs_Update.zip','download.php','installer.json');
$excludeDirs = array('.well-known');
packager($excludeFiles,$excludeDirs,'RisDocs_Full.zip');
// 打包升级包
$excludeFiles = array('packager.php','RisDocs_Full.zip','RisDocs_Update.zip','config.php','plugin.php','favicon.ico','download.php','installer.json');
$excludeDirs = array('.well-known','docs');
packager($excludeFiles,$excludeDirs,'RisDocs_Update.zip');
function packager($excludeFiles,$excludeDirs,$zipFileName) {
// 创建一个新的zip实例
$zip = new ZipArchive();

// 打开或创建zip文件
if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    die ("无法创建或打开文件");
}

// 获取当前目录的绝对路径
$currentDir = realpath('.');

// 遍历当前目录下的所有文件
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($currentDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($files as $file) {
    // 获取文件的绝对路径
    $filePath = $file->getRealPath();

    // 检查文件是否在排除列表中
    if (!in_array($file->getFilename(), $excludeFiles)) {
        // 检查文件所在目录是否在排除列表中
        $excludeDir = false;
        foreach ($excludeDirs as $dir) {
            if (strpos($filePath, $currentDir . DIRECTORY_SEPARATOR . $dir) === 0) {
                $excludeDir = true;
                break;
            }
        }
        if (!$excludeDir) {
            // 计算文件的相对路径，确保不包含父目录
            $relativePath = substr($filePath, strlen($currentDir) + 1);

            // 如果是文件则添加到zip中
            if ($file->isFile()) {
                $zip->addFile($filePath, $relativePath);
            }
        }
    }
}

// 关闭zip
$zip->close();
echo "打包".$zipFileName."完成！<br>";
}
?>
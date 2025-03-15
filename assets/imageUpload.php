<?php

include './function.php';

if (!isset($_GET['password'])) {
    echo '未知的请求';
} elseif (!password_verify($_GET['password'], $AdminPassword)) {
    echo '密码错误';
}

$target_dir = "uploads/";
if (!is_dir(__DIR__.'/uploads')) {
    mkdir(__DIR__.'/uploads', 0755, true); // 添加第三个参数true以递归创建目录
}

// 获取当前时间的微秒部分
$microtime = microtime(true);
$timestamp = floor($microtime);
$micro = sprintf("%06d", ($microtime - $timestamp) * 1000000);

// 生成新的文件名
$newFileName = date('YmdHis', $timestamp) . '_' . $micro . '.' . pathinfo(basename($_FILES["editormd-image-file"]["name"]), PATHINFO_EXTENSION);

$target_file = $target_dir . $newFileName;
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

if (isset($_FILES["editormd-image-file"]) && is_uploaded_file($_FILES["editormd-image-file"]["tmp_name"])) {
    $check = getimagesize($_FILES["editormd-image-file"]["tmp_name"]);
    if ($check !== false) {
    } else {
        $message = "请上传图片文件";
        $uploadOk = 0;
    }
}

if ($_FILES["editormd-image-file"]["size"] > 1500000) { // 限制大小为 1500KB
    $message = "超出大小限制（1500KB）";
    $uploadOk = 0;
}

if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
    $message = "抱歉，仅支持以下格式：JPG, JPEG, PNG, GIF";
    $uploadOk = 0;
}

if ($uploadOk == 1) {
    if (move_uploaded_file($_FILES["editormd-image-file"]["tmp_name"], $target_file)) {
        $response = [
            'success' => 1,
            'message' => "图片上传成功",
            'url' => './assets/' . $target_file,
        ];
    } else {
        $message = "图片上传失败";
        $response = [
            'success' => 0,
            'message' => $message,
        ];
    }
} else {
    $response = [
        'success' => 0,
        'message' => $message,
    ];
}

header('Content-Type: application/json');
echo json_encode($response);
?>
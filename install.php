<?php

$extractPath = './';
$configFilePath = './assets/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'install') {
    
    $jsonUrl = 'https://docs.3r60.top/Project/installer.json';
    $jsonContent = file_get_contents($jsonUrl);
    $json = json_decode($jsonContent, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        die('JSON 解析错误');
    }
    
    $siteName = $_POST['siteName'];
    
    if (!file_exists($configFilePath)) {
        $adminPassword = $_POST['adminPassword'];
        $filesToDownload = $json['Full']['files'];
    } else {
        require($configFilePath);
        if (!password_verify($_POST['adminPassword'],$AdminPassword)) {
            echo '管理员密码错误';
            exit;
        }
    $filesToDownload = $json['Update']['files'];
    }

    foreach ($filesToDownload as $fileUrl) {
        $filename = basename($fileUrl);
        $directory = dirname($fileUrl);
        $filePath = $extractPath . $directory . '/' . $filename;

        // Create the directory if it does not exist
        if (!file_exists($extractPath . $directory) && $directory != '.') {
            mkdir($extractPath . $directory, 0755, true);
        }

        try {
            $file = fopen($filePath, "wb");
            if ($file) {
                $downloadUrl = 'https://docs.3r60.top/Project/download.php?url=' . rawurlencode($fileUrl);
                $ch = curl_init($downloadUrl);
                curl_setopt($ch, CURLOPT_FILE, $file);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_exec($ch);
                curl_close($ch);
                fclose($file);
            }
        } catch (\Exception $e) {
            echo "文件下载失败: " . $filename . ". 错误: " . $e->getMessage();
            exit;
        }
    }

    if (!file_exists($configFilePath)) {
        $configContent = "<?php\n\$AdminPassword = '" . password_hash($adminPassword, PASSWORD_DEFAULT) . "';\n\$WebName = '" . htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') . "';\n";
        file_put_contents($configFilePath, $configContent);
    }

    echo '若此处无其他提示则安装完成！点击确认自动跳转（若存在报错代码请发送至群询问）';
    exit;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Ris_Docs Installer</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://assets.3r60.top/v3/package.js"></script>
</head>
<body>
    <div class="main">
        <div class="text-center">
            <img src="https://docs.3r60.top/favicon.ico" height="60px" class="mt-20 mb-0">
            <h1 class="mb-4">Ris_Docs Installer</h1>
            <h2>欢迎使用瑞思文档</h2>
            <form id="installForm">
                <?php if (file_exists($configFilePath)) : ?>
                    <input type="hidden" name="siteName" class="textEditor textEditor-success" required>
                    <p>更新/重装模式</p>
                    <input type="password" name="adminPassword" class="textEditor textEditor-success" style="max-width:300px" placeholder="验证管理员密码" required>
                <?php else: ?>
                    <input type="text" name="siteName" class="textEditor textEditor-success" style="max-width:300px" placeholder="设定站点名称" required><br>
                    <input type="password" name="adminPassword" class="textEditor textEditor-success" style="max-width:300px" placeholder="设定管理员密码" required>
                <?php endif; ?>
                <br>
                <button type="button" class="btn btn-success btn-md btn-shadow mt-10" id="installButton">下载并安装</button>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            $('#installButton').click(function () {
                var siteName = $('input[name="siteName"]').val();
                var adminPassword = $('input[name="adminPassword"]').val();
                $('#installButton').text('安装中...');
                $.ajax({
                    url: 'install.php',
                    type: 'POST',
                    data: {
                        action: 'install',
                        siteName: siteName,
                        adminPassword: adminPassword
                    },
                    xhrFields: {
                        onprogress: function (e) {
                            if (e.lengthComputable) {
                                var percentComplete = Math.round((e.loaded / e.total) * 100);
                                $('#installButton').text(percentComplete + '% 安装中...');
                            }
                        }
                    },
                    success: function (response) {
                        alert(response);
                        location.href = "./index.php";
                    },
                    error: function () {
                        alert('请检查您的网络连接或稍后重试。');
                        $('#installButton').text('下载并安装').prop('disabled', false);
                    }
                }).always(function () {
                    $('#installButton').prop('disabled', false);
                });

                $('#installButton').prop('disabled', true);
            });
        });
    </script>
</body>
</html>
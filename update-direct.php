<head>
        <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<?php
function generateMDIndex($directory, $parentPath = '', $isRoot = false) {
    $mdContent = '';

    // 扫描目录
    $files = scandir($directory);

    // 用于保存子目录和文件
    $subdirectories = [];
    $subfiles = [];

    foreach($files as $file) {
        if ($file != '.' && $file != '..' && $file != 'home.md' && $file != 'index.md' && $file != 'img' && $file != 'directory-true.md' && $file != 'directory.md' && $file != '404.md') {
            $filePath = $directory.
            '/'.$file;
            if (is_dir($filePath)) {
                // 如果是文件夹，保存到子目录数组
                $subdirectories[] = $file;
            }
            elseif(pathinfo($file, PATHINFO_EXTENSION) == 'md') {
                // 如果是MD文件，保存到子文件数组
                $subfiles[] = $file;
            }
        }
    }

    // 处理首页
    if ($isRoot) {
        $mdContent .= "- [首页](./)\n";
    }

    // 处理子文件
    $fileWeights = [];
    $fileNames = [];

    foreach($subfiles as $file) {
        $articleName = pathinfo($file, PATHINFO_FILENAME);

        // 检查文件名中是否包含 {M=整数} 标记
        if (preg_match('/\{M=(\d+)\}/', $articleName, $matches)) {
            // 提取权重值
            $weight = intval($matches[1]);
            $fileWeights[$articleName] = $weight;
            $articleName = preg_replace('/\{M=\d+\}/', '', $articleName); // 移除 {M=整数}
        } else {
            $fileWeights[$articleName] = 0; // 如果没有权重标记，默认权重为0
        }

        $fileNames[$articleName] = $file;
    }

    // 根据权重对文件进行排序
    arsort($fileWeights);

    foreach($fileWeights as $articleName => $weight) {
        $file = $fileNames[$articleName];
        $articleLink = "./?article={$parentPath}".urlencode($articleName);
        // 生成链接
        $mdContent .= str_repeat("  ", count(explode("/", $parentPath)) - 1).
        "- [".preg_replace('/\{M=\d+\}/', '', $articleName).
        "]($articleLink)\n";
    }

    // 处理子目录
    foreach($subdirectories as $dir) {
        $mdContent .= str_repeat("  ", count(explode("/", $parentPath)) - 1).
        "- [$dir]({$parentPath}?article=".urlencode($dir).
        '/index'.
        ")\n";
        $mdContent .= generateMDIndex($directory.
            '/'.$dir, "{$parentPath}".urlencode($dir).
            '/');
    }

    return $mdContent;
}

// 指定目录路径
include 'config.php';
$directoryPath = DOCS_DIRECTORY;

// 检查页面是否接收到提交的密码
if (isset($_POST['password'])) {
    // 检查密码是否正确
    if ($_POST['password'] === $AdminPassword) {
        // 如果密码正确，则继续执行后续逻辑

        // 生成MD索引文件
        $mdIndex = generateMDIndex($directoryPath, '', true);

        // 将MD索引文件内容保存到一个文件
        file_put_contents($directoryPath.
            'directory-true.md', $mdIndex);

        echo "目录生成完成，已保存到 ".$directoryPath.
        "directory-true.md 文件。";
    } else {
        // 如果密码错误，则显示错误提示或跳转到错误页面
        echo "密码错误";
        echo "<form method='post'>";
        echo "<input type='password' name='password' placeholder='请输入密码' />";
        echo "<button type='submit'>提交</button>";
        echo "</form>";
        exit;
    }
} else {
    // 如果页面没有接收到密码提交，则显示密码输入框
    echo "输入管理员密码更新目录文件";
    echo "<form method='post'>";
    echo "<input type='password' name='password' placeholder='请输入密码' />";
    echo "<button type='submit'>提交</button>";
    echo "</form>";
}
?>

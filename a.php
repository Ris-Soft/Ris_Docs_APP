<?php
// 引入配置项
include './assets/function.php';

if (!empty($SecurityEntrance) && ($_GET['SE'] !== $SecurityEntrance && $_GET['SE'] !== $SecurityEntrance)) {
    echo '安全入口错误';
    exit;
}

session_start();

if (!empty($_GET['loginout']) && $_GET['loginout'] == "true") {
    $_SESSION['RD_Password'] = "";
}

if (isset($_POST['password'])) {
    $_SESSION['RD_Password'] = $_POST['password'];
}

// POST请求处理
if ($_SERVER['REQUEST_METHOD'] == "POST" && password_verify($_SESSION['RD_Password'], $AdminPassword)) {
    if (!empty($_POST['action']) && $_POST['action'] == "editSet") {
        foreach ($_POST as $key => $value) {
            if ($key !== 'action' && $key !== "AdminPassword") {
                addConfig($key, htmlspecialchars($value));
            }
            if ($key == "AdminPassword" && !empty($value) && isset($value)) {
                addConfig('AdminPassword', password_hash($value, PASSWORD_DEFAULT));
                header('location: ' . $_SERVER['HTTP_REFERER']);
            }
        }
        header("location: ./admin.php?mode=editSet&SE=" . $_POST['SecurityEntrance']);
        include './assets/config.php';
    } elseif (!empty($_POST['action']) && $_POST['action'] == "editPost") {
        $category = $_POST['category'];
        if ($_GET['article'] == '') {
            echo '$(document).ready(function() { setTimeout(createMessage("文章名称不可为空", "danger"), 100); });';
        } elseif ($_GET['article'] == 'newPost') {
            forceFilePutContents(DOCS_DIRECTORY . $category . '/' . $_POST['title'] . '.md', $_POST['content']);
            $docs = ['title' => $_POST['title'], 'content' => $_POST['content']];
        } else {
            $oldPath = explode('/', $_GET['article']);
            $oldCategory = count($oldPath) > 1 ? $oldPath[0] : 'default';
            $oldTitle = end($oldPath);
            
            if ($oldCategory != $category || $_POST['title'] !== $oldTitle) {
                $oldFilePath = DOCS_DIRECTORY . $_GET['article'] . '.md';
                $newFilePath = DOCS_DIRECTORY . $category . '/' . $_POST['title'] . '.md';
                rename($oldFilePath, $newFilePath);
                forceFilePutContents($newFilePath, $_POST['content']);
                header('location: ./admin.php?mode=editPost&article=' . $category . '/' . $_POST['title'] . '&SE=' . $_GET['SE']);
            } else {
                forceFilePutContents(DOCS_DIRECTORY . $_GET['article'] . '.md', $_POST['content']);
            }
            $docs = ['title' => $_POST['title'], 'content' => $_POST['content']];
        }
        includePlugin('docsPost', json_encode($docs));
    } elseif (!empty($_POST['action']) && $_POST['action'] == "deletePost") {
        $file = $_POST['file'];
        if (strrpos($file, '/index') === strlen($file) - strlen('/index')) {
            $file = substr($file, 0, strlen($file) - strlen('/index'));
        }
        $file = DOCS_DIRECTORY . $file;
        if (is_dir($file)) {
            deleteDirectory($file);
        } else {
            $file = DOCS_DIRECTORY . $_POST['file'] . ".md";
            if (file_exists($file)) {
                unlink($file);
            }
        }
    } elseif (!empty($_POST['action']) && $_POST['action'] == "editPluginSet") {
        foreach ($_POST as $key => $value) {
            if ($key !== 'action') {
                addConfig($key, $value, __DIR__ . '/assets/plugins/' . $_GET['plugin'] . '/config.php');
            }
        }
        header("location: ./admin.php?mode=plugins&plugin=" . $_GET['plugin'] . "&SE=" . $_POST['SecurityEntrance']);
        include __DIR__ . '/assets/plugins/' . $_GET['plugin'] . '/config.php';
    } elseif (!empty($_POST['action']) && $_POST['action'] == "installPlugin") {
        if (isset($_POST['url']) && isset($_POST['type']) && isset($_POST['id'])) {

            $url = urldecode($_POST["url"]);
            $type = $_POST['type'];
            $id = $_POST['id'];
            if (extension_loaded('zip')) {
                $targetDirectory = "./assets/plugins/$id/";
            } else {
                $targetDirectory = "./";
            }
            if (!file_exists($targetDirectory)) {
                mkdir($targetDirectory, 0777, true);
            }
            $fileName = basename($url);
            $filePath = $targetDirectory . $fileName;
            $ch = curl_init($url);
            $fp = fopen($filePath, 'w');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_exec($ch);

            if (curl_errno($ch)) {
                echo "<script>alert('cURL 错误: " . curl_error($ch) . "');</script>";
            }

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            fclose($fp);

            if ($httpCode == 200 && extension_loaded('zip')) {
                $zip = new ZipArchive;
                $res = $zip->open($filePath);
                if ($res === TRUE) {
                    $totalFiles = $zip->numFiles;
                    $extractedFiles = 0;
                    for ($i = 0; $i < $totalFiles; $i++) {
                        $zip->extractTo($targetDirectory, array($zip->getNameIndex($i)));
                        $extractedFiles++;
                        $extractProgress = ($extractedFiles / $totalFiles) * 100;
                        flush();
                        ob_flush();
                    }
                    $zip->close();
                } else {
                    echo "<script>alert('解压缩失败: " . $res . "');</script>";
                }
            } else {
                echo "<script>alert('文件下载失败，错误码$httpCode');</script>";
            }
        } else {
            echo "<script>alert('缺少必要的参数');</script>";
        }
    } elseif (!empty($_POST['action']) && $_POST['action'] == "uninstallPlugin") {
        deleteDirectory('./assets/plugins/' . $_POST['id']);
    }
}

?>

<!doctype html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Language" content="zh-CN">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo '后台管理 - ' . $WebName ?></title>
    <script src="https://assets.3r60.top/v3/package.js"></script>
    <link rel="stylesheet" href="./assets/style.css">
</head>

<body>
    <topbar data-homeUrl='.' data-showExpendButton='true'>
    </topbar>
    <main <?php echo !empty($_GET['mode']) && ($_GET['mode'] == "editPost" || $_GET['mode'] == "plugins") ? 'class="flex pb-0"' : ''; ?>>
        <?php if (!empty($_GET['mode']) && $_GET['mode'] == "editPost"): ?>
            <lead>
                <ul class="list" data-loadFromFile='false' data-changeTitle='false'>
                    <li><a href='./admin.php?mode=editPost&article=newPost&SE=<?php echo $_GET['SE'] ?>'><i
                                class="bi bi-plus-square"></i>&nbsp;新建文章</a></li>
                    <?php loadDirectory('admin'); ?>
                    <script>
                        $('.list a').on('contextmenu', function (event) {
                            event.preventDefault();
                            var urlParams = new URLSearchParams(this.href);
                            var articleValue = urlParams.get('article');
                            if (articleValue !== null) {
                                var file = decodeURIComponent(articleValue);
                            }
                            if (file == 'newPost' || file == 'home') {
                                createMessage('暂不支持删除此文件', 'danger');
                                return;
                            }
                            createDialog('confirm', 'danger', '删除“' + file + '”', '确认删除此文章吗，删除后不可恢复', function () {
                                var $form = $('<form>', {
                                    'action': '',
                                    'method': 'POST'
                                });
                                $form.append($('<input>', {
                                    'type': 'hidden',
                                    'name': 'action',
                                    'value': 'deletePost'
                                }));
                                $form.append($('<input>', {
                                    'type': 'hidden',
                                    'name': 'file',
                                    'value': file
                                }));

                                $('body').append($form);
                                $form.submit();
                            });
                        });
                        $('.list a').on('click', function (event) {
                            event.preventDefault();
                            history.pushState('', '', this.href);
                            fetchAndReplaceContent(this.href, 'title,content', 'title,content', () => {
                                refreshMarkdownContent();
                                setActiveLinkInList($('.list'));
                                setTimeout(() => {
                                    $('[name="fullscreen"]').parent().on('click', function () {
                                        var isFullscreen = $('[name="fullscreen"]').parent().hasClass('active');
                                        $('#md-content').css('z-index', isFullscreen ? 1000 : 1);
                                    });
                                }, 1000);
                            });
                        });
                    </script>
                </ul>
                <footer></footer>
            </lead>
        <?php elseif (!empty($_GET['mode']) && $_GET['mode'] == 'plugins'): ?>
            <lead>
                <ul class="list" data-loadFromFile='false' data-changeTitle='false'>
                    <li><a href='./admin.php?mode=plugins&SE=<?php echo $_GET['SE'] ?>#/'><i
                                class="bi bi-plugin"></i>&nbsp;本地插件</a></li>
                    <li><a href='./admin.php?mode=plugins&plugin=shop&SE=<?php echo $_GET['SE'] ?>'><i
                                class="bi bi-shop"></i>&nbsp;插件商店</a></li>
                    <?php
                    $baseDir = './assets/plugins';
                    $dirs = scandir($baseDir);
                    foreach ($dirs as $dir) {
                        if ($dir == '.' || $dir == '..') {
                            continue;
                        }
                        $subDir = $baseDir . '/' . $dir;
                        if (is_dir($subDir)) {
                            $infoFile = $subDir . '/info.json';
                            if (file_exists($infoFile)) {
                                $infoContent = file_get_contents($infoFile);
                                $infoData = json_decode($infoContent, true);
                                // 检查JSON是否有效
                                if ($infoData !== null) {
                                    echo '
                                <li><a href="./admin.php?mode=plugins&plugin=' . $dir . '&SE=' . $_GET['SE'] . '"><i class="' . (isset($infoData['pluginIcon']) ? $infoData['pluginIcon'] : 'bi bi-plugin') . '"></i>&nbsp;' . $infoData['pluginName'] . '-' . $infoData['pluginVersion'] . '</a></li>';
                                } else {
                                    echo "无法解析 $infoFile 中的 JSON 数据\n\n";
                                }
                            } else {
                                echo "在 $subDir 中找不到 info.json 文件\n\n";
                            }
                        }
                    }
                    ?>
                    <script>
                        $('.list a').on('click', function (event) {
                            event.preventDefault();
                            history.pushState('', '', this.href);
                            fetchAndReplaceContent(this.href, 'title,content', 'title,content', () => {
                                setActiveLinkInList($('.list'));
                            });
                        });
                    </script>
                </ul>
                <footer></footer>
            </lead>
        <?php endif; ?>
        <!-- 主要部分 -->
        <link rel="stylesheet" type="text/css" href="https://assets.3r60.top/other/editormd/css/editormd.css">
        <?php echo !empty($_GET['mode']) && ($_GET['mode'] == "editPost" || $_GET['mode'] == "plugins") ? '<content>' : ''; ?>
        <?php includePlugin('admin'); ?>
        <?php if (password_verify($_SESSION['RD_Password'], $AdminPassword)): ?>
            <?php if (isset($_GET['mode'])): ?>
                <?php if ($_GET['mode'] == "editSet"): ?>
                    <span class="text-center">
                        <br>
                        <h2 class="text-left m-0A" style="max-width: 650px;"><i class="bi bi-gear"></i>&nbsp;站点设置</h2>
                        <form method="POST" id="configForm" action="" class="text-left m-0A" style="max-width: 650px;">
                            <input type="hidden" name="action" value="editSet">
                            <label for="WebName"><br>网站名称:</label><br>
                            <input type="text" id="WebName" name="WebName" class="textEditor textEditor-success textEditor-maxWidth"
                                placeholder="请输入网站名称" value="<?php echo $WebName ?>">
                            <br>
                            <label for="copyRight">版权信息:</label><br>
                            <input type="text" id="copyRight" name="copyRight"
                                class="textEditor textEditor-success textEditor-maxWidth" placeholder="请输入版权信息"
                                value="<?php echo htmlspecialchars($copyRight) ?>" required>
                            <br>
                            <label for="AdminPassword">验证密码:</label><br>
                            <input type="password" id="AdminPassword" name="AdminPassword"
                                class="textEditor textEditor-success textEditor-maxWidth" placeholder="留空则不更改密码">
                            <br>
                            <label for="SecurityEntrance">安全入口:</label><br>
                            <input type="text" id="SecurityEntrance" name="SecurityEntrance"
                                class="textEditor textEditor-success textEditor-maxWidth" placeholder="请输入安全入口"
                                value="<?php echo $SecurityEntrance ?>">
                            <button type="button" class="btn btn-shadow btn-circle"
                                onclick="createDialog('alert','success','安全入口','若填写此项，进入admin.php时需附带?SE=安全密码的 GET参数')">?</button>
                            <br>
                            <label for="Rewrite">伪静态:</label><br>
                            <input type="text" id="Rewrite" name="Rewrite" class="textEditor textEditor-success textEditor-maxWidth"
                                placeholder="请输入伪静态开关状态" value="<?php echo $Rewrite ?>">
                            <button type="button" class="btn btn-shadow btn-circle"
                                onclick="createDialog('alert','success','伪静态设置','开启前请先设置网站伪静态，方法点击下方连接查看<br><a href=\'https://docs.3r60.top/Project/?article=伪静态使用方法\'>伪静态使用方法</a>')">?</button>
                            更改站点图标仅需替换favicon.ico文件即可
                            <br><br>
                            <button type="submit" class="btn btn-shadow btn-success btn-md">保存设置</button>
                        </form>
                        <script>
                            $('#configForm').on('submit', function (event) {
                                event.preventDefault();
                                const formData = formInputsToKeyPairs($(this));
                                console.log(formData);
                                fetchAndReplaceContent(
                                    window.location.href,
                                    'main',
                                    'main',
                                    null,
                                    formData
                                );
                            });
                        </script>
                    </span>
                <?php elseif ($_GET['mode'] == "editPost"): ?>
                    <h2><i class="bi bi-pen"></i>&nbsp;编辑文章</h2>
                    <?php if (empty($_GET['article'])): ?>
                        <h4>在左侧单击一个文章即可编辑</h4>
                    <?php else: ?>
                        <form id="passage" method="POST" action="">
                            <span class="flex items-baseline mb-10">
                                分类:&nbsp;<select name="category" class="textEditor textEditor-success" style="width:auto">
                                <?php
                                $categories = getCategories();
                                $currentCategory = 'default';
                                if ($_GET['article'] != 'newPost') {
                                    $path = explode('/', $_GET['article']);
                                    if (count($path) > 1) {
                                        $currentCategory = $path[0];
                                    }
                                }
                                foreach ($categories as $category) {
                                    echo '<option value="' . $category . '"' . 
                                         ($category == $currentCategory ? ' selected' : '') . 
                                         '>' . $category . '</option>';
                                }
                                ?>
                                </select>&nbsp;
                                标题:&nbsp;<input type="text" id="postTitle" name="title" class="textEditor textEditor-success w-a"
                                    style="flex:1" placeholder="请输入文章标题" required <?php 
                                    if ($_GET['article'] == 'newPost') {
                                        echo 'value="新建文章"';
                                    } else {
                                        $path = explode('/', $_GET['article']);
                                        echo 'value="' . end($path) . '"';
                                    }
                                    ?>>&nbsp;
                                <button type="submit" class="btn btn-shadow btn-success btn-md">提交更改</button>
                            </span>
                            <input type="hidden" name="action" value="editPost">
                            <div id="md-content">
                                <textarea id="content" name="content"><?php
                                if ($_GET['article'] == 'newPost') {
                                    echo '新建空文章';
                                } else {
                                    $filePath = DOCS_DIRECTORY . $_GET['article'] . '.md';
                                    if (file_exists($filePath)) {
                                        echo file_get_contents($filePath);
                                    } else {
                                        echo '文章不存在';
                                    }
                                }
                                ?></textarea>
                            </div>
                        </form>
                        <script src="https://assets.3r60.top/Jquery/jquery-3.5.1.js"></script>
                        <script src="https://assets.3r60.top/other/editormd/editormd.min.js"></script>
                        <script type="text/javascript">
                            var saving = false;
                            $('#passage').on('submit', function (event) {
                                event.preventDefault();
                                const formData = formInputsToKeyPairs($(this));
                                console.log(formData);
                                fetchAndReplaceContent(
                                    window.location.href,
                                    '#postTitle,.list',
                                    '#postTitle,.list',
                                    () => {
                                        const urlParams = new URLSearchParams(new URL(window.location.href).search);
                                        urlParams.set('article', $('#postTitle').val());
                                        history.pushState({}, '', window.location.href.split('?')[0] + '?' + urlParams.toString());
                                        setActiveLinkInList($('.list'));
                                    },
                                    formData
                                );
                                // refreshMarkdownContent();
                            });
                            var contentEditor = editormd("md-content", {
                                width: "100%",
                                height: 500,
                                syncScrolling: "single",
                                path: "https://assets.3r60.top/other/editormd/lib/",
                                imageUpload: true,  // 开启图片上传功能
                                imageFormats: ["jpg", "jpeg", "gif", "png", "bmp", "webp"],  // 支持的图片格式
                                imageUploadURL: "./assets/imageUpload.php?password=<?php echo $_SESSION['RD_Password'] ?>",  // 上传图片的服务端地址
                                toc: true,
                                tocm: true,
                                emoji: true,
                                htmlDecode: true,
                                taskList: true,
                                tex: true,
                                flowChart: true,
                                sequenceDiagram: true,
                                theme: getColorMode() ? 'dark' : 'default',
                                editorTheme: getColorMode() ? '3024-night' : '3024-day',
                                previewTheme: getColorMode() ? 'dark' : 'default'
                            });
                            function setupPasteImage() {
                                var editorElement = $('#' + contentEditor.id);
                                editorElement.on("paste", function (event) {
                                    handlePastedImage(event);
                                });
                            }

                            function handlePastedImage(event) {
                                // 检查粘贴板中的数据是否包含图片
                                var items = (event.originalEvent || event).clipboardData.items;
                                if (items) {
                                    for (var i = 0; i < items.length; i++) {
                                        var item = items[i];
                                        if (item.kind === "file" && item.type.match(/image\//)) {
                                            var blob = item.getAsFile();
                                            uploadImage(blob);
                                            return;
                                        }
                                    }
                                }
                            }

                            function uploadImage(file) {
                                var formData = new FormData();
                                formData.append("editormd-image-file", file);

                                fetch('./assets/imageUpload.php?password=<?php
                                echo $_SESSION['RD_Password'] ?>', {
                                    method: 'POST',
                                    body: formData
                                })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            var imageUrl = data.url;
                                            contentEditor.insertValue('![](' + imageUrl + ')');
                                        } else {
                                            console.error('图片上传失败:', data.message);
                                            createMessage('图片上传失败:', data.message, 'danger');
                                        }
                                    })
                                    .catch(error => {
                                        console.error('图片上传失败:', error);
                                        createMessage('图片上传失败:', data.message, 'danger');
                                    });
                            }
                            $(document).on('colorModeChanged', function (event, colorMode) {
                                if (contentEditor !== null) {
                                    if (colorMode == 'dark') {
                                        contentEditor.setTheme('dark');
                                        contentEditor.setEditorTheme('3024-night');
                                        contentEditor.setPreviewTheme('dark')
                                    } else {
                                        contentEditor.setTheme('default');
                                        contentEditor.setEditorTheme('3024-day');
                                        contentEditor.setPreviewTheme('default')
                                    }
                                }
                            });
                            setupPasteImage();
                            $(document).keydown(function (event) {
                                if ((event.ctrlKey || event.metaKey) && (event.keyCode == 83 || event.keyCode == 115)) {
                                    event.preventDefault();
                                    if (!saving) {
                                        $('#passage').submit();
                                        saving = false;
                                    }
                                }
                            });
                            $(document).ready(function () {
                                var editorContainer = $('#md-content');
                                var fullscreenButton = $('[name="fullscreen"]').parent();
                                fullscreenButton.on('click', function () {
                                    var isFullscreen = fullscreenButton.hasClass('active');
                                    editorContainer.css('z-index', isFullscreen ? 1000 : 1);
                                });
                            })
                        </script>
                    <?php endif; ?>
                <?php elseif ($_GET['mode'] == "plugins"): ?>
                    <?php if (empty($_GET['plugin'])): ?>
                        <h2><i class="bi bi-plugin"></i>&nbsp;本地插件</h2>
                        <?php
                        $baseDir = './assets/plugins';
                        $dirs = scandir($baseDir);
                        $api = "https://backend.3r60.top/project/risDocs/plugins/shop.json";
                        $remotePlugins = json_decode(file_get_contents($api), true);

                        foreach ($dirs as $dir) {
                            if ($dir == '.' || $dir == '..') {
                                continue;
                            }
                            $subDir = $baseDir . '/' . $dir;
                            if (is_dir($subDir)) {
                                $infoFile = $subDir . '/info.json';
                                if (file_exists($infoFile)) {
                                    $infoContent = file_get_contents($infoFile);
                                    $infoData = json_decode($infoContent, true);
                                    if ($infoData !== null) {

                                        // 查找远程插件信息
                                        $remotePlugin = null;
                                        foreach ($remotePlugins as $rp) {
                                            if ($rp['pluginID'] === $dir) {
                                                $remotePlugin = $rp;
                                                break;
                                            }
                                        }

                                        // 检查是否有更新
                                        $updateAvailable = false;
                                        if ($remotePlugin && version_compare($infoData['pluginVersion'], $remotePlugin['pluginVersion']) < 0) {
                                            $updateAvailable = true;
                                        }

                                        // 显示插件信息
                                        echo '
                                        <form id="localPlugin_' . $dir . '" action="" method="POST">
                                        <input type="hidden" name="action" value="uninstallPlugin">
                                        <input type="hidden" name="id" value="' . $dir . '">
                                        <card class="flex items-center">
                                         <i class="text-large ml-5 mr-20 ' . (isset($infoData['pluginIcon']) ? $infoData['pluginIcon'] : 'bi bi-plugin') . '"></i>
                                            <span class="flex flex-column" style="flex:1">
                                            <span class="text-large">' . $infoData['pluginName'] . '</span>
                                            <span class="text-small">' . $infoData['pluginDescribe'] . '</span>
                                            </span><span class="flex flex-column">
                                            <span class="text-small">作者：' . $infoData['pluginAuthor'] . '</span>
                                            <span class="text-small">版本：' . $infoData['pluginVersion'];

                                        if ($updateAvailable) {
                                            echo ' (有更新：' . $remotePlugin['pluginVersion'] . ')';
                                        }

                                        echo '</span>
                                            </span>
                                            <div>
                                            <button type="submit" class="btn btn-shadow btn-danger btn-md ml-20">卸载</button><a type="button" href="./admin.php?mode=plugins&plugin=' . $dir . '&SE=" class="btn btn-shadow btn-primary btn-md ml-10">配置</a>';
                                        if ($updateAvailable) {
                                            echo '<a type="button" href="./admin.php?mode=plugins&plugin=shop" class="btn btn-shadow btn-warning btn-md ml-10">升级</a>';
                                        }

                                        echo '</div>
                                        </card>
                                        </form>';

                                        echo "<script>
                                        $('#localPlugin_" . $dir . "').on('submit', function(event) {
                                    event.preventDefault();
                                    const formData = formInputsToKeyPairs($(this));
                                    console.log(formData);
                                    fetchAndReplaceContent(
                                        window.location.href,
                                        'main',
                                        'main',
                                        null,
                                        formData
                                    );
                                    refreshMarkdownContent();
                                });</script>";
                                    } else {
                                        echo "无法解析 $infoFile 中的 JSON 数据\n\n";
                                    }
                                } else {
                                    echo "在 $subDir 中找不到 info.json 文件\n\n";
                                }
                            }
                        }
                        ?>
                    <?php elseif ($_GET['plugin'] == 'shop'): ?>
                        <h2><i class="bi bi-shop"></i>&nbsp;插件商店</h2>
                        <?php
                        if (version_compare(PHP_VERSION, '8.0.0', '>=') && !extension_loaded('zip')) {
                            echo "尊敬的 PHP " . PHP_VERSION . " 用户，您未安装zip扩展，插件zip将会下载到主目录，请手动解压他们";
                        }
                        $baseDir = './assets/plugins';
                        $dirs = scandir($baseDir);
                        $localPlugins = [];

                        // 读取本地插件信息
                        foreach ($dirs as $dir) {
                            if ($dir == '.' || $dir == '..') {
                                continue;
                            }
                            $subDir = $baseDir . '/' . $dir;
                            if (is_dir($subDir)) {
                                $infoFile = $subDir . '/info.json';
                                if (file_exists($infoFile)) {
                                    $infoContent = file_get_contents($infoFile);
                                    $infoData = json_decode($infoContent, true);
                                    if ($infoData !== null) {
                                        $localPlugins[$dir] = $infoData;
                                    } else {
                                        echo "无法解析 $infoFile 中的 JSON 数据\n\n";
                                    }
                                } else {
                                    echo "在 $subDir 中找不到 info.json 文件\n\n";
                                }
                            }
                        }

                        $api = "https://backend.3r60.top/project/risDocs/plugins/shop.json";
                        $plugins = json_decode(file_get_contents($api), true);

                        if (isset($plugins)) {
                            foreach ($plugins as $plugin) {
                                $pluginLocalVersion = isset($localPlugins[$plugin['pluginID']]['pluginVersion']) ? $localPlugins[$plugin['pluginID']]['pluginVersion'] : null;

                                // 比较版本
                                $versionInfo = '';
                                $buttonText = '';
                                $buttonClass = '';

                                if ($pluginLocalVersion === null) { // 插件未安装
                                    $versionInfo = "版本：{$plugin['pluginVersion']}";
                                    $buttonText = '安装';
                                    $buttonClass = 'btn-success';
                                } elseif (version_compare($pluginLocalVersion, $plugin['pluginVersion']) < 0) { // 需要升级
                                    $versionInfo = "版本：$pluginLocalVersion =>{$plugin['pluginVersion']}";
                                    $buttonText = '升级';
                                    $buttonClass = 'btn-warning';
                                } else { // 版本一致
                                    $versionInfo = "版本：{$plugin['pluginVersion']}";
                                    $buttonText = '重装';
                                    $buttonClass = 'btn-danger';
                                }

                                echo '
                                <form id="onlinePlugin_' . $plugin['pluginID'] . '" action="" method="POST">
                                <input type="hidden" name="action" value="installPlugin">
                                <input type="hidden" name="type" value="plugin">
                                <input type="hidden" name="url" value="' . $plugin['downloadURL'] . '">
                                <input type="hidden" name="id" value="' . $plugin['pluginID'] . '">
                                <card class="flex items-center">
                                 <i class="text-large ml-5 mr-20 ' . $plugin['pluginIcon'] . '"></i>
                                    <span class="flex flex-column" style="flex:1">
                                    <span class="text-large">' . $plugin['pluginName'] . '</span>
                                    <span class="text-small">' . $plugin['pluginDescribe'] . '</span>
                                    </span><span class="flex flex-column">
                                    <span class="text-small">作者：' . $plugin['pluginAuthor'] . '</span>
                                    <span class="text-small">' . $versionInfo . '</span>
                                    </span>
                                    <button class="btn btn-shadow ' . $buttonClass . ' btn-md ml-15">' . $buttonText . '</button>
                                </card>
                                </form>';
                                echo "<script>
                                        $('#onlinePlugin_" . $plugin['pluginID'] . "').on('submit', function(event) {
                                    event.preventDefault();
                                    const formData = formInputsToKeyPairs($(this));
                                    console.log(formData);
                                    fetchAndReplaceContent(
                                        window.location.href,
                                        'main',
                                        'main',
                                        null,
                                        formData
                                    );
                                    refreshMarkdownContent();
                                });</script>";
                            }
                        } else {
                            echo "<b>这里什么也没有</b>";
                        }
                        ?>
                    <?php else: ?>
                        <?php
                        $infoFileIndex = "./assets/plugins/" . $_GET['plugin'] . '/info.json';
                        $infoContentIndex = file_get_contents($infoFileIndex);
                        $infoDataIndex = json_decode($infoContentIndex, true);
                        ?>
                        <h2><i
                                class="<?php echo $infoDataIndex['pluginIcon'] ?? 'bi bi-plugin' ?>"></i>&nbsp;<?php echo $infoDataIndex['pluginName'] ?>
                        </h2>
                        <form action="" method="POST">
                            <input type="hidden" name="action" value="editPluginSet">
                            <?php
                            $setFileIndex = "./assets/plugins/" . $_GET['plugin'] . '/set.json';
                            $setContentIndex = file_get_contents($setFileIndex);
                            $setData = json_decode($setContentIndex, true);
                            @include "./assets/plugins/" . $_GET['plugin'] . '/config.php';
                            foreach ($setData as $item) {
                                $name = $item['name'];
                                switch ($item['type']) {
                                    case 'textinput':
                                        echo '<label for="' . $item['name'] . '">' . $item['label'] . ':</label>';
                                        echo '<input name="' . $item['name'] . '" type="text" class="textEditor textEditor-' . ($item['theme'] ?? 'success') . '" value="' . $$name . '" placeholder="' . $item['show']['placeholder'] . '">';
                                        break;
                                    case 'textarea':
                                        echo '<label for="' . $item['name'] . '">' . $item['label'] . ':</label>';
                                        echo '<textarea name="' . $item['name'] . '" class="textEditor textEditor-' . ($item['theme'] ?? 'success') . '" placeholder="' . $item['show']['placeholder'] . '">' . $$name . '</textarea>';
                                        break;
                                    case 'select':

                                        echo '<label for="' . $item['name'] . '">' . $item['label'] . ':</label>';
                                        echo '<select name="' . $item['name'] . '" class="textEditor textEditor-' . ($item['theme'] ?? 'success') . '">';
                                        foreach ($item['show'] as $option) {
                                            if ($$name == $option['value']) {
                                                echo '<option value="' . $option['value'] . '" selected>' . $option['text'] . '</option>';
                                            } else {
                                                echo '<option value="' . $option['value'] . '">' . $option['text'] . '</option>';
                                            }
                                        }
                                        echo '</select>';
                                        break;
                                    case 'checkbox':
                                        $checked = ($$name == 'on') ? 'checked' : '';
                                        echo '<input type="hidden" name="' . $item['name'] . '" value="off">';
                                        echo '<div class="checkbox-container">';
                                        echo '<input type="checkbox" name="' . $item['name'] . '" id="' . $item['name'] . '"' . $checked . '>';
                                        echo '<label for="' . $item['name'] . '">' . $item['show']['text'] . '</label>';
                                        echo '</div>';
                                        break;
                                    case 'text':
                                        echo '<p>';
                                        echo $item['text'];
                                        echo '</p>';
                                        break;
                                }
                            }
                            echo '<br><button type="submit" class="btn btn-shadow btn-success btn-md">保存</button></form>';
                            ?>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php else: ?>
                    <index>
                        <h1><?php echo $WebName ?></h1>
                        <h3>后台管理</h3>
                    </index>
                    <footer></footer>
                <?php endif; ?>
            <?php else: ?>
                <div id="main-display">
                    <div class="moudle" style="width:320px;margin:auto">
                        <form method="POST" action="" name="AdminAuth">
                            <div class="dialog-overlay"></div>
                            <div class="dialog-box" style="border-top-color: var(--color-primary);">
                                <div class="dialog-header">
                                    <h3>验证您的身份</h3>
                                </div>
                                <div class="dialog-body">
                                    <p>请输入密码</p>
                                </div>
                                <div class="dialog-footer">
                                    <input type="password" id="password" class="textEditor" name="password" placeholder="密码"
                                        value="<?php echo $_SESSION['RD_Password'] ?>"><br>
                                    <button class="btn btn-primary btn-md">登录</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            <? endif; ?>
            <?php echo !empty($_GET['mode']) && ($_GET['mode'] == "editPost" || $_GET['mode'] == "plugins") ? '</content>' : '' ?>
            <?php if (!empty($_GET['mode']) && $_GET['mode'] == 'editPost'): ?>
            <?php elseif (!empty($_GET['mode']) && $_GET['mode'] == 'plugins'): ?>
                <script>
                    $('.onlinePlugin').on('submit', function (event) {
                        event.preventDefault();
                        const formData = formInputsToKeyPairs($(this));
                        console.log(formData);
                        fetchAndReplaceContent(
                            window.location.href,
                            'main',
                            'main',
                            null,
                            formData
                        );
                        refreshMarkdownContent();
                    });
                </script>
            <?php endif; ?>
    </main>
    <script>
        let defaultTitle = '<?php echo $WebName ?>';
        let defaultNavItems = [{
            text: '<i class="bi bi-house"></i>&nbsp;管理中心',
            href: 'admin.php?SE=<?php echo $_GET['SE'] ?? null ?>#'
        },
        {
            text: '<i class="bi bi-gear"></i>&nbsp;站点设置',
            href: 'admin.php?mode=editSet&SE=<?php echo $_GET['SE'] ?? null ?>#'
        },
        {
            text: '<i class="bi bi-pen"></i>&nbsp;编辑文章',
            href: 'admin.php?mode=editPost&SE=<?php echo $_GET['SE'] ?? null ?>#'
        },
        {
            text: '<i class="bi bi-plugin"></i>&nbsp;插件管理',
            href: 'admin.php?mode=plugins&SE=<?php echo $_GET['SE'] ?? null ?>#'
        },
        {
            text: '<i class="bi bi-box-arrow-right"></i>&nbsp;退出登录',
            href: 'admin.php?loginout=true&SE=<?php echo $_GET['SE'] ?? null ?>#'
        }
        ];
        let defaultNavRightItems = [{
            "text": "<i class=\"bi bi-info-circle\"></i>",
            "href": `javascript:createDialog(\"alert\", \"primary\", \"关于<?php echo addslashes($WebName) ?>\", \"<?php echo $copyRight ? str_replace('"', "'", htmlspecialchars_decode($copyRight)) . '<br>' : '' ?>软件版本：<?php echo $localVersion; ?><br><a href=\\\"https://docs.3r60.top/Project\\\">Ris_Docs</a>提供软件服务\")`
        }];
        let defaultFooterLinks = [];
        let defaultCopyright = `<?php echo $copyRight ? htmlspecialchars_decode($copyRight) : '版权所有 © 2024 腾瑞思智' ?>`;
        let webTitle = '<?php echo addslashes($WebName) ?? '瑞思文档' ?>';
        $(document).ready(function () {
            setTimeout('<?php
            if ($_SESSION['RD_Password'] == '' || empty($_SESSION['RD_Password']) || !isset($_SESSION['RD_Password'])) {
                echo 'createMessage("请验证身份后继续","")';
            } elseif (!password_verify($_SESSION['RD_Password'], $AdminPassword)) {
                echo 'createMessage("密码错误","danger")';
            } elseif (!empty($_GET['mode']) && $_GET['mode'] !== 'editPost') {
                echo 'createMessage("欢迎回来，Admin","success")';
            }
            $vers = file_get_contents("https://app.3r60.top/assets/projects.json");
            $projects = json_decode($vers);
            $ver = $projects->Ris_Docs->version;
            $update = str_replace(PHP_EOL, '<br>', $projects->Ris_Docs->update->{$ver}->changeLog);
            if ($ver !== $localVersion && ($_GET['mode'] ?? null !== 'editPost')) {
                echo ';createDialog("confirm","success","版本更新","' . $localVersion . '=>' . $ver . '<br>更新日志:' . $update . '<br>点击确定前往更新",()=>{location.href="./install.php"})';
            }
            ?>', 500)
        })

        function formInputsToKeyPairs(formElement) {
            const formData = new FormData(formElement[0]);
            const dataObject = {};

            for (let [key, value] of formData.entries()) {
                dataObject[key] = value;
            }

            return dataObject;
        }

        function refreshMarkdownContent() {
            contentEditor = editormd("md-content", {
                width: "100%",
                height: 500,
                syncScrolling: "single",
                path: "https://assets.3r60.top/other/editormd/lib/",
                htmlDecode: true,
                toc: true,
                imageUpload: true,  // 开启图片上传功能
                imageFormats: ["jpg", "jpeg", "gif", "png", "bmp", "webp"],  // 支持的图片格式
                imageUploadURL: "./assets/imageUpload.php?password=<?php echo $_SESSION['RD_Password'] ?>",  // 上传图片的服务端地址
                tocm: true,
                emoji: true,
                taskList: true,
                tex: true,
                flowChart: true,
                sequenceDiagram: true,
            });
            saving = false;
        }
    </script>
    <script src="https://assets.3r60.top/other/editormd/lib/marked.min.js"></script>
</body>

</html>
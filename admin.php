<?php

// 引入配置项
include './assets/function.php';

if (!empty($SecurityEntrance) && ($_GET['SE'] !== $SecurityEntrance && $_GET['SE'] !== $SecurityEntrance)) {
  echo '安全入口错误';
  exit;
}

session_start();

if ($_GET['loginout'] == "true") {
  $_SESSION['RD_Password'] = "";
}

if (isset($_POST['password'])) {
  $_SESSION['RD_Password'] = $_POST['password'];
}

// POST请求处理
if ($_SERVER['REQUEST_METHOD'] == "POST" && $_SESSION['RD_Password'] == $AdminPassword) {
  if ($_POST['action'] == "editSet") {
    foreach ($_POST as $key => $value) {
      if ($key !== 'action') {
        addConfig($key, $value);
      }
    }
    header("location: ./admin.php?mode=editSet&SE=" . $_POST['SecurityEntrance']);
    include './assets/config.php';
  } elseif ($_POST['action'] == "editPost") {
    if ($_GET['article'] == '') {
      echo '$(document).ready(function() {setTimeout(createMessage("文章名称不可为空","danger"), 100 )';
    } elseif ($_GET['article'] == 'newPost') {
      forceFilePutContents(DOCS_DIRECTORY . $_POST['title'] . '.md', $_POST['content']);
      $docs = ['title'=>$_POST['title'],'content'=>$_POST['content']];
    } else {
      forceFilePutContents(DOCS_DIRECTORY . $_GET['article'] . '.md', $_POST['content']);
      $docs = ['title'=>$_POST['title'],'content'=>$_POST['content']];
    }
    includePlugin('docsPost',json_encode($docs));
  } elseif ($_POST['action'] == "deletePost") {
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
  } elseif ($_POST['action'] == "editPluginSet") {
    foreach ($_POST as $key => $value) {
      if ($key !== 'action') {
        addConfig($key, $value, __DIR__ . '/assets/plugins/' . $_GET['plugin'] . '/config.php');
      }
    }
    header("location: ./admin.php?mode=plugins&plugin=" . $_GET['plugin'] . "&SE=" . $_POST['SecurityEntrance']);
    include  __DIR__ . '/assets/plugins/' . $_GET['plugin'] . '/config.php';
  } elseif ($_POST['action'] == "installPlugin") {
    if (isset($_POST['url']) && isset($_POST['type']) && isset($_POST['id'])) {

      $url = urldecode($_POST["url"]);
      $type = $_POST['type'];
      $id = $_POST['id'];
      $targetDirectory = "./assets/plugins/$id/";
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

      if ($httpCode == 200) {
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
  } elseif ($_POST['action'] == "uninstallPlugin") {
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
  <topbar data-title='<?php echo $WebName ?>' data-homeUrl='.' data-showExpendButton='true'>
  </topbar>
  <main <?php echo $_GET['mode'] == "editPost" || $_GET['mode'] == "plugins" ? 'class="flex pb-0"' : ''; ?>>
    <?php if ($_GET['mode'] == "editPost") : ?>
      <lead>
        <ul class="list" data-loadFromFile='false' data-changeTitle='false'>
          <li><a href='./admin.php?mode=editPost&article=newPost&SE=<?php echo $_GET['SE'] ?>'><i class="bi bi-plus-square"></i>&nbsp;新建文章</a></li>
          <?php loadDirectory('admin'); ?>
        </ul>
      </lead>
    <?php elseif ($_GET['mode'] == 'plugins') : ?>
      <lead>
        <ul class="list" data-loadFromFile='false' data-changeTitle='false'>
          <li><a href='./admin.php?mode=plugins&SE=<?php echo $_GET['SE'] ?>#/'><i class="bi bi-plugin"></i>&nbsp;本地插件</a></li>
          <li><a href='./admin.php?mode=plugins&plugin=shop&SE=<?php echo $_GET['SE'] ?>'><i class="bi bi-shop"></i>&nbsp;插件商店</a></li>
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
        </ul>
      </lead>
    <?php endif; ?>
    <!-- 主要部分 -->
    <link rel="stylesheet" type="text/css" href="./assets/editormd/css/editormd.css">
    <?php echo $_GET['mode'] == "editPost" || $_GET['mode'] == "plugins" ? '<content>' : ''; ?>
    <?php if ($_SESSION['RD_Password'] == $AdminPassword) : ?>
      <?php if (isset($_GET['mode'])) : ?>
        <?php if ($_GET['mode'] == "editSet") : ?>
          <span class="text-center">
            <br>
            <h2 class="text-left m-0A" style="max-width: 650px;"><i class="bi bi-gear"></i>&nbsp;站点设置</h2>
            <form method="POST" action="" class="text-left m-0A" style="max-width: 650px;">
              <input type="hidden" name="action" value="editSet">
              <label for="WebName"><br>网站名称:</label><br>
              <input type="text" id="WebName" name="WebName" class="textEditor textEditor-success textEditor-maxWidth" placeholder="请输入网站名称" value="<?php echo $WebName ?>">
              <br>
              <label for="copyRight">版权信息:</label><br>
              <input type="text" id="copyRight" name="copyRight" class="textEditor textEditor-success textEditor-maxWidth" placeholder="请输入版权信息" value="<?php echo $copyRight ?>" required>
              <br>
              <label for="AdminPassword">验证密码:</label><br>
              <input type="password" id="AdminPassword" name="AdminPassword" class="textEditor textEditor-success textEditor-maxWidth" placeholder="请输入管理员密码" value="<?php echo $AdminPassword ?>" required>
              <br>
              <label for="SecurityEntrance">安全入口:</label><br>
              <input type="text" id="SecurityEntrance" name="SecurityEntrance" class="textEditor textEditor-success textEditor-maxWidth" placeholder="请输入安全入口" value="<?php echo $SecurityEntrance ?>">
              <button type="button" class="btn btn-shadow btn-circle" onclick="createDialog('alert','success','安全入口','若填写此项，进入admin.php时需附带?SE=安全密码的 GET参数')">?</button>
              <br>
              <label for="Rewrite">伪静态:</label><br>
              <input type="text" id="Rewrite" name="Rewrite" class="textEditor textEditor-success textEditor-maxWidth" placeholder="请输入伪静态开关状态" value="<?php echo $Rewrite ?>">
              <button type="button" class="btn btn-shadow btn-circle" onclick="createDialog('alert','success','伪静态设置','开启前请先设置网站伪静态，方法点击下方连接查看<br><a href=\'https://docs.3r60.top/Project/?article=伪静态使用方法\'>伪静态使用方法</a>')">?</button>
              <br>
              <button type="submit" class="btn btn-shadow btn-success btn-md">保存设置</button>
            </form>
          </span>
        <?php elseif ($_GET['mode'] == "editPost") : ?>
          <h2><i class="bi bi-pen"></i>&nbsp;编辑文章</h2>
          <script>
            $(document).ready(function() {
              $('.list a').on('contextmenu', function(event) {
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
                createDialog('confirm', 'danger', '删除“' + file + '”', '确认删除此文章吗，删除后不可恢复', function() {
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
            })
          </script>
          <?php if (empty($_GET['article'])) : ?>
            <h4>在左侧单击一个文章即可编辑</h4>
          <?php else : ?>
            <form id="passage" method="POST" action="">
              <span class="flex items-baseline mb-10">
                标题:&nbsp;<input type="text" id="title" name="title" class="textEditor textEditor-success w-a" style="flex:1" placeholder="请输入文章标题" required <?php echo $_GET['article'] == 'newPost' ? 'value="新建文章"' : 'disabled value=' . $_GET['article'] ?>>&nbsp;
                <button type="submit" class="btn btn-shadow btn-success btn-md">提交更改</button>
              </span>
              <input type="hidden" name="action" value="editPost">
              <div id="md-content" style="z-index: 1000 !important">
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
            <script src="./assets/editormd/editormd.min.js"></script>
            <script type="text/javascript">
              var contentEditor;
              $(function() {
                contentEditor = editormd("md-content", {
                  width: "100%",
                  height: 500,
                  syncScrolling: "single",
                  path: "./assets/editormd/lib/",
                  htmlDecode: true,
                  toc: true,
                  tocm: true,
                  emoji: true,
                  taskList: true,
                  tex: true,
                  flowChart: true,
                  sequenceDiagram: true,
                });
              });
              $(document).ready(function() {
                setTimeout(() => {
                  if (getCookie('set_colorMode') === 'dark') {
                    contentEditor.setTheme('dark');
                    contentEditor.setEditorTheme('3024-night');
                    contentEditor.setPreviewTheme('dark');
                  } else if (getCookie('set_colorMode') === 'light') {
                    contentEditor.setTheme('default');
                    contentEditor.setEditorTheme('3024-day');
                    contentEditor.setPreviewTheme('default');
                  } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    contentEditor.setTheme('dark');
                    contentEditor.setEditorTheme('3024-night');
                    contentEditor.setPreviewTheme('dark');
                  } else {
                    contentEditor.setTheme('default');
                    contentEditor.setEditorTheme('3024-day');
                    contentEditor.setPreviewTheme('default');
                  }
                }, 100)
                $(document).keydown(function(event) {
                  if ((event.ctrlKey || event.metaKey) && (event.keyCode == 83 || event.keyCode == 115)) {
                    event.preventDefault();
                    $('#passage').submit();
                  }
                });
              })
              window.matchMedia('(prefers-color-scheme: dark)').addListener((e) => {
                if (e.matches && getCookie('set_colorMode') !== 'dark' && getCookie('set_colorMode') !== 'light') {
                  contentEditor.setTheme('dark');
                  contentEditor.setEditorTheme('3024-night');
                  contentEditor.setPreviewTheme('dark');
                } else {
                  contentEditor.setTheme('default');
                  contentEditor.setEditorTheme('3024-day');
                  contentEditor.setPreviewTheme('default');
                }
              });
            </script>
          <?php endif; ?>
        <?php elseif ($_GET['mode'] == "plugins") : ?>
          <?php if (empty($_GET['plugin'])) : ?>
            <h2><i class="bi bi-plugin"></i>&nbsp;本地插件</h2>
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
                  if ($infoData !== null) {
                    echo '
                <form action="" method="POST">
                <input type="hidden" name="action" value="uninstallPlugin">
                <input type="hidden" name="id" value="' . $dir . '">
                <card class="flex items-center">
                 <i class="text-large ml-5 mr-20 ' . (isset($infoData['pluginIcon']) ? $infoData['pluginIcon'] : 'bi bi-plugin') . '"></i>
                    <span class="flex flex-column" style="flex:1">
                    <span class="text-large">' . $infoData['pluginName'] . '</span>
                    <span class="text-small">' . $infoData['pluginDescribe'] . '</span>
                    </span><span class="flex flex-column">
                    <span class="text-small">作者：' . $infoData['pluginAuthor'] . '</span>
                    <span class="text-small">版本：' . $infoData['pluginVersion'] . '</span>
                    </span>
                    <button type="submit" class="btn btn-shadow btn-danger btn-md ml-20">卸载</button>&nbsp;
                    <a type="button" href="https://docs.3r60.top/Project/admin.php?mode=plugins&plugin=' . $dir . '&SE=" class="btn btn-shadow btn-primary btn-md">配置</a>
              </card>
              </form>';
                  } else {
                    echo "无法解析 $infoFile 中的 JSON 数据\n\n";
                  }
                } else {
                  echo "在 $subDir 中找不到 info.json 文件\n\n";
                }
              }
            }
            ?>
          <?php elseif ($_GET['plugin'] == 'shop') : ?>
            <h2><i class="bi bi-shop"></i>&nbsp;插件商店</h2>
            <?php
            $baseDir = './assets/plugins';
            $dirs = scandir($baseDir);
            $Plugindata = array();
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
                    array_push($Plugindata, $dir);
                  } else {
                    echo "无法解析 $infoFile 中的 JSON 数据\n\n";
                  }
                } else {
                  echo "在 $subDir 中找不到 info.json 文件\n\n";
                }
              }
            }
            $api = "https://backend.3r60.top/project/risDocs/plugins/shop.json";
            @$plugins = json_decode(file_get_contents($api), true);
            if (isset($plugins)) {
              foreach ($plugins as $plugin) {
                echo '
                <form action="" method="POST">
                <input type="hidden" name="action" value="installPlugin">
                <input type="hidden" name="type" value="plugin">
                <input type="hidden" name="url" value="' . $plugin['downloadURL'] . '">
                <input type="hidden" name="id" value="' . $plugin['pluginID'] . '">
                <card class="flex items-center">
                 <i class="text-large ml-5 mr-20 ' . (isset($plugin['pluginIcon']) ? $plugin['pluginIcon'] : 'bi bi-plugin') . '"></i>
                    <span class="flex flex-column" style="flex:1">
                    <span class="text-large">' . $plugin['pluginName'] . '</span>
                    <span class="text-small">' . $plugin['pluginDescribe'] . '</span>
                    </span><span class="flex flex-column">
                    <span class="text-small">作者：' . $plugin['pluginAuthor'] . '</span>
                    <span class="text-small">版本：' . $plugin['pluginVersion'] . '</span>
                    </span>
                    <button ' . (in_array($plugin['pluginID'], $Plugindata) ? "class=\"btn btn-shadow btn-danger btn-md ml-15\">重装" : "class=\"btn btn-shadow btn-success btn-md ml-15\">安装") . ' </button>
              </card>
              </form>';
              }
            } else {
              echo "<b>这里什么也没有</b>";
            }
            ?>
          <?php else : ?>
            <?php
            $infoFileIndex = "./assets/plugins/" . $_GET['plugin'] . '/info.json';
            $infoContentIndex = file_get_contents($infoFileIndex);
            $infoDataIndex = json_decode($infoContentIndex, true);
            ?>
            <h2><?php echo $infoDataIndex['pluginIcon'] ?? '<i class="bi bi-plugin"></i>' ?>&nbsp;<?php echo $infoDataIndex['pluginName'] ?></h2>
            <form action="" method="POST">
              <input type="hidden" name="action" value="editPluginSet">
              <?php
              $setFileIndex = "./assets/plugins/" . $_GET['plugin'] . '/set.json';
              $setContentIndex = file_get_contents($setFileIndex);
              $setData = json_decode($setContentIndex, true);
              require "./assets/plugins/" . $_GET['plugin'] . '/config.php';
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
                }
              }
              echo '<br><button type="submit" class="btn btn-shadow btn-success btn-md">保存</button></form>';
              ?>
            <?php endif; ?>
          <?php endif; ?>
        <?php else : ?>
          <index>
            <h1><?php echo $WebName ?></h1>
            <h3>后台管理</h3>
          </index>
          <footer></footer>
        <?php endif; ?>
      <?php else : ?>
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
                  <input type="password" id="password" class="textEditor" name="password" placeholder="密码" value="<?php echo $_SESSION['RD_Password'] ?>"><br>
                  <button class="btn btn-primary btn-md">登录</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      <? endif; ?>
      <?php echo $_GET['mode'] == "editPost" || $_GET['mode'] == "plugins" ? '</content>' : '' ?>
  </main>
  <script>
    let defaultTitle = '瑞思文档';
    let defaultNavItems = [{
        text: '<i class="bi bi-house"></i>&nbsp;管理中心',
        href: 'admin.php?SE=<?php echo $_GET['SE'] ?>#'
      },
      {
        text: '<i class="bi bi-gear"></i>&nbsp;站点设置',
        href: 'admin.php?mode=editSet&SE=<?php echo $_GET['SE'] ?>#'
      },
      {
        text: '<i class="bi bi-pen"></i>&nbsp;编辑文章',
        href: 'admin.php?mode=editPost&SE=<?php echo $_GET['SE'] ?>#'
      },
      {
        text: '<i class="bi bi-plugin"></i>&nbsp;插件管理',
        href: 'admin.php?mode=plugins&SE=<?php echo $_GET['SE'] ?>#'
      },
      {
        text: '<i class="bi bi-box-arrow-right"></i>&nbsp;退出登录',
        href: 'admin.php?loginout=true&SE=<?php echo $_GET['SE'] ?>#'
      }
    ];
    let defaultNavRightItems = [{
      "text": "<i class=\"bi bi-info-circle\"></i>",
      "href": "javascript:createDialog(\"alert\", \"primary\", \"关于<?php echo addslashes($WebName) ?>\", \"<?php echo $copyRight ? $copyRight . '<br>' : '' ?>软件版本：<?php echo $localVersion; ?><br><a href=\\\"https://docs.3r60.top/Project\\\">Ris_Docs</a>提供软件服务\")"
    }];
    let defaultFooterLinks = [];
    let defaultCopyright = '版权所有 © 2024 腾瑞思智';
    let webTitle = '<?php echo addslashes($WebName) ?? '瑞思文档' ?>';
    $(document).ready(function() {
      $('.list a').on('click', function(event) {
        event.preventDefault();
        history.pushState('', '', this.href);
        fetchAndReplaceContent(this.href, 'title,content', 'title,content', () => {
          <?php echo $_GET['mode'] == 'editPost' ? 'refreshMarkdownContent()' : '' ?>;
          setActiveLinkInList($('.list'));
        });

      });
    });
    $(document).ready(function() {
      setTimeout('<?php
                  if ($_SESSION['RD_Password'] == '') {
                    echo 'createMessage("请验证身份后继续","")';
                    exit;
                  } elseif ($_SESSION['RD_Password'] !== $AdminPassword) {
                    echo 'createMessage("密码错误","danger")';
                    exit;
                  } elseif ($_GET['mode'] !== 'editPost') {
                    echo 'createMessage("欢迎回来，Admin","success")';
                  }
                  $vers = file_get_contents("https://backend.3r60.top/ver/vers.json");
                  $projects = json_decode($vers);
                  $ver = $projects->Ris_Docs->version;
                  $update = str_replace(PHP_EOL, '<br>', $projects->Ris_Docs->update);
                  if ($ver !== $localVersion && $_GET['mode'] !== 'editPost') {
                    echo ';createDialog("confirm","success","版本更新","' . $localVersion . '=>' . $ver . '<br>更新日志:' . $update . '<br>点击确定前往更新",()=>{location.href="./install.php"})';
                  }
                  ?>', 500)
    })

    function refreshMarkdownContent() {
      newMarkdownContent = $('#content').html();
      contentEditor = editormd("md-content", {
        markdown: newMarkdownContent,
        width: "100%",
        height: 500,
        syncScrolling: "single",
        path: "./assets/editormd/lib/",
        htmlDecode: true,
        toc: true,
        tocm: true,
        emoji: true,
        taskList: true,
        tex: true,
        flowChart: true,
        sequenceDiagram: true,
      });
    }
  </script>
  <?php if ($Rewrite == "true") : ?>
    <script src="//<?php echo $Http_Host_RW ?>/assets/editormd/lib/marked.min.js"></script>
  <?php else : ?>
    <script src="./assets/editormd/lib/marked.min.js"></script>
  <?php endif; ?>
</body>

</html>
<?
//引入配置项
include __DIR__ . '/config.php';
include __DIR__ . '/plugin.php';
//核心配置 (非必要不更改)
$localVersion = "2.0-r";
define('DOCS_DIRECTORY', __DIR__ . '/../docs/'); //文档存储位置
define('DOCS_404',  __DIR__ . '/../docs/404.md'); //404文档存储位置
define('CONFIG_PATH', __DIR__ . '/config.php');
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$uri = $_SERVER['REQUEST_URI'];
$queryString = $_SERVER['QUERY_STRING'];
if (!empty($queryString)) {
  $uri = str_replace('?' . $queryString, '', $uri);
}
if (substr($uri, -1) === '?') {
  $uri = substr($uri, 0, -1);
}
$Http_Host = $protocol . $host . $uri;
$Http_Host_RW = $_SERVER['HTTP_HOST'];
includePlugin(basename($_SERVER['SCRIPT_NAME']) == 'admin.php' ? 'admin' : 'user');
//定义函数
// - 插件引入
function includePlugin($env_requestPage, $input = '')
{
  $baseDir = __DIR__ . '/plugins';
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
          $pluginName = $dir;
          require(__DIR__ . "/plugins/$dir/functions.php");
        } else {
          echo "<script>alert('插件错误:无法解析插件 $dir 的 JSON信息 数据，此插件跳过加载')</script>";
        }
      } else {
        echo "<script>alert('插件错误:无法找到插件 $dir 的 JSON文件，此插件跳过加载')</script>";
      }
    }
  }
  if (!isset($return)) {
    return $input;
  }
  return $return;
}
function loadArticle($Name)
{
  $file = DOCS_DIRECTORY . $Name . ".md";
  if (file_exists($file)) {
    $html = includeFileContent("\n" . file_get_contents($file));
    $html = includeSet($html);
    echo $html;
  } else {
    $file = DOCS_404;
    $html = includeFileContent("\n" . file_get_contents($file));
    $html = includeSet($html);
    echo $html;
  }
}

function includeFileContent($markdownContent)
{
  $pattern = '/{{include\(\'(.*?)\'\)}}/';
  $updatedContent = preg_replace_callback($pattern, function ($matches) {
    if ($matches[1] == "directory") {
      $fileContent = generateMDIndex(DOCS_DIRECTORY, '', true, "common");
      return $fileContent;
    } else {
      $fileContent = file_get_contents($matches[1]);
      return $fileContent;
    }
  }, $markdownContent);
  return $updatedContent;
}

function includeSet($docs_fullContent)
{
  $pattern = '/{{set\(\'(.*?)\'\)}}/';
  $docs_fullContent = preg_replace_callback($pattern, function ($matches) {
    $variableName = $matches[1];
    if (isset($GLOBALS[$variableName])) {
      return $GLOBALS[$variableName];
    } else {
      return $matches[0];
    }
  }, $docs_fullContent);
  $docs_fullContent = includePlugin("docsTransform", $docs_fullContent);
  return $docs_fullContent;
}

function loadDirectory($mode = "common")
{
  $content = includeFileContent(generateHTMLIndex(DOCS_DIRECTORY, '', true, $mode));
  $content = includeSet($content);
  $content = preg_replace('/^<ul>/i', '', $content);
  $content = preg_replace('/<\/ul>\s*$/i', '', $content);
  echo $content;
}
// 判断网页标题
$purpose = $_GET['article'];
if (isset($purpose)) {
  $file = DOCS_DIRECTORY . $purpose . ".md";
  if (!file_exists($file)) {
    $pageName = "文档不存在";
  } else {
    $pageName = preg_replace('/\{M=\d+\}/', '', $purpose);
    $pageName = str_replace('/', '>', $pageName);
  }
  if ($pageName == "home") {
    $pageName = "首页";
  }
} else {
  $pageName = "首页";
}
function generateMDIndex($directory, $parentPath = '', $isRoot = false, $mode)
{
  $mdContent = '';
  if ($mode == "admin") {
    $linkPlugin .= "&SE=" . $_GET['SE'];
  }
  $files = scandir($directory);
  $subdirectories = [];
  $subfiles = [];
  foreach ($files as $file) {
    if ($file != '.' && $file != '..' && $file != 'home.md' && $file != 'index.md' && $file != 'img' && $file != 'directory-true.md' && $file != 'directory.md' && $file != '404.md') {
      $filePath = $directory .
        '/' . $file;
      if (is_dir($filePath)) {
        $subdirectories[] = $file;
      } elseif (pathinfo($file, PATHINFO_EXTENSION) == 'md') {
        $subfiles[] = $file;
      }
    }
  }
  global $Http_Host;
  global $Http_Host_RW;
  global $Rewrite;
  if ($isRoot) {
    if ($Rewrite == "true" && $mode == "common") {
      $mdContent .= "- [首页](//{$Http_Host_RW}/article/home)\n";
    } else {
      $mdContent .= "- [首页]({$Http_Host}?article=home{$linkPlugin})\n";
    }
  }
  $fileWeights = [];
  $fileNames = [];
  foreach ($subfiles as $file) {
    $articleName = pathinfo($file, PATHINFO_FILENAME);
    if (preg_match('/\{M=(\d+)\}/', $articleName, $matches)) {
      $weight = intval($matches[1]);
      $fileWeights[$articleName] = $weight;
      $articleName = preg_replace('/\{M=\d+\}/', '', $articleName);
    } else {
      $fileWeights[$articleName] = 0;
    }
    $fileNames[$articleName] = $file;
  }
  arsort($fileWeights);
  foreach ($fileWeights as $articleName => $weight) {
    $file = $fileNames[$articleName];
    if ($Rewrite == "true" && $mode == "common") {
      $articleLink = "//{$Http_Host_RW}/article/{$parentPath}" . urlencode($articleName);
    } else {
      $articleLink = "{$Http_Host}?article={$parentPath}" . urlencode($articleName) . "{$linkPlugin}";
    }
    $mdContent .= str_repeat("  ", count(explode("/", $parentPath)) - 1) . "- [" . preg_replace('/\{M=\d+\}/', '', $articleName) . "]($articleLink)\n";
  }
  foreach ($subdirectories as $dir) {
    if ($Rewrite == "true" && $mode == "common") {
      $articleLink = "//{$Http_Host_RW}/article/{$parentPath}" . urlencode($articleName);
      $mdContent .= str_repeat("  ", count(explode("/", $parentPath)) - 1) . "- [$dir](//{$Http_Host_RW}/article/" . urlencode($dir) . "/index)\n";
    } else {
      $mdContent .= str_repeat("  ", count(explode("/", $parentPath)) - 1) . "- [$dir]({$Http_Host}?article=" . urlencode($dir) . "/index" . $linkPlugin . ")\n";
    }
    $mdContent .= generateMDIndex($directory . '/' . $dir, "{$parentPath}" . urlencode($dir) . '/', '', $mode);
  }
  return $mdContent;
}
function generateHTMLIndex($directory, $parentPath = '', $isRoot = false, $mode)
{
  $htmlContent = '';

  if ($mode == "admin") {
    $linkPlugin .= "?mode=editPost&SE=" . $_GET['SE'] . '&';
  } else {
    $linkPlugin .= "?";
  }

  // 扫描目录
  $files = scandir($directory);

  // 用于保存子目录和文件
  $subdirectories = [];
  $subfiles = [];

  foreach ($files as $file) {
    if ($file != '.' && $file != '..' && $file != 'home.md' && $file != 'index.md' && $file != 'img' && $file != 'directory-true.md' && $file != 'directory.md' && $file != '404.md') {
      $filePath = $directory . '/' . $file;

      if (is_dir($filePath)) {
        $subdirectories[] = $file;
      } elseif (pathinfo($file, PATHINFO_EXTENSION) == 'md') {
        $subfiles[] = $file;
      }
    }
  }

  global $Http_Host;
  global $Http_Host_RW;
  global $Rewrite;

  // 处理首页
  if ($isRoot) {
    if ($Rewrite == "true" && $mode == "common") {
      $htmlContent .= '<li><a href="//' . $Http_Host_RW . '/article/home">首页</a></li>';
    } else {
      $htmlContent .= '<li><a href="' . $Http_Host . $linkPlugin . 'article=home' . '">首页</a></li>';
    }
  }

  // 处理子文件
  $fileWeights = [];
  $fileNames = [];

  foreach ($subfiles as $file) {
    $articleName = pathinfo($file, PATHINFO_FILENAME);

    if (preg_match('/\{M=(\d+)\}/', $articleName, $matches)) {
      $weight = intval($matches[1]);
      $fileWeights[$articleName] = $weight;
      $articleName = preg_replace('/\{M=\d+\}/', '', $articleName);
    } else {
      $fileWeights[$articleName] = 0;
    }

    $fileNames[$articleName] = $file;
  }

  arsort($fileWeights);

  foreach ($fileWeights as $articleName => $weight) {
    $file = $fileNames[$articleName];
    $encodedName = urlencode($articleName);
    if ($Rewrite == "true" && $mode == "common") {
      $articleLink = '//' . $Http_Host_RW . '/article/' . ($parentPath ? $parentPath . '/' . $encodedName : $encodedName);
    } else {
      $articleLink = $Http_Host . $linkPlugin . 'article=' . ($parentPath ? $parentPath . '/' . $encodedName : $encodedName);
    }

    $htmlContent .= str_repeat('  ', count(explode('/', $parentPath)) - 1) . '<li><a href="' . $articleLink . '">' . preg_replace('/\{M=\d+\}/', '', $articleName) . '</a></li>' . "\n";
  }

  // 处理子目录
  foreach ($subdirectories as $dir) {
    $encodedDir = urlencode($dir);
    if ($Rewrite == "true" && $mode == "common") {
      $dirLink = '//' . $Http_Host_RW . '/article/' . ($parentPath ? $parentPath . '/' . $encodedDir : $encodedDir) . '/index';
    } else {
      $dirLink = $Http_Host . $linkPlugin . 'article=' . ($parentPath ? $parentPath . '/' . $encodedDir : $encodedDir) . '/index';
    }
    $htmlContent .= str_repeat('  ', count(explode('/', $parentPath)) - 1) . '<li><a href="' . $dirLink . '">' . $dir . '</a></li>' . "\n";
    $htmlContent .= generateHTMLIndex($directory . '/' . $dir, ($parentPath ? $parentPath . '/' . $encodedDir : $encodedDir), false, $mode);
  }

  return '<ul>' . $htmlContent . '</ul>';
}
function getSubstr($str, $leftStr, $rightStr)
{
  $left = strpos($str, $leftStr);
  //echo '左边:'.$left;
  $right = strpos($str, $rightStr, $left);
  //echo '<br>右边:'.$right;
  if ($left < 0 or $right < $left) return '';
  return substr($str, $left + strlen($leftStr), $right - $left - strlen($leftStr));
}
function forceFilePutContents($filepath, $message)
{
  try {
    $isInFolder = preg_match("/^(.*)\/([^\/]+)$/", $filepath, $filepathMatches);
    if ($isInFolder) {
      $folderName = $filepathMatches[1];
      $fileName = $filepathMatches[2];
      if (!is_dir($folderName)) {
        mkdir($folderName, 0777, true);
      }
    }
    file_put_contents($filepath, $message);
  } catch (Exception $e) {
    echo "ERR: error writing '$message' to '$filepath'," . $e->getMessage();
  }
}
function deleteDirectory($dir)
{
  if (!is_dir($dir)) {
    return false;
  }
  $files = array_diff(scandir($dir), array('.', '..'));
  foreach ($files as $file) {
    (is_dir("$dir/$file")) ? deleteDirectory("$dir/$file") : unlink("$dir/$file");
  }
  return rmdir($dir);
}
function addConfig($variableName, $value,  $fileName = CONFIG_PATH)
{
  if (!file_exists($fileName) || empty(file_get_contents($fileName))) {
    file_put_contents($fileName, '<?php');
  }
  $content = file_get_contents($fileName);
  if (preg_match('/(\n\s*)?\$' . preg_quote($variableName, '/') . '\s*=\s*(.*);\n?/', $content, $matches)) {
    $newLine = "$matches[1]$$variableName = '$value';\n";
    $newContent = str_replace($matches[0], $newLine, $content);
  } else {
    file_put_contents($fileName, "\n$$variableName = '$value';", FILE_APPEND);
  }
  if (isset($newContent)) {
    file_put_contents($fileName, $newContent);
  }
}
function deleteConfig($variableName, $fileName = CONFIG_PATH)
{
  $content = file_get_contents($fileName);
  $pattern = '/(\n\s*)?\$' . preg_quote($variableName, '/') . '\s*=\s*.*;\n?/';
  $newContent = preg_replace($pattern, '', $content);
  if ($newContent !== $content) {
    file_put_contents($fileName, $newContent);
    return true;
  } else {
    return false;
  }
}

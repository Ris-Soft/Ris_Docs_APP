<?
//引入配置项
include __DIR__ . '/config.php';
include __DIR__ . '/plugin.php';
//2.2版本密码改为哈希处理，此处为升级适配代码
if (strpos($AdminPassword, '$2y$') !== 0 && strpos($AdminPassword, '$argon2i$') !== 0 && strpos($AdminPassword, '$argon2id$') !== 0 && strpos($AdminPassword, '$7$') !== 0) {
  addConfig('AdminPassword', password_hash($AdminPassword, PASSWORD_DEFAULT), __DIR__ . '/config.php');
}
//核心配置 (非必要不更改)
$localVersion = "2.4.1";
define('DOCS_DIRECTORY', __DIR__ . '/../docs/'); //文档存储位置
define('DOCS_404', __DIR__ . '/../docs/404.md'); //404文档存储位置
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
//定义函数
// - 插件引入

function initializeCategories()
{
  $categoryMarker = DOCS_DIRECTORY . 'category_enabled';
  if (!file_exists($categoryMarker)) {
    // 创建default分类目录
    if (!is_dir(DOCS_DIRECTORY . 'default')) {
      mkdir(DOCS_DIRECTORY . 'default', 0777, true);
    }

    // 移动现有文件到default分类
    $files = scandir(DOCS_DIRECTORY);
    foreach ($files as $file) {
      if ($file != '.' && $file != '..' && $file != 'home.md' && $file != '404.md' && $file != 'default' && $file != 'img') {
        $oldPath = DOCS_DIRECTORY . $file;
        $newPath = DOCS_DIRECTORY . 'default/' . $file;
        if (is_file($oldPath)) {
          rename($oldPath, $newPath);
        }
      }
    }

    // 创建分类标记文件
    file_put_contents($categoryMarker, '');
  }
}

function getCategories()
{
  initializeCategories();
  $categories = [];
  $files = scandir(DOCS_DIRECTORY);
  foreach ($files as $file) {
    if ($file != '.' && $file != '..' && $file != 'home.md' && $file != '404.md' && $file != 'img' && $file != 'category_enabled' && is_dir(DOCS_DIRECTORY . $file)) {
      $categories[] = $file;
    }
  }
  return $categories;
}

function includePlugin($env_requestPage, $input = '')
{

  $baseDir = __DIR__ . '/plugins';
  $dirs = scandir($baseDir);
  foreach ($dirs as $dir) {
    if ($dir == '.' || $dir == '..' || $dir == '说明') {
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
          $return = null;
          require(__DIR__ . "/plugins/$dir/functions.php");
          if ($return != null)
            $input = $return;
        }
      } else {
        echo "<script>alert('插件错误:无法解析插件 $dir 的 JSON信息 数据，此插件跳过加载')</script>";
      }
    } else {
      echo "<script>alert('插件错误:无法找到插件 $dir 的 JSON文件，此插件跳过加载')</script>";
    }
  }
  if (isset($return)) {
    return $return;
  }
  return $input;
}
function loadArticle($Name, $echo = true)
{
  if (strpos($Name, 'search:') === 0) {
    $Name = substr($Name, 7);
    if ($echo) {
      echo "# $Name 的搜索结果 \n 在左侧列表中选择以继续";
      return;
    } else {
      return "# $Name 的搜索结果 \n 在左侧列表中选择以继续";
    }
  }
  $file = DOCS_DIRECTORY . $Name . ".md";
  if (file_exists($file)) {
    $html = includeFileContent("\n" . file_get_contents($file));
    $html = includeSet($html);
    if ($echo) {
      echo $html;
    } else {
      return $html;
    }
  } else {
    $file = DOCS_404;
    $html = includeFileContent("\n" . file_get_contents($file));
    $html = includeSet($html);
    if ($echo) {
      echo $html;
    } else {
      return $html;
    }
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

function loadDirectory($mode = "common", $category = "default")
{
  $content = generateHTMLIndex(DOCS_DIRECTORY . $category, '', true, $mode);
  $content = preg_replace('/^<ul>/i', '', $content);
  $content = preg_replace('/<\/ul>\s*$/i', '', $content);
  echo $content;
}
// 判断网页标题
$purpose = isset($_GET['article']) ? $_GET['article'] : null;
if (isset($purpose)) {
  $file = DOCS_DIRECTORY . $purpose . ".md";
  if (!file_exists($file) && strpos($purpose, 'search:') !== 0) {
    $pageName = "文档不存在";
  } else {
    $pageName = preg_replace('/\{M=\d+\}/', '', $purpose);
    $pageName = str_replace('/', '>', $pageName);
  }
  if ($pageName == "home") {
    $pageName = "首页";
  }
  if (is_dir(DOCS_DIRECTORY . $purpose) && $mode !== "admin") {
    $pageName = strpos($purpose, '/') !== false ? substr($purpose, 0, strpos($purpose, '/')) : $purpose;
  }
} else {
  $pageName = "首页";
}
function generateMDIndex($directory, $parentPath = '', $isRoot = false, $mode = "user")
{
  $mdContent = '';
  if ($mode == "admin") {
    $linkPlugin = "&SE=" . $_GET['SE'];
  } else {
    $linkPlugin = "";
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
    @$file = $fileNames[$articleName];
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
      $mdContent .= str_repeat("  ", count(explode("/", $parentPath)) - 1) . "- [$dir](//{$Http_Host_RW}/article/" . urlencode($dir) . ")\n";
    } else {
      $mdContent .= str_repeat("  ", count(explode("/", $parentPath)) - 1) . "- [$dir]({$Http_Host}?article=" . urlencode($dir) . "" . $linkPlugin . ")\n";
    }
    $mdContent .= generateMDIndex($directory . '/' . $dir, "{$parentPath}" . urlencode($dir) . '/', '', $mode);
  }
  return $mdContent;
}
function generateHTMLIndex($directory, $parentPath = '', $isRoot = false, $mode = "user", $category = NULL)
{
  $htmlContent = '';

  if ($category == NULL) {
    $category = basename($directory);
  }

  // Handle search functionality
  if (strpos($category, 'search:') === 0) {
    $searchTerm = substr($category, 7);
    $searchResults = [];

    // Helper function to search recursively through directories
    function searchInDirectory($dir, $term)
    {
      $results = [];
      $files = scandir($dir);

      foreach ($files as $file) {
        if ($file != '.' && $file != '..' && $file != 'img' && $file != '404.md') {
          $path = $dir . '/' . $file;
          if (is_dir($path)) {
            $results = array_merge($results, searchInDirectory($path, $term));
          } elseif (pathinfo($file, PATHINFO_EXTENSION) == 'md') {
            $content = file_get_contents($path);
            $title = pathinfo($file, PATHINFO_FILENAME);
            $category = basename(dirname($path));
            if (stripos($content, $term) !== false || stripos($title, $term) !== false) {
              $results[] = [
                'path' => str_replace(DOCS_DIRECTORY, '', $path),
                'title' => $category . ' > ' . preg_replace('/\{M=\d+\}/', '', $title)
              ];
            }
          }
        }
      }
      return $results;
    }

    $searchResults = searchInDirectory(DOCS_DIRECTORY, $searchTerm);

    // Generate HTML for search results
    foreach ($searchResults as $result) {
      global $Http_Host, $Http_Host_RW, $Rewrite;
      $encodedPath = ltrim(str_replace('.md', '', $result['path']), '/');

      if ($Rewrite == "true" && $mode == "common") {
        $articleLink = '//' . $Http_Host_RW . '/article/' . $encodedPath;
      } else {
        $articleLink = $Http_Host . '?article=' . $encodedPath;
      }

      $htmlContent .= '<li><a href="' . $articleLink . '">' . $result['title'] . '</a></li>' . "\n";
    }

    return '<ul>' . $htmlContent . '</ul>';
  }

  // Rest of the original function code remains the same
  if ($mode == "admin") {
    $linkPlugin = "?mode=editPost&SE=" . $_GET['SE'] . '&category=' . basename($category) . '&';
  } else {
    $linkPlugin = "?";
  }

  // Original directory scanning and processing code...
  // [Previous code remains unchanged from here]

  $files = scandir($directory);
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
    @$file = $fileNames[$articleName];
    $encodedName = urlencode($articleName);
    if ($Rewrite == "true" && $mode == "common") {
      $articleLink = '//' . $Http_Host_RW . '/article/' . basename($directory) . '/' . ($parentPath ? $parentPath . '/' . $encodedName : $encodedName);
    } elseif ($mode != "admin") {
      $articleLink = $Http_Host . $linkPlugin . 'article=' . basename($directory) . '/' . ($parentPath ? $parentPath . '/' . $encodedName : $encodedName);
    } else {
      $articleLink = $Http_Host . $linkPlugin . 'article=' . ($parentPath ? $parentPath . '/' . $encodedName : $encodedName);
    }

    $htmlContent .= str_repeat('  ', count(explode('/', $parentPath)) - 1) . '<li><a href="' . $articleLink . '">' . preg_replace('/\{M=\d+\}/', '', $articleName) . '</a></li>' . "\n";
  }

  foreach ($subdirectories as $dir) {
    $encodedDir = urlencode($dir);
    if ($Rewrite == "true" && $mode == "common") {
      $dirLink = '//' . $Http_Host_RW . '/article/' . basename($directory) . '/' . ($parentPath ? $parentPath . '/' . $encodedDir : $encodedDir) . '/index';
    } elseif ($mode != "admin") {
      $dirLink = $Http_Host . $linkPlugin . 'article=' . basename($directory) . '/' . ($parentPath ? $parentPath . '/' . $encodedDir : $encodedDir) . '/index';
    } else {
      $dirLink = $Http_Host . $linkPlugin . 'article=' . ($parentPath ? $parentPath . '/' . $encodedDir : $encodedDir) . '/index';
    }
    $htmlContent .= str_repeat('  ', count(explode('/', $parentPath)) - 1) . '<li><a href="' . $dirLink . '">' . $dir . '</a></li>' . "\n";
    $htmlContent .= generateHTMLIndex($directory . '/' . $dir, ($parentPath ? $parentPath . '/' . $encodedDir : $encodedDir), false, $mode, $category);
  }

  return '<ul>' . $htmlContent . '</ul>';
}
function getSubstr($str, $leftStr, $rightStr)
{
  $left = strpos($str, $leftStr);
  //echo '左边:'.$left;
  $right = strpos($str, $rightStr, $left);
  //echo '<br>右边:'.$right;
  if ($left < 0 or $right < $left)
    return '';
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
function addConfig($variableName, $value, $fileName = CONFIG_PATH)
{
  if (!file_exists($fileName) || empty(file_get_contents($fileName))) {
    file_put_contents($fileName, '<?php');
  }
  $content = str_replace('?>', '', file_get_contents($fileName));
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

<?
//引入配置项
include __DIR__.'/config.php';
include __DIR__.'/plugin.php';
//核心配置 (非必要不更改)
$localVersion = "1.6.1";
define('DOCS_DIRECTORY', 'docs/'); //文档存储位置
define('DOCS_404', 'docs/404.md'); //404文档存储位置
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
function loadArticle($Name) {
    $file = DOCS_DIRECTORY.$Name.".md";
    if (file_exists($file)) {
        echo includeSet(includeFileContent("\n".file_get_contents($file)));
    } else {
        $file = DOCS_404;
        echo includeSet(includeFileContent("\n".file_get_contents($file)));
    }
}

function includeFileContent($markdownContent) {
    $pattern = '/{{include\(\'(.*?)\'\)}}/';
    $updatedContent = preg_replace_callback($pattern, function($matches) {
        if ($matches[1] == "directory"){
        $fileContent = generateMDIndex(DOCS_DIRECTORY, '', true,"common");
        return $fileContent;
        }else{
        $fileContent = file_get_contents($matches[1]);
        return $fileContent;
        }
    }, $markdownContent);
    return $updatedContent;
}

function includeSet($markdownContent) {
    $pattern = '/{{set\(\'(.*?)\'\)}}/';
    $updatedContent = preg_replace_callback($pattern, function($matches) {
        $variableName = $matches[1];
        if (isset($GLOBALS[$variableName])) {
            return $GLOBALS[$variableName];
        } else {
            return $matches[0]; 
        }
    }, $markdownContent);
    return $updatedContent;
}

function loadDirectory($mode = "common") {

        $content = includeFileContent(generateMDIndex(DOCS_DIRECTORY, '', true,$mode));
        echo $content;
}
// 判断网页标题
$purpose = $_GET['article'];
if (isset($purpose)) {
    $file = DOCS_DIRECTORY.$purpose.".md";
    if (!file_exists($file)) {
    $pageName = "文档不存在";
    } else {
    $pageName = preg_replace('/\{M=\d+\}/', '', $purpose);
    $pageName = str_replace('/', '>', $pageName);
    }
} else {
    $pageName = "首页";
}
function generateMDIndex($directory, $parentPath = '', $isRoot = false ,$mode) {
    $mdContent = '';

    if ($mode == "admin"){
        $linkPlugin .= "&SE=".$_GET['SE'];
    }

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

    global $Http_Host;
    global $Http_Host_RW;
    global $Rewrite;

    // 处理首页
    if ($isRoot) {
        if ($Rewrite == "true" && $mode == "common"){
            $mdContent .= "- [首页](//{$Http_Host_RW}/article/home)\n";
        } else {
            $mdContent .= "- [首页]({$Http_Host}?article=home{$linkPlugin})\n";
        }
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
        if ($Rewrite == "true" && $mode == "common"){
            $articleLink = "//{$Http_Host_RW}/article/{$parentPath}".urlencode($articleName);
        } else {
            $articleLink = "{$Http_Host}?article={$parentPath}".urlencode($articleName)."{$linkPlugin}";
        }
        
        // 生成链接
        $mdContent .= str_repeat("  ", count(explode("/", $parentPath)) - 1)."- [".preg_replace('/\{M=\d+\}/', '', $articleName)."]($articleLink)\n";
    }

    // 处理子目录
    foreach($subdirectories as $dir) {
        if ($Rewrite == "true" && $mode == "common"){
            $articleLink = "//{$Http_Host_RW}/article/{$parentPath}".urlencode($articleName);
            $mdContent .= str_repeat("  ", count(explode("/", $parentPath)) - 1)."- [$dir](//{$Http_Host_RW}/article/".urlencode($dir)."/index)\n";
        } else {
            $mdContent .= str_repeat("  ", count(explode("/", $parentPath)) - 1)."- [$dir]({$Http_Host}?article=".urlencode($dir)."/index".$linkPlugin.")\n";
        }
        $mdContent .= generateMDIndex($directory.'/'.$dir, "{$parentPath}".urlencode($dir).'/','',$mode);
    }


    return $mdContent;
}
function getSubstr($str, $leftStr, $rightStr)
{
    $left = strpos($str, $leftStr);
    //echo '左边:'.$left;
    $right = strpos($str, $rightStr,$left);
    //echo '<br>右边:'.$right;
    if($left < 0 or $right < $left) return '';
    return substr($str, $left + strlen($leftStr), $right-$left-strlen($leftStr));
}
function forceFilePutContents ($filepath, $message){
    try {
        $isInFolder = preg_match("/^(.*)\/([^\/]+)$/", $filepath, $filepathMatches);
        if($isInFolder) {
            $folderName = $filepathMatches[1];
            $fileName = $filepathMatches[2];
            if (!is_dir($folderName)) {
                mkdir($folderName, 0777, true);
            }
        }
        file_put_contents($filepath, $message);
    } catch (Exception $e) {
        echo"ERR: error writing '$message' to '$filepath',". $e->getMessage();
    }
}
function deleteDirectory($dir) {
    if (!is_dir($dir)) {
        return false;
    }
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? deleteDirectory("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
}

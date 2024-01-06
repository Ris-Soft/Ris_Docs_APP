<?php 
//引入配置项
include 'config.php';

include 'md2html.php';
$Parsedown = new Parsedown();
// use Michelf\Markdown;
//定义函数
function loadArticle($Name){
    global $Parsedown;
                    $file = DOCS_DIRECTORY.$Name.".md";
                    if (file_exists($file)) {
                        echo$Parsedown->text(includeSet(includeFileContent(file_get_contents($file))));
                    } else {
                        $file = DOCS_404;
                        echo $Parsedown->text(includeSet(includeFileContent(file_get_contents($file))));
                    }
                }
function includeFileContent($markdownContent) {
    $pattern = '/{{include\(\'(.*?)\'\)}}/';
    $updatedContent = preg_replace_callback($pattern, function($matches) {
        $fileContent = file_get_contents($matches[1]);
        return $fileContent;
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
            return $matches[0]; // 如果找不到变量，返回原始内容
        }
    }, $markdownContent);
    return $updatedContent;
}

            // Function to load and display Markdown content in the left column
function loadDirectory() {
    global $Parsedown;
                $file = DOCS_DIRECTORY_FILE;  // Update with your directory file path

                if (file_exists($file)) {
                    $content = includeFileContent(file_get_contents($file));
                    echo $Parsedown->text($content);
                } else {
                    echo "<p>目录文件不存在。</p>";
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
    <title><?php echo $WebName ?></title>
    <script src="marked.min.js"></script>
<script src="https://assets.3r60.top/v2/package.js"></script>
    <link rel="stylesheet" href="ghmd.css">

<style>
    body {
        display: flex;
    }
.main a {
    color: white; /* 设置链接文字颜色为白色 */
    text-decoration: underline!important; /* 移除链接下划线 */
}


    .main {
        display: flex;
        flex-direction: row; /* Display in a row */
        margin-bottom: 10px;
            height: 90%;
    overflow: auto;
    }

    .left-column {
        margin-top: 15px;
        margin-left: 15px;
        margin-bottom: 15px;
        width: 20%;
        padding: 20px;
        overflow-y: auto;
        box-sizing: border-box;
        color: white;
        background-color: rgba(255,255,255,.2);
        border-radius: 10px;
    }
    .left-column-content {
            text-align: left;
        }
    .right-column {
        width: 80%;
        padding: 10px;
        overflow-y: auto;
        box-sizing: border-box;
        color: white;
        border-radius: 20px;
        text-align: left;
    }

    @media (max-width: 875px) {
        .left-column {
            display: none;
        }
        .main {
            height: 85%;
        }
        .right-column {
            width: 100%;
        }
    }

code, pre {
    /*background: #2d2d2d;*/
    color: rgb(201, 209, 217);
    font-family: Consolas;
    text-align: left;
    padding: 2px;
    padding-left: 0.8em;
    border-radius: 5px;
    counter-reset: line;
    white-space: pre-wrap; /* 使用pre-wrap以保留换行，同时允许自动换行 */
    word-spacing: normal;
    word-break: normal;
    max-width: 100%; /* 限制最大宽度 */
    overflow: auto; /* 处理溢出情况 */
}
p {
    margin:none;
}
Lead>ul {
  list-style-type: none;
  padding: 0; 
  width: 230px; 
}

Lead>ul li a {
  display: block; 
  padding: 8px 16px; 
  text-decoration: none;
}

Lead>ul li a:hover {
    background-color: rgba(0,0,0,.2); 
}

ul ul {
    /*width: 210px;*/
    /*margin-left: 20px; */
    list-style-type: none
}

Lead>ul>ul>li a {
  padding: 4px 16px; 
}
</style>
</head>
<body>
    <!--遮盖层-->
    <div class="back-under" id="back-under"></div> 
    <!--顶栏-->
    <Topbar id="Topbar">
        <button type="submit" onclick="toggleMenu()"><i id="MenuButton" class="bi bi-list" style="font-size:25px;"></i></button>
        <img src="favicon.ico" alt="网站LOGO" height="40px" />
        <p><?php echo $WebName ?></p>
        <div style="margin-left:auto">
        <button class="btn-white" type="submit" onclick="toggleLight()"><i class="bi bi-moon-fill" style="font-size:15px;"></i></button><button class="btn-white" type="submit" onclick="toggleSet()"><i class="bi bi-gear-fill" style="font-size:15px;"></i></button>
        </div>
    </Topbar>
    <!--主体部分-->
    <Left id="W-Menu">
        <h3>全局导航</h3>
		<ul class="list">
<!--展示附加链接-->
			<li><a href="<?php echo $HomeURL ?>">首页</a></li>
			<li><a href="./">文档</a></li>
            <?php if($BlogURL != "NoShow"){echo '<li><a href="'.$BlogURL.'">博客</a></li>';}?>
            <?php if($GithubURL != "NoShow"){echo '<li><a href="'.$GithubURL.'">Github</a></li>';}?>
			<?php if($ShowRefrush == true){echo '<li><a href="#" onclick="javascript:location.reload();">刷新</a></li>';}?>


			</ul>
    </Left>
    <Right id="W-Set">
        <h3>网站菜单</h3>
        <!--关于部分-->
        <h4>关于程序</h4>
        <span><b>Ris_Docs</b><br>
        开源PHP文档系统[By PYLXU]<br>
        当前版本:<i>231223</i></span>
        <!--版权部分-->
        <h4>关于框架</h4>
        <span><b>RWUI V2[主体副本231223]</b><br>
        全部代码均由PYLXU书写<br>
        <i>© 2023 腾瑞思智. All rights reserved.</i></span>
    </Right>
    <!--主容器-->
    <div class="container">
        <Lead id="Lead" style="background-color: rgba(220,220,220,.2)">
            <h3>站内导航
                <button class="onlyPhone btn-white" type="submit" onclick="toggleLead()" style="color: rgb(0, 0, 0);float: right;height: 30px;margin-right: 10px;"><i class="bi bi-list" style="font-size: 15px; color: rgb(0, 0, 0);"></i></button></h3>
            <!--<code markdown>-->
            <?PHP
            loadDirectory();
            ?>
            <!--</code>-->
            <a href="./update-direct.php">更新目录文件</a>
            <br>
            由 <a href="https://github.com/PYLXU/Ris_Docs_APP/">Ris Docs</a> 驱动
        </Lead>
    <!--主要部分-->
    <Main id="Main">
            <!--<code markdown>-->
                <?PHP
                if ($_GET['article'] == "" || $_GET['article'] == "home") {
                    loadArticle('home');
                } else {
                    loadArticle($_GET['article']);
                }
                ?>
            <!--</code>-->
    </Main>
    </div>
<script src="https://assets.3r60.top/v2/package-end.js"></script>
</body>
</html>

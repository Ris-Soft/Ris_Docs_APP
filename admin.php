<?php 

//引入配置项
include './assets/function.php';
if(!empty($SecurityEntrance) && $_GET['SE'] !== $SecurityEntrance){
	echo '安全入口错误';
	exit;
}

// POST请求处理
if($_SERVER['REQUEST_METHOD'] == "POST") {
	if($_POST['action'] == "editSet") {
		$Php = '<?php
$AdminPassword = "'.$_POST['AdminPassword'].'";
$WebName = "'.$_POST['WebName'].'";
$HomeURL = "'.$_POST['HomeUrl'].'";
$GithubURL = "'.$_POST['GithubUrl'].'";
$BlogURL = "'.$_POST['BlogUrl'].'";
$Rewrite = "'.$_POST['Rewrite'].'";
$SecurityEntrance = "'.$_POST['SecurityEntrance'].'";
?>
';
file_put_contents("./assets/config.php",$Php);
header("location: ./admin.php?article=editSet&SE=".$_POST['SecurityEntrance']);
include './assets/config.php';
	} elseif($_POST['action'] == "createPost") {
		forceFilePutContents(DOCS_DIRECTORY.$_POST['name'].'.md',"新建空文档");
	} elseif($_POST['action'] == "editPost") {
		forceFilePutContents(DOCS_DIRECTORY.$_GET['article'].'.md',$_POST['content']);
	}
}

session_start();
if($_GET['loginout'] == "true"){
	$_SESSION['RD_Password'] = "";
}
if(isset($_POST['password'])){
$_SESSION['RD_Password'] = $_POST['password'];
}

?>
	<!doctype html>
	<html lang="zh-CN">
		<head>
			<meta charset="UTF-8">
			<meta http-equiv="Content-Language" content="zh-CN">
			<meta http-equiv="X-UA-Compatible" content="IE=edge">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<title>
				<?php echo '后台管理 - '. $WebName ?>
			</title>
			<script src="https://assets.3r60.top/v2/package.js">
			</script>
			<link rel="stylesheet" href="./assets/style.css">
			<style>
			#main-display {  
        			text-align: center;  
        			height:60vh;
        			width:100vw;
    			display: table-cell;
    			vertical-align: middle;
    			text-align: center;
			}
			</style>
</head>
<body>
<?php 
//引入顶栏
include './assets/topbar.php';
?>
	<!--主容器-->
	<div class="container">
		<Lead id="Lead" style="background-color: rgba(220,220,220,.2)">
			<h3>
				后台管理
				<button class="onlyPhone btn-white" type="submit" onclick="toggleLead()"
				style="float: right;height: 30px;margin-right: 10px;">
					<i class="bi bi-list" style="font-size: 15px; color: rgb(0, 0, 0);">
					</i>
				</button>
			</h3>
			 - 管理项目
			<ul>
				<li><a href="admin.php?SE=<?php echo $_GET['SE']?>"><i class="bi bi-house"></i>&nbsp;管理中心</a></li>
				<li><a href="admin.php?article=editSet&SE=<?php echo $_GET['SE']?>"><i class="bi bi-gear"></i>&nbsp;站点设置</a></li>
				<li><a href="admin.php?article=newPost&SE=<?php echo $_GET['SE']?>"><i class="bi bi-plus-square"></i>&nbsp;新建文章</a></li>
			</ul>
			 - 文章列表
			<?PHP loadDirectory('admin'); ?>
				<a href="./">
					返回前台
				</a>
		</Lead>
		<!--主要部分-->
		    <link rel="stylesheet" type="text/css" href="./assets/editormd/css/editormd.css">
			
		<Main id="Main" class="markdown-body">
			<?php if($_SESSION['RD_Password'] == $AdminPassword): ?>
				<?php if(isset($_GET['article'])): ?>
					<?php if($_GET['article'] == "editSet"): ?>
						<h2>站点设置</h2>
						<form method="POST" action="">
						<input type="hidden" name="action" value="editSet">
						<button style="position:fixed;top:7px;right:15px" class="Button2">保存设置</button>
						网站名称:<br>
						<input class="Input" name="WebName" placeholder="网站名称" style="margin-top:5px;margin-bottom:5px" value="<?php echo $WebName ?>"><br>
						管理员密码:<br>
						<input type="password" name="AdminPassword" class="Input" placeholder="管理员密码" style="margin-top:5px;margin-bottom:5px" value="<?php echo $AdminPassword ?>" required><br>
						官网地址:<br>
						<input class="Input" name="HomeUrl" placeholder="官网地址" style="margin-top:5px;margin-bottom:5px" value="<?php echo $HomeURL ?>"><br>
						Github地址[NoShow表示不显示]:<br>
						<input class="Input" name="GithubUrl" placeholder="Github地址" style="margin-top:5px;margin-bottom:5px" value="<?php echo $GithubURL ?>"><br>
						博客地址[NoShow表示不显示]:<br>
						<input class="Input" name="BlogUrl" placeholder="博客地址" style="margin-top:5px;margin-bottom:5px" value="<?php echo $BlogURL ?>"><br>
						安全入口:<button type="button" onclick="alert('若填写此项，进入admin.php时需附带?SE=安全密码的 GET参数，同时右栏的管理入口会隐藏')">了解...</button><br>
						<input class="Input" name="SecurityEntrance" placeholder="安全入口" style="margin-top:5px;margin-bottom:5px" value="<?php echo $SecurityEntrance ?>"><br>
						伪静态模式[true表示启用]:<button type="button" onclick="alert('第三方提供方案,复制下面地址访问查看使用方法（仅支持软件的index.php在网站主目录时启用）    https://bbs.0rst.com/t/19.html')">了解...</button><br>
						<input class="Input" name="Rewrite" placeholder="伪静态开关" style="margin-top:5px;margin-bottom:5px" value="<?php echo $Rewrite ?>"><br>
						为了安全，附加函数请自行打开assets/plugin.php修改<br>
						<form>
					<?php elseif($_GET['article'] == "newPost"): ?>
			<div id="main-display">
			<div class="moudle" style="width:320px;margin:auto">
				<h3>输入文章名称</h3>
				<span>支持二级目录</span><br><br>
				<form method="POST" action="" name="CreatePost">
				<input type="hidden" name="action" value="createPost">
				<input class="Input" name="name" placeholder="输入文章名称" style="width:280px;margin-bottom:20px"><br>
				<button style="width:280px;margin-bottom:20px" class="Button2">创建</button>
				</form>
				</div>
			</div>
					<?php else: ?>
					<h2><?php echo $pageName; ?></h2>
						<form method="POST" action="">
						<input type="hidden" name="action" value="editPost">
					<button style="position:fixed;top:7px;right:15px" class="Button2">保存文档</button>

	<div id="md-content" style="z-index: 1 !important ;color:black !important">
	    <textarea name="content"><?php echo file_get_contents(DOCS_DIRECTORY.$_GET['article'].'.md') ?></textarea>
	</div>
						<form>

    <!-- 这里必须先引入jquery -->
	<script src="https://assets.3r60.top/Jquery/jquery-3.5.1.js"></script>
	<!-- 引入js -->
	<script src="./assets/editormd/editormd.min.js"></script>
	<script type="text/javascript">
       //初始化Markdown编辑器
	    var contentEditor;
	    $(function() {
	      contentEditor = editormd("md-content", {
	        width   : "100%",//宽度
	        height  : 500,//高度
	        syncScrolling : "single",//单滚动条
			  path    : "./assets/editormd/lib/"//依赖的包路径
	      });
	    });
	</script>
当前编辑器仍然存在某些细节问题，介意请继续直接修改文件

					<? endif; ?>
				<?php else: ?>
				<?php echo file_get_contents('https://assets.3r60.top/v2/page/notice.php') ?>
				<h2>Ris_Docs 后台管理</h2>
				欢迎来到瑞思后台管理，您可以在左侧选择"站点设置"来编辑站点设置或者文字标题来编辑文章<br>
				<button style="margin-top:10px" class="Button2" onclick="location.href = './admin.php?article=editSet'">站点设置</button>
				<button class="Button2" onclick="location.href = './admin.php?article=newPost'">新建文章</button>
				<button class="Button2" onclick="location.href = './admin.php?loginout=true'">退出后台</button>
				<? endif; ?>
			<?php else: ?>
			<div id="main-display">
			<div class="moudle" style="width:320px;margin:auto">
				<h3>请输入密码以验证身份</h3>
				<form method="POST" action="" name="AdminAuth">
				<input class="Input" type="password" name="password" placeholder="输入管理员密码" style="width:280px;margin-bottom:10px"><br>
				<button style="width:280px;margin-bottom:20px" class="Button2">确认</button>
				</form>
				</div>
			</div>
			<? endif; ?>
		</Main>
	</div>
	<script src="https://assets.3r60.top/v2/package-end.js">
	</script>
<?php
$vers = file_get_contents("https://backend.3r60.top/ver/vers.json");
$projects = json_decode($vers);
$ver = $projects->Ris_Docs->version;
$update = str_replace(PHP_EOL, '   ', $projects->Ris_Docs->update);
if($ver !== $localVersion){
    // echo "<script>  
    //     if (confirm('RisDocs新版本已发布: " . htmlspecialchars($ver) . ", 点击确认前往更新')) {  
    //         window.location.href = './install.php';  
    //     }  
    // </script>";
	echo '<script>notice("success","RisDocs新版本已发布: ' . htmlspecialchars($ver) . '&nbsp; <a href=\"./install.php\" style=\"color:blue !important\"><button>立即更新</button></a>&nbsp;<button onclick=\"alert(\''.$update.'\')\">查看新内容</button>")</script>';
	 }
?>
</body>

</html>

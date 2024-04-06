<?php 
//引入配置项
include './assets/function.php';
?>
	<!doctype html>
	<html lang="zh-CN">
		<head>
			<meta charset="UTF-8">
			<meta http-equiv="Content-Language" content="zh-CN">
			<meta http-equiv="X-UA-Compatible" content="IE=edge">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<title>
				<?php echo $pageName .' - '. $WebName ?>
			</title>
			<script src="https://assets.3r60.top/v2/package.js">
			</script>
			<?php if($Rewrite == "true"): ?>
			<link rel="stylesheet" href="//<?php echo $Http_Host_RW ?>/assets/ghmd.css">
			<link rel="stylesheet" href="//<?php echo $Http_Host_RW ?>/assets/style.css">
			<?php else: ?>
			<link rel="stylesheet" href="./assets/ghmd.css">
			<link rel="stylesheet" href="./assets/style.css">
			<?php endif; ?>
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
				站内导航
				<button class="onlyPhone btn-white" type="submit" onclick="toggleLead()"
				style="color: rgb(0, 0, 0);float: right;height: 30px;margin-right: 10px;">
					<i class="bi bi-list" style="font-size: 15px; color: rgb(0, 0, 0);">
					</i>
				</button>
			</h3>
			<?PHP loadDirectory("common"); ?>
				由
				<a href="https://github.com/PYLXU/Ris_Docs_APP/">
					Ris Docs
				</a>
				驱动
		</Lead>
		<!--主要部分-->
		<Main id="Main" class="markdown-body">
			<!--<code markdown>-->
			<?PHP if ($_GET[ 'article']=="" || $_GET[ 'article']=="home" ) { loadArticle('home'); } else { loadArticle($_GET[ 'article']); } ?>
				<!--</code>-->
		</Main>
	</div>
	<script src="https://assets.3r60.top/v2/package-end.js">
	</script>
</body>

</html>

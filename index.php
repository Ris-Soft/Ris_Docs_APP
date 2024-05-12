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
			<link rel="stylesheet" href="//<?php echo $Http_Host_RW ?>/assets/editormd/css/editormd.preview.css" />
			<?php else: ?>
			<link rel="stylesheet" href="./assets/ghmd.css">
			<link rel="stylesheet" href="./assets/style.css">
			<link rel="stylesheet" href="./assets/editormd/css/editormd.preview.css" />
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
			<code markdown>
			<?PHP loadDirectory("common"); ?>
			</code>
				由
				<a href="https://github.com/PYLXU/Ris_Docs_APP/">
					Ris Docs
				</a>
				驱动
		</Lead>
		<!--主要部分-->
		<Main id="Main" class="markdown-body" style="padding-top:6px">
		<span class="colorNoToggle" style="font-size:12px;color:rgb(180,180,180);width:100%;"><?php 
		echo $WebName.'>'.$pageName ?>&nbsp;&nbsp;&nbsp;
		<span id="toolBox">
			<a href="javascript:startPrint();" class="colorNoToggle">
			<i class="bi bi-printer colorNoToggle" style="font-size:15px;"></i>
			打印模式</a>&nbsp;&nbsp;
			<a href="javascript:copyUrl(window.location.href)" class="colorNoToggle"">
			<i class="bi bi-clipboard colorNoToggle" style="font-size:15px;"></i>
			复制链接</a>
		</span>
		</span>
            <div style="margin-top:10px" id="test-editormd-view">
               <textarea style="display:none" name="test-editormd-markdown-doc">
			   <?php if ($_GET[ 'article']=="" || $_GET[ 'article']=="home" ) { loadArticle('home'); } else { loadArticle($_GET[ 'article']); } ?>
			   </textarea>               
            </div>
		</Main>
	</div>
	<script src="https://assets.3r60.top/v2/package-end.js"></script>
	<script src="https://assets.3r60.top/Jquery/jquery-3.5.1.js"></script>
			<?php if($Rewrite == "true"): ?>
			<script src="//<?php echo $Http_Host_RW ?>/assets/editormd/lib/marked.min.js"></script>
        <script src="//<?php echo $Http_Host_RW ?>/assets/editormd/lib/marked.min.js"></script>
        <script src="//<?php echo $Http_Host_RW ?>/assets/editormd/lib/prettify.min.js"></script>
        <script src="//<?php echo $Http_Host_RW ?>/assets/editormd/lib/raphael.min.js"></script>
        <script src="//<?php echo $Http_Host_RW ?>/assets/editormd/lib/underscore.min.js"></script>
        <script src="//<?php echo $Http_Host_RW ?>/assets/editormd/lib/sequence-diagram.min.js"></script>
        <script src="//<?php echo $Http_Host_RW ?>/assets/editormd/lib/flowchart.min.js"></script>
        <script src="//<?php echo $Http_Host_RW ?>/assets/editormd/lib/jquery.flowchart.min.js"></script>
        <script src="//<?php echo $Http_Host_RW ?>/assets/editormd/editormd.js"></script>
			<?php else: ?>
        <script src="assets/editormd/lib/marked.min.js"></script>
        <script src="assets/editormd/lib/prettify.min.js"></script>
        <script src="assets/editormd/lib/raphael.min.js"></script>
        <script src="assets/editormd/lib/underscore.min.js"></script>
        <script src="assets/editormd/lib/sequence-diagram.min.js"></script>
        <script src="assets/editormd/lib/flowchart.min.js"></script>
        <script src="assets/editormd/lib/jquery.flowchart.min.js"></script>
        <script src="assets/editormd/editormd.js"></script>
			<script src="./assets/editormd/lib/marked.min.js"></script>
			<?php endif; ?>
        <script type="text/javascript">
            $(function() {
                var testEditormdView;
				    $(function() {
					testEditormdView = editormd.markdownToHTML("test-editormd-view", {
                        htmlDecode      : true, 
                        toc             : true,
                        tocm            : true,
                        emoji           : true,
                        taskList        : true,
                        tex             : true,  // 默认不解析
                        flowChart       : true,  // 默认不解析
                        sequenceDiagram : true,  // 默认不解析
                    });
					});
            });
</script>
<script>
function copyUrl(id) {
    $("body").after("<input id='copyVal'></input>");
    var text = id;
    var input = document.getElementById("copyVal");
    input.value = text;
    input.select();
    input.setSelectionRange(0, input.value.length);   
    document.execCommand("copy");
    $("#copyVal").remove();
}
function startPrint() {
	document.getElementById('toolBox').style.display = 'none';
	document.getElementById('Lead').style.display = 'none';
	document.getElementById('Topbar').style.display = 'none';
	document.getElementById('Main').style.marginTop = '0px';
	document.getElementById('Main').style.marginLeft = '0px';
	document.getElementsByClassName('container')[0].style.marginTop = '8px';
	document.getElementsByClassName('container')[0].style.marginTop = '8px';
	if (!darkMode){
		toggleLight();
	}
	print();
}
	function LoadMD(){
     var m = document.querySelectorAll('code[markdown]');
    for (var i = 0; i < m.length; i++) {
        m[i].outerHTML = marked.parse(m[i].innerHTML.trim());
    }
	}
	LoadMD();
	</script>
</body>

</html>

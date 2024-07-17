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
			<script src="https://assets.3r60.top/v3/package.js"></script>
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
    <topbar
        data-title='<?php echo $WebName ?>'
        data-homeUrl = '.'
        data-navRightItems='[{"text": "<i class=\"bi bi-info-circle\"></i>","href": "javascript:createDialog(\"alert\", \"primary\", \"关于<?php echo addslashes($WebName) ?>\", \"<?php echo $copyRight ? $copyRight.'<br>' : '' ?>软件版本：<?php echo $localVersion; ?><br><a href=\\\"https://docs.3r60.top/Project\\\">Ris_Docs</a>提供软件服务\")"}]'
        data-showExpendButton='false'
        data-loadCallBack='refreshMarkdownContent'>
    </topbar>
	<main class="flex pb-0">
		<lead>
		    <ul class="list" data-loadFromFile='false' data-changeTitle='false'>
			<?php loadDirectory("common"); ?>
			</ul>
			<footer></footer>
		</lead>
		<!--主要部分-->
		<content class="markdown-body" style="padding-top:6px">
		<span class='pagePath'><?php 
		echo $WebName.'>'.$pageName ?>&nbsp;&nbsp;&nbsp;
		<span id="toolBox">
			<a href="javascript:startPrint();" class="colorNoToggle">
			<i class="bi bi-printer colorNoToggle" style="font-size:15px;"></i>
			打印模式</a>&nbsp;&nbsp;
			<a href="javascript:copyUrl(window.location.href)">
			<i class="bi bi-clipboard colorNoToggle" style="font-size:15px;"></i>
			复制链接</a>
		</span>
		</span>
            <div style="margin-top:10px" id="test-editormd-view">
               <textarea style="display:none" name="test-editormd-markdown-doc">
			   <?php if ($_GET[ 'article']=="" || $_GET[ 'article']=="home" ) { loadArticle('home'); } else { loadArticle($_GET[ 'article']); } ?>
			   </textarea>               
            </div>
		</content>
	</main>
<script>
	let defaultTitle = '瑞思文档';
	let defaultNavItems = [];
	let defaultNavRightItems = [{"text": "<i class=\"bi bi-info-circle\"></i>","href": "javascript:createDialog(\"alert\", \"primary\", \"关于<?php echo addslashes($WebName) ?>\", \"<?php echo $copyRight ? $copyRight.'<br>' : '' ?>软件版本：<?php echo $localVersion; ?><br><a href=\\\"https://docs.3r60.top/Project\\\">Ris_Docs</a>提供软件服务\")"}];
	let defaultFooterLinks = [];
	let defaultCopyright = '版权所有 © 2024 腾瑞思智';
	let webTitle = '<?php echo addslashes($WebName) ?? '瑞思文档' ?>';
	$(document).ready(function() {
        $('.list a').on('click', function(event) {
            event.preventDefault();
            history.pushState('', '', this.href);
            fetchAndReplaceContent(this.href, 'title,content', 'title,content', ()=>{
                refreshMarkdownContent();
                setActiveLinkInList($('.list'));
            } );
            
        });
    });
</script>
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
			</script>
        <script type="text/javascript">
            $(function() {
            testEditormdView = editormd.markdownToHTML("test-editormd-view", {
                htmlDecode      : true, 
                toc             : true,
                tocm            : true,
                emoji           : true,
                taskList        : true,
                html            : true,
                tex             : true,  // 默认不解析
                flowChart       : true,  // 默认不解析
                sequenceDiagram : true,  // 默认不解析
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
    createMessage('复制链接成功','success');
}
function startPrint() {
	$("#toolBox").css('display' ,'none');
	$("lead").remove();
	$("topbar").remove();
	$('.pagePath').css('marginLeft','0px');
	$("content").css('marginTop' ,'0px');
	$("content").css('marginLeft', '0px');
	$("main").css('paddingTop', '0px');
	if (getCookie('set_colorMode') === 'dark'){
		colorMode('light',false);
	}
	print();
}
function refreshMarkdownContent() {
    newMarkdownContent = $('#test-editormd-view textarea').html();
    $('#test-editormd-view').html('');
    testEditormdView = editormd.markdownToHTML("test-editormd-view", {
        markdown        : newMarkdownContent,
        htmlDecode      : true, 
        toc             : true,
        tocm            : true,
        emoji           : true,
        html            : true,
        taskList        : true,
        tex             : true,  
        flowChart       : true,  
        sequenceDiagram : true,  
    });
}
	</script>
</body>

</html>

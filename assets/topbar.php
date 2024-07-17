<?php
// 懒加载
// ob_start();
echo '
<style>
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
#loading {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
    height: 100%;
    position: fixed;
    background:rgba(0,0,0,.2);
    top: 0;
    left: 0;
    opacity: 0;
    transition: opacity .2s;
    z-index:900;
}

#loading.active {
    opacity: 1;
}

#loading.hide {
    display: none;
}
#loading div {
    width: 52px;
    height: 52px;
    border: 5px solid transparent;
    border-top-color: rgba(50, 205, 50, .8);;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}
</style>
<div id="loading" class="active">
<div></div>
</div>
<script>
window.addEventListener(\'load\', function() {
    if (document.getElementById(\'loading\')) {
        document.getElementById(\'loading\').remove();
    }
});
</script>';
// ob_flush();
// flush();
?>
	<!--遮盖层-->
	<div class="back-under" id="back-under">
	</div>
	<!--顶栏-->
	<Topbar id="Topbar">
		<button type="submit" style="display:none" onclick="toggleMenu()">
			<i id="MenuButton" class="bi bi-list" style="font-size:25px;">
			</i>
		</button>
			<?php if($Rewrite == "true"): ?>
			<img src="//<?php echo $Http_Host_RW ?>/favicon.ico" alt="网站LOGO" height="40px" />
			<?php else: ?>
			<img src="favicon.ico" alt="网站LOGO" height="40px" />
			<?php endif; ?>
		
		<p>
			<?php echo $WebName ?>
		</p>
		<div style="margin-left:auto">
			<button class="btn-white" type="submit" onclick="toggleLight()">
				<i class="bi bi-moon-fill" style="font-size:15px;">
				</i>
			</button>
			<button class="btn-white" type="submit" onclick="toggleSet()">
				<i class="bi bi-gear-fill" style="font-size:15px;">
				</i>
			</button>
		</div>
	</Topbar>
	<!--主体部分-->
	<Left id="W-Menu">
	</Left>
<right id="W-Set" style="background-color: rgba(0, 0, 0, 0.5); display: none;" class="show">

        <!--版权部分-->
    <a href="<?php if($Rewrite == "true"): ?>
			//<?php echo $Http_Host_RW ?>/admin.php
			<?php else: ?>
			./admin.php
			<?php endif; ?>" class="w-80 Button2" style="margin-top: 20px; text-align: left">
        <?php if(empty($SecurityEntrance)): ?>
		<span class="display-flex" >
        <i class="bi bi-gear" style="font-size: 20px; margin-top: auto; margin-bottom: auto; margin-right: 10px"></i>
        <span>后台管理</span></span>
        </a>
        <a href="javascript:showdiv('verBox','verBoxToggle')" id="verBoxToggle" class="w-80 Button2" style="margin-top: 5px; text-align: left">
        <?php endif; ?>
		<span class="display-flex">
        <i class="bi bi-info-circle" style="font-size: 20px; margin-top: auto; margin-bottom: auto; margin-right: 10px"></i>
        <span>关于软件</span></span>
        </a>
        <div class="showhide" id="verBox" >
        <span><b >Ris_Docs</b><br >
        开源PHP文档系统[By PYLXU]<br >
			当前版本:
			<i>
				<?php echo $localVersion ?>
			</i></span>
        </div>
                        <a href="javascript:showdiv('infoBox','infoBoxToggle')" id="infoBoxToggle" class="w-80 Button2" style="margin-top: 5px; text-align: left">
        <span class="display-flex" >
        <i class="bi bi-app-indicator" style="font-size: 20px; margin-top: auto; margin-bottom: auto; margin-right: 10px"></i>
        <span>关于框架</span></span>
        </a>
        <div class="showhide" id="infoBox" >
                <span><b >RWUI V2</b><br >
        全部代码均由PYLXU书写<br >
        <i >© 2022-2024 腾瑞思智. All rights reserved.</i></span>
        </div>
            </right>
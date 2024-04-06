	<!--遮盖层-->
	<div class="back-under" id="back-under">
	</div>
	<!--顶栏-->
	<Topbar id="Topbar">
		<button type="submit" onclick="toggleMenu()">
			<i id="MenuButton" class="bi bi-list" style="font-size:25px;">
			</i>
		</button>
		<img src="favicon.ico" alt="网站LOGO" height="40px" />
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
		<h3>
			全局导航
		</h3>
		<ul class="list">
			<!--展示附加链接-->
			<li>
				<a href="<?php echo $HomeURL ?>">
					首页
				</a>
			</li>
			<?php if($BlogURL !="NoShow" ){echo '<li><a href="'.$BlogURL.'">博客</a></li>';}?>
			<?php if($GithubURL !="NoShow" ){echo '<li><a href="'.$GithubURL.'">Github</a></li>';}?>
			<?php if($ShowRefrush== true){echo '<li><a href="#" onclick="javascript:location.reload();">刷新</a></li>';}?>
		</ul>
	</Left>
	<Right id="W-Set">
		<h3>
			网站菜单
		</h3>
		<?php if(empty($SecurityEntrance)){echo '<button style="margin-top:10px" class="Button2" onclick="location.href = \'./admin.php?\'">后台管理</button>';}?>
		<!--关于部分-->
		<h4>
			关于程序
		</h4>
		<span>
			<b>
				Ris_Docs
			</b>
			<br>
			开源PHP文档系统[By PYLXU]
			<br>
			当前版本:
			<i>
				<?php echo $localVersion ?>
			</i>
		</span>
		<!--版权部分-->
		<h4>
			关于框架
		</h4>
		<span>
			<b>
				RWUI V2
			</b>
			<br>
			全部代码均由PYLXU书写
			<br>
			<i>
				© 2022-2024 腾瑞思智. All rights reserved.
			</i>
		</span>
	</Right>
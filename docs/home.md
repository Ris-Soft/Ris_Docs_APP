<hero>
	<index>
		<style>
		main index {
			padding: 120px 5px 120px;
			width: 100%;
			text-align: center;
			height: 30vh;
			display: block;
		}
		main index::before {
		  content: "";
		  position: absolute;
		  top: 0;
		  left: 0;
		  width: 100%;
		  height: 60vh;
		     background: url('https://api.3r60.top/v2/background/bing.php') center/cover no-repeat;
		  opacity: 0.5;
		  z-index: -1;
		  border-radius: 0px 0px 10px 10px;
          border-bottom: rgb(167 167 167 / 70%) solid 0.5px
		}
		main index h1 {
			font-size: 48px;
		}
		main index h3 {
			font-size: 28px;
		}
		</style>
            <h1>{{set('WebName')}}</h1>
            <h3>由 Ris_Docs 强力驱动</h3>
            <br>
            <center>
                <a href="./?article=default" style="position: relative; overflow: hidden; display: inline-block;"><button class="btn btn-success btn-lg" style="position: relative; overflow: hidden;">默认分类</button></a>
                <a href="./admin.php" style="position: relative; overflow: hidden; display: inline-block;"><button class="btn btn-success-e btn-lg" style="position: relative; overflow: hidden;">管理后台</button></a>
            </center>
	</index>
</hero>



### 文档目录

{{include('directory')}}


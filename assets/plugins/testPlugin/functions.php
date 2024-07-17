<?php if ($env_requestPage == 'user') : ?>
  <!--插件作用域判断-->
  <!--我们提供了PHP变量便于您快速获取当前请求页类型 user:用户界面  admin:管理界面 -->

  <!--开发方式一：使用JavaScript操作-->
  <!--注意：若您需要使用RWUIv3部分函数，因RWUIv3强制在尾部加载，因此你需要设定一个500ms的延时来执行，下面是一个插件例子-->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      setTimeout(() => {
        // createMessage("这是一条测试消息","success")
        console.log('插件[<?php echo $pluginName ?>]已加载');

        // RWUIv3支持您向顶栏添加元素以执行您的插件功能
        // addButtonToNavRight('bi bi-plugin','','#',()=>{createMessage("这是一条测试消息","success")

        // 您可以使用DOM操作来实现更多插件，Jquery会自动引入
        
        // 温馨提示：本软件使用PJAX实现热加载，您可能需要实时监听URL改动以实现您的功能
      }, 500)
    })
  </script>

<?php elseif ($env_requestPage == 'docsTransform') : ?>
  <?php
  // 开发方式二：使用我们提供的PHP接口
  // 此处对接接口：文档转换中间件

  // MD全文本变量 $input
  // 赋值变量给 $return 即可
     $return = $WebName;
  ?>

<?php elseif ($env_requestPage == 'docsPost') : ?>
  <!--此处对接接口：文档发布后执行-->
  <?php
    $docs = json_decode($input);
    $title = $docs->title;
    $content = $docs->content;
    // file_put_contents('./1.ini',$content)
  ?>
<?php endif; ?>
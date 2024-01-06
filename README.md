# 软件介绍

**本软件由PYLXU使用PHP开发的一套MD文档系统**

支持以下功能：
 - 1.给用户显示MD文档
 - 2.支持半自动整理目录
 - 3.定义化高，界面美观
 - 4.稳定更新，安全保障
 - 5.代码开源，隐私保障

# 快速上手

**功能说明请查看"功能测试"示例文档**

## 下载应用

下载方式1-通过**Github**下载 
访问后选择一个版本下载。

### 注意事项

- 本网站软件使用PHP，请确保您的服务器存在PHP环境

## 安装应用

下载ZIP压缩包后解压至目标网站根目录即可

目录框架：

```bash

├── docs                           // 文档目录,包含默认的文件
├── index.php                           // 程序主文件
├── config.php                          // 程序配置文件
├── md2html.php                          // MD转HTML核心
├── update-direct.php                          // 访问后可更新目录
├── ghmd.css                          // MD样式
```

## 配置说明

使用前建议先更改以下配置项：
 - [强烈建议]$AdminPassword 此项为访问目录更新文件时的验证密码,尽量复杂  
 - [必备]$WebName 此项为网站名称,随意(可包含HTML代码)
 - [可选]网站链接部分
 - [可选]自定义配置项(在MD文件内使用 {删除这些文字{set('UpdateTime')}} 可直接在MD文件中显示变量)


### 版权声明

全部代码均由PYLXU开发  
更多疑问请咨询QQ 753307914

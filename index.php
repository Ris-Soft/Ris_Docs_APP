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
        <?php echo $pageName . ' - ' . $WebName ?>
    </title>
    <script>
        let defaultTitle = '<?php echo $WebName ?>';
        let defaultNavItems = [
            {
                'text': "首页",
                'href': "<?php echo ($Rewrite == "true" ? '//' . $Http_Host_RW : '.') ?>",
            },
            <?php
            $categories = getCategories();
            $navItems = [];
            foreach ($categories as $category) {
                $displayName = $category === 'default' ? '文档' : $category;
                echo '{' .
                    'text: "' . $displayName . '",' .
                    'href: "' . ($Rewrite == "true" ? '//' . $Http_Host_RW . '/article/' . $category : './?article=' . $category) . '",' .
                    '},';
            }
            ?>
        ];
        let defaultNavRightItems = [
            {
                "text": "<i class=\"bi bi-search\"></i>",
                "href": `javascript:createDialog(\"type\", \"success\", \"内容搜索\", \"从所有分类中搜索标题标题与内容\", (text) => {searchContent(text)})`
            }
            , {
                "text": "<i class=\"bi bi-info-circle\"></i>",
                "href": `javascript:createDialog(\"alert\", \"primary\", \"关于<?php echo addslashes($WebName) ?>\", \"<?php echo $copyRight ? str_replace('"', "'", htmlspecialchars_decode($copyRight)) . '<br>' : '' ?>软件版本：<?php echo $localVersion; ?><br><a href=\\\"https://docs.3r60.top/Project\\\">Ris_Docs</a>提供软件服务\")`
            }, {
                "text": "<i class=\"bi bi-pen\"></i>",
                "href": `JavaScript:window.location.href = '<?php echo $Rewrite ? '//' . $Http_Host_RW . '/admin.php' : './admin.php' ?>'`
            }];
        let defaultFooterLinks = [];
        let defaultCopyright = `<?php echo $copyRight ? htmlspecialchars_decode($copyRight) : '版权所有 © 2025 腾瑞思智' ?>`;
        let webTitle = '<?php echo addslashes($WebName) ?? '瑞思文档' ?>';
    </script>
    <script src="https://assets.3r60.top/v3/package.js"></script>
    <link rel="stylesheet" href="https://assets.3r60.top/other/editormd/css/editormd.css" />
    <link rel="stylesheet"
        href="<?php echo $Rewrite ? '//' . $Http_Host_RW . '/assets/style.css' : './assets/style.css' ?>">
</head>

<body>
    <topbar data-homeUrl='<?php echo ($Rewrite == "true") ? '//' . $Http_Host_RW : '.' ?>' data-showExpendButton='false'
        data-noactive='true'>

    </topbar>
    <main class="flex pb-0">
        <?php if (isset($_GET['article']) && $_GET['article'] === 'home/')
            $_GET['article'] = "home" ?>
        <?php if (isset($_GET['article']) && $_GET['article'] === 'default/home')
            $_GET['article'] = "home" ?>
        <?php if (!empty($_GET['article']) && $_GET['article'] !== 'home'): ?>
            <?php
            // 检查当前分类是否只有一个文章并且不是目录
            $category = explode('/', $_GET['article'])[0];
            $mdFiles = glob("docs/{$category}/*.md");
            $singleArticle = count($mdFiles) === 1;

            // 如果不是单文件，且不是直接访问文件，则显示侧边栏
            $_GET['article'] = rtrim($_GET['article'], '/');
            if (!$singleArticle):
                ?>
                <lead>
                    <ul class="list" data-loadFromFile='false' data-changeTitle='false'>
                        <?php loadDirectory("common", $category); ?>
                    </ul>
                    <footer></footer>
                </lead>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!empty($_GET['article']) && $_GET['article'] !== 'home'): ?>
            <content class="markdown-body"
                style="padding-top:6px;<?php echo ($singleArticle) ? 'width:100%;margin-left: 0;' : ''; ?>">
                <?php includePlugin('user'); ?>
                <!-- Page Header -->
                <?php if (!$singleArticle): ?>
                    <span class='pagePath'>
                        <?php echo $WebName . '>' . $pageName ?>&nbsp;&nbsp;&nbsp;
                        <span id="toolBox">
                            <a href="javascript:startPrint();" class="colorNoToggle">
                                <i class="bi bi-printer colorNoToggle" style="font-size:15px;"></i>
                                打印模式
                            </a>&nbsp;&nbsp;
                            <a href="javascript:copyUrl(window.location.href)">
                                <i class="bi bi-clipboard colorNoToggle" style="font-size:15px;"></i>
                                复制链接
                            </a>
                        </span>
                    </span>
                <?php endif; ?>

                <!-- Article Container -->
                <div class="article-container">
                    <div style="margin-top:10px" id="test-editormd-view">
                        <?php
                        $article = $_GET['article'];
                        // 如果是单文件分类，直接重定向到该文件
                        if ($singleArticle && strpos($article, '/') === false) {
                            $file = basename($mdFiles[0], '.md');
                            $article = $category . '/' . $file;
                        }

                        $fullPath = "docs/" . str_replace('/', DIRECTORY_SEPARATOR, $article);

                        if (is_dir($fullPath)) {
                            $category = explode('/', $article)[0];
                            $indexFile = "docs/{$category}/index.md";

                            if (!file_exists($indexFile)) {
                                // Display category listing
                                echo "<h1>{$category}</h1><ul class='article-list'>";
                                foreach (glob("docs/{$category}/*.md") as $file) {
                                    $name = basename($file, '.md');
                                    if ($name !== 'index') {
                                        $title = trim(file_get_contents($file, false, null, 0, 1000));
                                        preg_match('/^#\s*(.+)$/m', $title, $matches);
                                        $title = $matches[1] ?? $name;
                                        $link = $Rewrite == "true" ?
                                            "//{$Http_Host_RW}/article/{$category}/{$name}" :
                                            "./?article={$category}/{$name}";
                                        echo "<li><a href='{$link}'>{$title}</a></li>";
                                    }
                                }
                                echo "</ul>";
                            } else {
                                // 处理index文件内容
                                $content = loadArticle($article, false);
                                if (preg_match('/<hero>(.*?)<\/hero>(.*)/s', $content, $matches)) {
                                    echo $matches[1];
                                    echo '<textarea style="display:none" name="test-editormd-markdown-doc">';
                                    echo $matches[2];
                                    echo '</textarea>';
                                } else {
                                    echo '<textarea style="display:none" name="test-editormd-markdown-doc">';
                                    echo $content;
                                    echo '</textarea>';
                                }
                            }
                        } else {
                            // 处理普通文章内容
                            $content = loadArticle($article, false);
                            // echo $article;
                            if (preg_match('/<hero>(.*?)<\/hero>(.*)/s', $content, $matches)) {
                                echo $matches[1];
                                echo '<textarea style="display:none" name="test-editormd-markdown-doc">';
                                echo $matches[2];
                                echo '</textarea>';
                            } else {
                                echo '<textarea style="display:none" name="test-editormd-markdown-doc">';
                                echo $content;
                                echo '</textarea>';
                            }
                        }
                        ?>
                    </div>
                    <?php if ($singleArticle) :?>
                        <footer></footer>
                    <?php endif; ?>
                    <?php if (!$singleArticle): ?>
                        <div class="article-toc" id="article-toc"></div>
                        <span class="toc-placeholder"></span>
                    <?php endif; ?>
                </div>
            </content>

        <?php else: ?>
            <span style="width: 100%;">
                <?php
                $content = loadArticle('home', false);
                if (preg_match('/<hero>(.*?)<\/hero>(.*)/s', $content, $matches)) {
                    echo $matches[1];
                    $mdContent = $matches[2];
                } else {
                    $mdContent = $content;
                }
                ?>
                <div class="markdown-body" style="width: 100%;">
                    <div class="article-container">
                        <div style="margin-top:10px" id="test-editormd-view">
                            <textarea style="display:none" name="test-editormd-markdown-doc">
                                                                    <?php echo $mdContent; ?>
                                                                </textarea>
                        </div>
                    </div>
                </div>
            </span>
        <?php endif; ?>
    </main>
    <style>
        .article-container {
            display: flex;
            gap: 20px;
        }

        #test-editormd-view {
            flex: 1;
        }

        .article-toc {
            width: 250px;
            padding: 15px;
            background: var(--surface);
            border-radius: 8px;
            max-height: calc(100vh - 100px);
            overflow-y: auto;
            position: fixed;
            top: 70px;
            right: 30px;
            z-index: 1000;
        }

        .toc-placeholder {
            width: 250px;
            padding: 15px;
            max-height: calc(100vh - 100px);
            overflow-y: hidden;
            position: sticky;
            top: 70px;
        }

        .article-toc ul {
            list-style: none;
            padding-left: 15px;
            margin-bottom: 0;
        }

        .article-toc>ul {
            padding-left: 0;
            margin-bottom: 0;
        }

        .article-toc a {
            color: var(--text);
            text-decoration: none;
            display: block;
            padding: 3px 0;
            transition: all 0.3s ease;
        }

        .article-toc a.active {
            color: var(--primary);
            font-weight: bold;
            background: var(--surface);
            border-radius: 4px;
            padding-left: 5px !important;
        }

        .article-toc p {
            color: var(--text);
            font-weight: 900;
            margin-bottom: 8px;
            font-size: 20px;
        }

        .article-toc a:hover {
            color: var(--primary);
        }

        @media (max-width: 768px) {
            .article-container {
                flex-direction: column;
            }

            .article-toc {
                width: 100%;
                max-height: none;
                position: static;
                order: -1;
            }

            .toc-placeholder {
                display: none;
            }
        }
    </style>
    <script>
        $(document).ready(function () {
            setTimeout(() => {
                $('a[href="./"]').attr('href', '<?php echo $_SERVER['DOCUMENT_ROOT'] ?>')
            }, 1000)
        });
    </script>
    <script src="https://assets.3r60.top/other/editormd/lib/marked.min.js"></script>
    <script src="https://assets.3r60.top/other/editormd/lib/marked.min.js"></script>
    <script src="https://assets.3r60.top/other/editormd/lib/prettify.min.js"></script>
    <script src="https://assets.3r60.top/other/editormd/lib/raphael.min.js"></script>
    <script src="https://assets.3r60.top/other/editormd/lib/underscore.min.js"></script>
    <script src="https://assets.3r60.top/other/editormd/lib/sequence-diagram.min.js"></script>
    <script src="https://assets.3r60.top/other/editormd/lib/flowchart.min.js"></script>
    <script src="https://assets.3r60.top/other/editormd/lib/jquery.flowchart.min.js"></script>
    <script src="https://assets.3r60.top/other/editormd/editormd.js"></script>
    <script type="text/javascript">

        function searchContent(text) {
            event.preventDefault();
            const search = text.toLowerCase();
            const url = '<?php echo ($Rewrite == "true") ? '//' . $Http_Host_RW : '.' ?>/?article=search:' + text;
            history.pushState('', '', url)
            fetchAndReplaceContent(url, 'title,main', 'title,main', () => {
                refreshMarkdownContent();
                setActiveLinkInList($('.list'));
            });

        }

        function loadToc() {
            // 处理目录
            const tocContainer = document.getElementById('article-toc');
            if (!tocContainer) return;
            const headings = document.querySelectorAll('.markdown-body h1, .markdown-body h2, .markdown-body h3, .markdown-body h4, .markdown-body h5, .markdown-body h6');
            const toc = document.createElement('ul');
            const counters = { h1: 0, h2: 0, h3: 0, h4: 0, h5: 0, h6: 0 };

            headings.forEach(heading => {
                const level = parseInt(heading.tagName.substring(1));
                const tag = heading.tagName.toLowerCase();
                counters[tag]++;
                
                // Generate unique ID based on text content
                const uniqueId = `${tag}-${counters[tag]}-${heading.textContent.toLowerCase().replace(/\s+/g, '-')}`;
                heading.id = uniqueId;

                const li = document.createElement('li');
                const a = document.createElement('a');
                a.href = `#${uniqueId}`;
                a.textContent = heading.textContent;
                a.style.paddingLeft = `${(level - 1) * 10}px`;
                li.appendChild(a);
                toc.appendChild(li);
            });

            tocContainer.innerHTML = '<p>目录</p>';
            tocContainer.appendChild(toc);

            return headings;
        }

        function addStyle(dark) {
            if (dark) {
                $('#test-editormd-view').addClass('editormd-preview-theme-dark');
                document.documentElement.style.setProperty('--surface', 'rgb(28 28 28 / 50%)');
                document.documentElement.style.setProperty('--background', '#222');
                document.documentElement.style.setProperty('--text', '#fff');
                document.documentElement.style.setProperty('--primary', 'rgba(255, 255, 255, 0.7)');
            } else {
                $('#test-editormd-view').removeClass('editormd-preview-theme-dark');
                document.documentElement.style.setProperty('--surface', 'rgba(230, 230, 230, 0.5)');
                document.documentElement.style.setProperty('--background', '#fff');
                document.documentElement.style.setProperty('--text', '#000');
                document.documentElement.style.setProperty('--primary', 'rgba(0, 0, 0, 0.7)');
            }
        }
        $(function () {
            refreshMarkdownContent();

            $(document).on('colorModeChanged', function (event, colorMode) {
                if (testEditormdView !== null) {
                    addStyle(colorMode == "dark");
                }
            });


            if (!location.hash) return;
            window.scrollTo({
                top: document.querySelector(location.hash).offsetTop - 60,
                behavior: 'smooth'
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
            createMessage('复制链接成功', 'success');
        }

        function startPrint() {
            $("#toolBox").css('display', 'none');
            $("lead").remove();
            $("topbar").remove();
            $('.pagePath').css('marginLeft', '0px');
            $("content").css('marginTop', '0px');
            $("content").css('marginLeft', '0px');
            $("main").css('padding', '0px');
            if (getCookie('set_colorMode') === 'dark') {
                colorMode('light', false);
            }
            print();
        }

        function refreshMarkdownContent() {
            testEditormdView = editormd.markdownToHTML("test-editormd-view", {
                htmlDecode: true,
                toc: true,
                tocm: true,
                emoji: true,
                htmlDecode: true,
                taskList: true,
                tex: true,
                flowChart: true,
                sequenceDiagram: true,
            });

            addStyle(getColorMode());

            setTimeout(() => {
                $('a[href="./"]').attr('href', '<?php echo ($Rewrite == "true") ? '//' . $Http_Host_RW : '.' ?>')
            }, 5000);

            $('.list a').off('click');
            $('.category-item').off('click');

            $('.list a').on('click', function (event) {
                event.preventDefault();
                history.pushState('', '', this.href);
                fetchAndReplaceContent(this.href, 'title,content', 'title,content', () => {
                    refreshMarkdownContent();
                    setActiveLinkInList($('.list'));
                });
            });

            $('.category-item').on('click', function (event) {
                event.preventDefault();
                history.pushState('', '', this.href);
                fetchAndReplaceContent(this.href, 'title,main', 'title,main', () => {
                    refreshMarkdownContent();
                    setActiveLinkInList($('.list'));
                });
            });

            const headings = loadToc();
            if (!headings) return;

            // 添加滚动监听
            const tocLinks = document.querySelectorAll('.article-toc a');
            const headingElements = Array.from(headings);

            window.addEventListener('scroll', () => {
                let currentHeading = null;
                const scrollPosition = window.scrollY;

                headingElements.forEach(heading => {
                    const headingTop = heading.offsetTop - 100;
                    if (scrollPosition >= headingTop) {
                        currentHeading = heading;
                    }
                });

                tocLinks.forEach(link => {
                    link.classList.remove('active');
                    if (currentHeading && link.getAttribute('href') === `#${currentHeading.id}`) {
                        link.classList.add('active');
                    }
                });
            });

            // 点击目录项时平滑滚动
            tocLinks.forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const targetId = link.getAttribute('href').slice(1);
                    const targetElement = document.getElementById(targetId);
                    if (!targetElement) return;
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                });
            });
        }

        function activeLink() {
            let currentUrl = window.location.href;
            let lastActive = null;
            $('.navCenter li a').each(function () {
                let href = $(this).attr('href');
                if (href === '.') {
                    if (currentUrl.endsWith('/') || currentUrl.endsWith('index.php')) {
                        lastActive = $(this).parent();
                    }
                } else if (href && decodeURIComponent(currentUrl).includes(decodeURIComponent(href.replace(/^\./, '')))) {
                    lastActive = $(this).parent();
                }
            });
            $('.navCenter .active').removeClass('active');
            if (lastActive) {
                lastActive.addClass('active');
            }
        }

        function waitforelement(selector, callback) {
            if ($(selector).length) {
                callback();
            } else {
                setTimeout(() => {
                    waitforelement(selector, callback);
                }, 100);
            }
        }

        $(document).ready(function () {
            waitforelement('.navCenter li a', () => {
                activeLink();
            });
        });

        $(document).on("pageChanged", function () {
            refreshMarkdownContent();
            activeLink();
        });
    </script>
</body>

</html>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>Evolution of Loneliness Guild-Homepage</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="apple-touch-icon" href="apple-touch-icon.png">
        <link rel="stylesheet" href="/themes/boilerplate/css/normalize.min.css">
        <link rel="stylesheet" href="/themes/boilerplate/css/main.css">
        <link rel="stylesheet" href="/themes/boilerplate/css/sidebar.css">
        <!--[if lt IE 9]>
            <script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
            <script>window.html5 || document.write('<script src="js/vendor/html5shiv.js"><\/script>')</script>
        <![endif]-->
    </head>
    <body>
        <!--[if lt IE 8]>
            <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
        <![endif]-->
        <div class="header-container">
            <header class="wrapper clearfix">
                <h1 class="title">{##header##}</h1>
                <nav>
                    {##nav##}
                </nav>
            </header>
        </div>
        <div class="main-container">
            <div class="wrapper main clearfix">
                <article>
                    {##main##}
                </article>
                <aside>
                    <nav>
                        {##user_nav##}
                        {##widgets##}
                    </nav>
                </aside>
            </div> <!-- #main -->
        </div> <!-- #main-container -->
        <div class="footer-container">
            <footer class="wrapper">
                {##footer##}
            </footer>
        </div>
    </body>
</html>

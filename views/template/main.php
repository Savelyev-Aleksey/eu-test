<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Some title</title>
        <link href="/css/main.css" rel="stylesheet" type="text/css">
        <script src="/js/jquery-3.2.1.min.js"></script>
        <script src="/js/bootstrap.min.js"></script>
    </head>
    <body>
        <header>
          <nav class="navbar navbar-default">
            <div class="container-fluid">
              <div class="navbar-header">
                <a class="navbar-brand" href="/">Goods</a>
                <button type="button" class="navbar-toggle collapsed"
                    data-toggle="collapse" aria-expanded="false"
                    data-target="#main-navbar" >
                  <span class="sr-only">Toggle navigation</span>
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
                </button>
              </div>
              <div class="collapse navbar-collapse" id="main-navbar">
                <?= Form::open(User::get_authorized_user(), ['method' => 'post',
                    'action' => '/user/logout', 'class' => 'navbar-form navbar-right']);
                ?>
                <?= Form::submit('Logout', ['class' => 'btn btn-default']); ?>
                <?= Form::close(); ?>
              </div>
            </div>

          </nav>

        </header>
        <div class="container"><?= $_content; ?>
        </div>
        <footer>
            Some footer
        </footer>
    </body>
</html>
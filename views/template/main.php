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
                <?php
                  $user = User::get_authorized_user();
                  if (isset($user)):
                ?>
                <?= Form::open($user, ['method' => 'post',
                    'action' => '/user/logout', 'class' => 'navbar-form navbar-right']);
                ?>
                <?= Form::hidden('logout', true); ?>
                <?= Form::submit('Logout', ['class' => 'btn btn-default']); ?>
                <?= Form::close(); ?>
                <?php endif; ?>
              </div>
            </div>

          </nav>
        </header>
        <div class="container">
          <?php
            $flash = Session::flash();
            if (isset($flash)): ?>
            <div class="alert alert-info alert-dismissible" role="alert">
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span></button>
              <?= $flash ?>
            </div>
          <?php
            endif; ?>
          
          <?= $_content; ?>
        </div>
        <footer>
            Some footer
        </footer>
    </body>
</html>
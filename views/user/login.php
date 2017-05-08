<div class="row">
  <div class="col-md-6 col-md-offset-3 col-xs-12">
    <div class="panel panel-primary">
      <div class="panel-heading">
        <h3 class="panel-title">Please login to proceed</h3>
      </div>
      <div class="panel-body">
        <form method="post" action="/user/login" id="user-login" class="form-horizontal">
          <div class="form-group">
            <label for="login" class="col-sm-2">Login</label>
            <div class="col-sm-10">
              <input type="text" name="login" id="login" placeholder="login" class="form-control"
                     value="<?php if (isset($login)) echo $login; ?>">
            </div>
          </div>

          <div class="form-group">
            <label for="password" class="col-sm-2">Password</label>
            <div class="col-sm-10">
              <input type="password" name="password" id="password" placeholder="Password" class="form-control">
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
              <button type="submit" class="btn btn-primary">Sign in</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php

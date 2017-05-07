<form method="post" action="/good/review" id="good-review" class="form-horizontal">
    <div class="form-group">
        <label for="login" class="col-sm-2">Login</label>
        <div class="col-sm-10">
            <input type="text" name="rating" id="login" placeholder="login" class="form-control"
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
            <button type="submit" class="btn btn-default">Sign in</button>
        </div>
    </div>
</form>

<select name="some">
  <option selected></option>
</select>
<?php
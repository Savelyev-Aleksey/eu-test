<?= Form::open($review, ['action' => '/good/review', 'method' => 'post',
    'class' => 'form-horizontal']); ?>

  <div class="form-group">
    <?= Form::hidden('good_id'); ?>
  </div>

  <div class="form-group">
    <?= Form::label('rate', 'Rating', ['class' => 'col-sm-2 control-label']); ?>
    <div class="col-sm-10">
      <?= Form::select('rate', [NULL, 1, 2, 3, 4, 5], NULL, ['class' => 'form-control']); ?>
    </div>
  </div>

  <div class="form-group">
    <?= Form::label('comment', 'Review', ['class' => 'col-sm-2 control-label']); ?>
    <div class="col-sm-10">
      <?= Form::textarea('comment', ['class' => 'form-control', 'rows' => 3]); ?>
    </div>
  </div>

  <div class="form-group">
    <div class="col-sm-offset-2 col-sm-10">
      <?php $glyth = '<span class="glyphicon glyphicon-send"></span>';?>
      <?= Form::submit("Save review $glyth", ['class' => 'btn btn-primary']); ?>
    </div>
  </div>



<?= Form::close();

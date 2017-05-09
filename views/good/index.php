<?php

if (isset($goods) && is_array($goods)):
?>
<div class="goods row">
<?php
  foreach ($goods as $good):
?>
  <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
    <section class="good-elem panel panel-info">
      <header class="panel-heading">
        <a href="/good/show/<?=$good->id;?>"><?= Render::out($good->name); ?></a>
      </header>
      <div class="panel-body">
 <?php
      $review = $good->good_reviews(["user_id={uid}",['{uid}' => $user->id]]);
      $review = count($review) ? $review[0] : new Good_Review(['good_id' => $good->id]);

      Render::view('good/_review_form',['review' => $review]);
      ?>
      </div>
    </section>
  </div>
<?php
  endforeach;
?>
</div>
<?php
endif;


<?php

if (isset($goods) && is_array($goods)):
?>
<div class="goods">
<?php
  foreach ($goods as $good):
?>
  <section class="good-elem">
    <header><a href="/good/show/<?=$good->id;?>"><?= $good->name; ?></a></header>
<?php
    $review = $good->good_reviews(["user_id={uid}",['{uid}' => $user->id]]);
    $review = count($review) ? $review[0] : new Good_Review(['user_id' => $user->id,
        'good_id' => $good->id]);

    echo Render::view('_review_form',['review' => $review]);
    ?>
  </section>
<?php
  endforeach;
?>
</div>
<?php
endif;


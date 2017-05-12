<header class="page-header">
  <h1 class="text-center text-capitalize"><?= $good->name; ?></h1>
</header>
<div class="row">
<?php
  $reviews = $good->good_reviews();
  $total_rate = 0;
  foreach ($reviews as $review):
?>
  <div class="col-md-6">
  <section class="panel panel-info">
    <header class="panel-heading">
      <div class="panel-title"><?= Render::out($review->user()->login); ?></div>
    </header>
    <div class="panel-body">
      <p><strong>Rating:</strong>
<?php
      $rate = $review->rate;
      $total_rate += $rate;
      for($i = 0; $i < $rate; $i++):
?>
        <span class="glyphicon glyphicon-star"></span>
<?php
      endfor;
      for($i = 5 - $rate; $i > 0; $i--):
?>
        <span class="glyphicon glyphicon-star-empty"></span>
<?php
      endfor;
?>
      </p>
      <header><strong>Comment:</strong></header>
      <p><?= Render::out($review->comment); ?></p>
    </div>
  </section>
  </div>
<?php
  endforeach;
 ?>
</div>
<section class="total-rating">
  <p><strong>Total rating:</strong> <?= $total_rate; ?></p>
  <?php $avg = count($reviews) ? $total_rate / count($reviews) : 0;?>
  <p><strong>Average rating:</strong> <?= number_format($avg, 1); ?></p>
</section>
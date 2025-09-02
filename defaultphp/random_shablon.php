<?php
ob_start();

$const1 = [
];


shuffle($const1);
$random_const1 = array_slice($const1, 0, 6);
?>

<section class="review">
  <div class="const1_title">
    <h2 class="text-wrapper-36">const</h2>
  </div>

  <div class="const1_container">
    <div class="swiper const1-swiper">
      <div class="swiper-wrapper">
        <?php foreach ($random_const1 as $review): ?>
          <div class="swiper-slide">
            <div class="review__item <?= isset($review['line_clamp']) && $review['line_clamp'] ? 'line-clamp-' . $review['line_clamp'] : '' ?>">
              <div class="review__item_header">
                <span class="review__item_image">
                  <img 
                    src="<?= htmlspecialchars($review['img']); ?>" 
                    alt="Const <?= htmlspecialchars($review['name']); ?>" 
                    loading="lazy"
                  >
                </span>
                <div class="review__item_info">
                  <p class="review__item_name"><?= htmlspecialchars($review['name']); ?></p>
                  <span class="review__item_rating rating-<?= (int)$review['rating']; ?>">★★★★★</span>
                  <span class="review__item_date"><?= htmlspecialchars($review['date']); ?></span>
                </div>
              </div>
              <div class="review__item_body">
                <div class="review__item_text"><?= htmlspecialchars($review['text']); ?></div>
                <a href="#" class="review__item_button"></a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>

      </div>
      <div class="swiper-pagination"></div>
    </div>
  </div>
</section>

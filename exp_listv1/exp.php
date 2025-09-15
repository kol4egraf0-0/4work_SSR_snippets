<!-- не обязательно факи, itemscope itemprop itemtype можно убрать, сразу с микроразметкой:D -->
<?php
$faq_items = [];

$i = 1;
while (isset(${'ques' . $i}) && isset(${'answ' . $i})) {
    $faq_items[] = [
        'question' => ${'ques' . $i},
        'answer' => ${'answ' . $i}
    ];
    $i++;
}
?>

<section class="faq" itemscope itemtype="https://schema.org/FAQPage">
  <div class="sec_title">
    <h2 class="text-wrapper-36">Заголовок</h2>
  </div>

  <div class="accordion-wrapper">
    <?php foreach ($faq_items as $index => $faq): ?>
    <div class="accordion" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
      <div class="accordion__header">
        <h4 itemprop="name"><?php echo htmlspecialchars($faq['question']); ?></h4>
        <svg class="accordion__icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M6 9L12 15L18 9"/>
        </svg>
      </div>
      <div class="accordion__body" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
        <p itemprop="text"><?php echo htmlspecialchars($faq['answer']); ?></p>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>


<!-- юзка -->
<?php 
        $ques1 = "1?";
        $answ1 = "1";

        $ques2 = "2?";
        $answ2 = "2";

        $ques3 = "3?";
        $answ3 = "3";

        include '../includes/file.php';
?>
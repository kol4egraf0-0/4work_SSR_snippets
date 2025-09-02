document.addEventListener('DOMContentLoaded', function () {
  // ====== Отслеживание времени на сайте ======
  const timeGoals = [
    { seconds: 60, goal: '60_sec' },
    { seconds: 180, goal: '180_sec' },
    { seconds: 300, goal: '300_sec' }
  ];

  const sentGoals = new Set();

  timeGoals.forEach(function (entry) {
    setTimeout(function () {
      if (
        typeof yaCounter97764629 !== 'undefined' &&
        yaCounter97764629.reachGoal &&
        !sentGoals.has(entry.goal)
      ) {
        yaCounter97764629.reachGoal(entry.goal);
        sentGoals.add(entry.goal);
        console.log(`Цель ${entry.goal} отправлена`);
      }
    }, entry.seconds * 1000);
  });

  // ====== Удаление href у активного пункта меню ======
  const currentPath = window.location.pathname.trim().replace(/\/+$/, '').replace(/^\/+/, '');

  document.querySelectorAll('.navigation__link').forEach(function (link) {
    const hrefRaw = link.getAttribute('href');
    if (!hrefRaw || hrefRaw === '#') return;

    const linkPath = hrefRaw.trim().replace(/^\.?\//, '').replace(/\/+$/, '');

    if (linkPath === currentPath) {
      link.removeAttribute('href');
      link.classList.add('navigation__link--active');
    }
  });

  // ====== свайпер  ======
  const reviewSlider = document.querySelector('.name-swiper');
  
  if (reviewSlider) {
    new Swiper(reviewSlider, {
      loop: true,
      slidesPerView: 1,
      spaceBetween: 20,
      pagination: {
        el: reviewSlider.querySelector('.swiper-pagination'),
        clickable: true,
      },
      autoplay: {
        delay: 5000,
        disableOnInteraction: false,
      },
      breakpoints: {
        768: {
          slidesPerView: 2,
          spaceBetween: 20,
        },
        992: {
          slidesPerView: 3,
          spaceBetween: 30,
        }
      }
    });
  }
  
  $('.review__item_button').click(function(e) {
    e.preventDefault();
    var $item = $(this).closest('.review__item');
    if ($item.hasClass('expanded')) {
      $item.removeClass('expanded');
    } else {
      $item.addClass('expanded');
    }
  });

  $('.review__item_text').each(function () {
    if ($(this).height() > 72) {
      $(this).closest('.review__item').addClass('line-clamp-3');
    }
  });
});

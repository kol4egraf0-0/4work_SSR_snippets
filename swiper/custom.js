  const reviewSlider = document.querySelector('.');
  
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
$(document).ready(function () {
    $('#experience-detail .section-content-gallery .slide-1 .owl-carousel').owlCarousel({
      loop: false,
      dots: false,
      nav: false,
      items: 1,
      margin: 12,
      smartSpeed: 400,
      responsive: {
        0: { items: 1, dots: false },
        540: { items: 2, dots: false }
      }
    });
  });
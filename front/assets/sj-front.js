/**
 * SJ Reviews — Front-end JS
 * Initialise Swiper pour chaque widget .sj-reviews[data-sj-slider]
 * Dépend de `swiper` (handle WP natif depuis WP 5.9)
 */
(function () {
  'use strict';

  function initReviewsWidgets() {
    document.querySelectorAll('.sj-reviews[data-sj-slider]').forEach(function (wrap) {
      var raw = wrap.getAttribute('data-sj-slider');
      if (!raw) return;

      var cfg;
      try { cfg = JSON.parse(raw); } catch (e) { return; }

      var swiperEl = wrap.querySelector('#' + cfg.uid);
      if (!swiperEl) return;

      // Pagination element
      var paginationEl = wrap.querySelector('#' + cfg.uid + '-pagination');

      // Arrows
      var prevEl = wrap.querySelector('#' + cfg.uid + '-prev');
      var nextEl = wrap.querySelector('#' + cfg.uid + '-next');

      // Si pas d'éléments dédiés, chercher les classes génériques dans le conteneur
      if (!prevEl) prevEl = wrap.querySelector('.sj-arrow--prev');
      if (!nextEl) nextEl = wrap.querySelector('.sj-arrow--next');

      var pagination = false;
      if (paginationEl && cfg.showDots) {
        if (cfg.dotsStyle === 'number') {
          // Pagination type fraction custom
          pagination = {
            el: paginationEl,
            type: 'fraction',
            renderFraction: function (currentClass, totalClass) {
              return '<span class="' + currentClass + '"></span>'
                + ' / '
                + '<span class="' + totalClass + '"></span>';
            },
          };
        } else {
          pagination = {
            el: paginationEl,
            type: 'bullets',
            clickable: true,
          };
        }
      }

      var navigation = false;
      if (cfg.showArrows && (prevEl || nextEl)) {
        navigation = {
          prevEl: prevEl || null,
          nextEl: nextEl || null,
        };
      }

      var autoplay = false;
      if (cfg.autoplay) {
        autoplay = {
          delay: cfg.delay || 4000,
          disableOnInteraction: false,
          pauseOnMouseEnter: true,
        };
      }

      /* global Swiper */
      if (typeof Swiper === 'undefined') {
        console.warn('[SJ Reviews] Swiper non disponible. Assurez-vous que le script `swiper` WordPress est chargé.');
        return;
      }

      // Détruit l'instance précédente si le widget est ré-initialisé (edit Elementor, ajout dynamique)
      if (swiperEl.swiper) {
        swiperEl.swiper.destroy(true, true);
      }

      new Swiper(swiperEl, {
        loop:         cfg.loop,
        speed:        cfg.speed || 500,
        slidesPerView: cfg.perView || 1,
        spaceBetween: cfg.spaceBetween || 24,
        autoplay:     autoplay,
        pagination:   pagination,
        navigation:   navigation,
        a11y: {
          prevSlideMessage: 'Avis précédent',
          nextSlideMessage: 'Avis suivant',
        },
        breakpoints: cfg.perView > 1 ? {
          0:   { slidesPerView: 1 },
          640: { slidesPerView: Math.min(2, cfg.perView) },
          1024:{ slidesPerView: cfg.perView },
        } : undefined,
      });
    });
  }

  // Init au DOMContentLoaded et après Elementor frontend
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initReviewsWidgets);
  } else {
    initReviewsWidgets();
  }

  // Ré-init après édition Elementor
  if (window.elementorFrontend) {
    window.elementorFrontend.hooks.addAction('frontend/element_ready/sj-reviews.default', function () {
      initReviewsWidgets();
    });
  }

  // Exposition publique pour réinit manuelle
  window.sjReviewsInit = initReviewsWidgets;
})();

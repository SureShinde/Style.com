require(['jquery', 'tinyslider'], function ($) {
  $(document).ready(function () {
    // ======= Reviews Slider Home Page
    var reviewSlides = $('.reviews-slideshow .review-slide').length;
    if (reviewSlides > 0) {
      var slider = tns({
        container: '.reviews-slideshow',
        items: 1,
        mouseDrag: true,
        loop: true,
        responsive: {
          0: {
            items: 1,
            nav: true,
            controls: false,
          },
          640: {
            nav: false,
            controls: true,
          },
        },
      });
    }

    // ======= Home page Go Down Button
    $('.arrow-down').on('click', function () {
      $('html, body').animate(
        {
          scrollTop: $($(this).attr('href')).offset().top,
        },
        500
      );
      return false;
    });

    // ======= Mobile Menu
    $('.nav-toggle').on('click', function () {
      $('.navigation-holder').addClass('opened-menu');
    });
    $('.close-mobile-menu-button').on('click', function () {
      $(this).parents('.navigation-holder').removeClass('opened-menu');
    });

    // ======= Slideshow Stylist Page
    var worksSlides = $(
      '.wk-mp-profile-works-slideshow .wk-mp-profile-works-slide'
    ).length;
    if (worksSlides > 0) {
      var sliderWorks = tns({
        container: '.wk-mp-profile-works-slideshow',
        mouseDrag: true,
        controls: true,
        responsive: {
          0: {
            items: 2,
            nav: false,
            controls: false,
          },
          640: {
            controls: true,
          },
          768: {
            edgePadding: 170,
          },
          1150: {
            edgePadding: 100,
            items: 4,
          },
        },
      });
    }

    var expertiseSlides = $(
      '.wk-mp-profile-expertise-slideshow .wk-mp-profile-expertise-slide'
    ).length;
    if (expertiseSlides > 0) {
      var sliderWorks = tns({
        container: '.wk-mp-profile-expertise-slideshow',
        nav: false,
        mouseDrag: true,
        responsive: {
          0: {
            items: 1,
            edgePadding: 90,
            gutter: 20,
            controls: false,
          },
          480: {
            items: 2,
            edgePadding: 70,
          },
          640: {
            items: 3,
            controls: true,
          },
          768: {
            items: 4,
            edgePadding: 50,
          },
          880: {
            items: 5,
            edgePadding: 0,
          },
          1024: {
            items: 6,
          },
          1200: {
            gutter: 30,
          },
        },
      });
    }

    var adviceSlides = $('.marketplace-seller-profile .advice-list li').length;
    if (adviceSlides > 0) {
      var sliderAdvice = tns({
        container: '.marketplace-seller-profile .advice-list',
        mouseDrag: true,
        navPosition: true,
        controls: false,
        nav: true,
        responsive: {
          0: {
            items: 1,
            edgePadding: 90,
          },
          480: {
            items: 2,
            edgePadding: 50,
          },
          640: {
            edgePadding: 0,
            items: 3,
          },
          840: {
            items: 4,
          },
          1024: {
            items: 6,
          },
        },
      });
    }

    // ======= Stylists Page
    $('.show-filters-button').on('click', function () {
      if ($(this).hasClass('filters-displayed')) {
        $('html, body').animate(
          {
            scrollTop: $('.wk-mp-design-stylists-sidebar').offset().top,
          },
          500
        );
        $(this).removeClass('filters-displayed');
        $(this).text('Show All');
      } else {
        $(this).addClass('filters-displayed');
        $(this).text('Show Less');
      }
      if ($(this).parent().find('.filter-options-holder').hasClass('opened')) {
        $(this).parent().find('.filter-options-holder').removeClass('opened');
      } else {
        $(this).parent().find('.filter-options-holder').addClass('opened');
      }
      return false;
    });

    const stylistSidebar = $('.wk-mp-design-stylists-sidebar');
    const stylistToolbar = $('.toobar-stylists');

    $('.show-filter').on('click', function () {
      if (stylistSidebar.hasClass('opened')) {
        stylistSidebar.removeClass('opened');
      } else {
        stylistSidebar.addClass('opened');
      }
    });
    $('.close-stylists-sidebar').on('click', function () {
      stylistSidebar.removeClass('opened');
    });

    $('.show-sorter').on('click', function () {
      if (stylistToolbar.hasClass('opened')) {
        stylistToolbar.removeClass('opened');
      } else {
        stylistToolbar.addClass('opened');
      }
    });
    $('.close-stylists-toolbar').on('click', function () {
      stylistToolbar.removeClass('opened');
    });

    $('.custom-select').each(function () {
      var $this = $(this),
        numberOfOptions = $(this).children('option').length;

      $this.addClass('select-hidden');
      $this.wrap('<div class="select"></div>');
      $this.after('<div class="select-styled"></div>');

      var $styledSelect = $this.next('div.select-styled');
      if ($this.children('option:selected').length) {
          $styledSelect.text($this.children('option:selected').text());
      } else {
          $styledSelect.text($this.children('option').eq(0).text());
      }

      var $list = $('<ul />', {
        class: 'select-options',
      }).insertAfter($styledSelect);

      for (var i = 0; i < numberOfOptions; i++) {
        $('<li />', {
          text: $this.children('option').eq(i).text(),
          rel: $this.children('option').eq(i).val(),
        }).appendTo($list);
      }

      var $listItems = $list.children('li');

      $styledSelect.click(function (e) {
        e.stopPropagation();
        $('div.select-styled.active')
          .not(this)
          .each(function () {
            $(this).removeClass('active').next('ul.select-options').hide();
          });
        $(this).toggleClass('active').next('ul.select-options').toggle();
      });

      $listItems.click(function (e) {
        e.stopPropagation();
        $listItems.removeClass('selected');
        $(this).addClass('selected');
        $styledSelect.text($(this).text()).removeClass('active');
        $this.val($(this).attr('rel'));
        $list.hide();
        //console.log($this.val());
      });

      $(document).click(function () {
        $styledSelect.removeClass('active');
        $list.hide();
      });
    });

    // ======= Input Placeholder

    // $('.field:not(.choice) input').each(function () {
    //   var inputValue = $(this).val();
    //   if (inputValue !== '') {
    //     $(this).parents('.field').addClass('focused');
    //   }
    // });

    // $('.field:not(.choice) input').focus(function () {
    //   $(this).parents('.field').addClass('focused');
    // });

    // $('.field:not(.choice) input').blur(function () {
    //   var inputValue = $(this).val();
    //   if (inputValue == '') {
    //     $(this).parents('.field').removeClass('focused');
    //   }
    // });
  });
});

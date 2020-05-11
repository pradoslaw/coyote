$(function () {
    'use strict';

    let handler = function () {
        if ($(window).scrollTop() > 150) {
            let breadcrumb = $('#breadcrumb-fixed');
            let css = {left: $('.navbar-brand').position().left, opacity: 1.0};

            if (!breadcrumb.length) {
                breadcrumb = $('.breadcrumb:first:visible').clone();

                // breadcrumb can be empty
                if ($.trim(breadcrumb.text()).length > 0) {
                    breadcrumb.attr({id: 'breadcrumb-fixed'}).css(css).hide().appendTo('body');

                    breadcrumb.slideDown('fast');
                }
            }
            else {
                breadcrumb.css(css);
            }
        }
        else {
            $('#breadcrumb-fixed').remove();
        }
    };

  let isMobile = (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent));

  if (!isMobile && $('.fixed-top').length === 1) {
    $(window).scroll(handler);
  }
});

function adjustHashOffset() {
  window.scrollTo(window.scrollX, window.scrollY - 58);
}

if (document.getElementsByClassName('fixed-top').length) {
  window.addEventListener('hashchange', adjustHashOffset);

  window.addEventListener('load', () => {
    if (window.location.hash) {
      adjustHashOffset();
    }
  });
}

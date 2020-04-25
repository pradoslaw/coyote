import '../components/subscribe';

$(() => {
    'use strict';

    $('a[data-toggle="tooltip"]').tooltip({trigger: 'hover'});

    $('a[data-toggle="lightbox"]').click(function() {
        require.ensure([], (require) => {
            require('ekko-lightbox/dist/ekko-lightbox');

            $(this).ekkoLightbox({
              left_arrow_class: '.fa .fa-angle-left .ekko-lightbox-prev',
              right_arrow_class: '.fa .fa-angle-right .ekko-lightbox-next',
            });
        });

        return false;
    });
});

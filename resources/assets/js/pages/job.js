import '../components/subscribe';

$(() => {
    'use strict';

    $('a[data-toggle="tooltip"]').tooltip({trigger: 'hover'});

    $('a[data-toggle="lightbox"]').click(function() {
        require.ensure([], (require) => {
            require('ekko-lightbox/dist/ekko-lightbox');

            $(this).ekkoLightbox();
        });

        return false;
    });
});

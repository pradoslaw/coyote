$(function() {
    $('#wrap').each(function() {
        require.ensure([], (require) => {
            require('perfect-scrollbar/jquery')($);

            let pending = false;

            $(this).perfectScrollbar().on('ps-y-reach-end', function() {
                if (pending) {
                    return;
                }

                let offset = $('#reputation').find('.reputation-item').length;
                pending = true;

                $.get(_config.reputation_url, {offset: offset}, function(html) {
                    $('#reputation').append(html);

                    pending = false;
                });
            });
        });
    });
});

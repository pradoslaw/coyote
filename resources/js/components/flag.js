$(function() {
    'use strict';

    /**
     * Show "flag to report" page
     */
    $('body').on('click', '.btn-report', function() {
        $.get($(this).attr('href'), {url: $(this).data('url'), metadata: $(this).data('metadata')}, function(html) {
            $(html).appendTo('body');

            $('#flag').find('.modal').modal('show');
        });

        return false;
    });

    /**
     * Close flagged post report
     */
    $('.alert-report').submit(function() {
        var url = $(this).attr('action');
        var $this = $(this);

        $.get(url, function(html) {
            $('body').append(html);

            $('#modal-report').modal('show').one('click', '.danger', function() {
                $.post(url);
                $this.fadeOut();

                $('#modal-report').modal('dispose').remove();
                $('.modal-backdrop').remove();
                return false;
            });
        });

        return false;
    });
});

import 'jquery-color-animation/jquery.animate-colors';
import Dialog from '../libs/dialog';
import Config from '../libs/config';

$(function() {
    $('textarea[name="text"]').each(function() {
        $(this).wikiEditor().prompt().fastSubmit().autogrow().pasteImage();
    });

    $('#wrap').each(function() {
        require.ensure([], (require) => {
            require('perfect-scrollbar/jquery')($);

            let overview = $('#overview');
            let pending = false;

            $(this).perfectScrollbar().scrollTop(overview.outerHeight());

            $(this).on('mouseenter', '.unread', () => {
                $(this).off('mouseenter');
                $(this).animate({backgroundColor: '#fff'});
            })
            .on('ps-y-reach-start', () => {
                if (pending === true) {
                    return;
                }

                pending = true;
                $.get(_config.infinity_url, {offset: $('.media', overview).length}, html => {
                    overview.prepend(html);

                    // jezeli nie ma wiecej wiadomosci, to ajax nie bedzie kolejny raz wyslany
                    if ($.trim(html) === '') {
                        $(this).off('ps-y-reach-start');
                    }

                    pending = false;
                });
            });
        });
    });

    $('#recipient').each(function() {
        $(this).autocomplete({
            url: $(this).data('prompt-url')
        });
    });

    $('.btn-delete-pm').click(function() {
        Dialog.confirm({
            message: $(this).data('confirm'),
            form: {
                attr: {
                    action: $(this).attr('href'),
                    method: 'post'
                },
                csrfToken: Config.csrfToken()
            }}
        )
        .show();

        return false;
    });

    $('#box-pm a[data-toggle="tab"]').click(function(e) {
        if ($(e.target).attr('aria-controls') == 'preview') {
            $('#preview').html('<i class="fa fa-spinner fa-spin fa-2x"></i>');

            $.post(_config.preview_url, {'text': $('textarea[name="text"]').val()}, html => {
                $('#preview').html(html);
            });
        }
    });
});

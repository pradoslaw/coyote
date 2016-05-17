$(function () {
    "use strict";
    
    /**
     * Custom tags
     */
    var applyTags = function(tags) {
        $('.col-subject').find('.tag-clouds a').each(function() {
            if ($.inArray($(this).data('tag'), tags) > -1) {
                $(this).parents('tr').addClass('tagged');
            }
        });
    };

    var filterTags = function(string) {
        return string.replace(new RegExp(',', 'g'), ' ').split(' ').filter(function(element) {
            return element !== '';
        });
    };

    $('#box-my-tags').on('click', '.btn-settings', function() {
        $('#box-my-tags').find('.tag-clouds').toggle();
        $('#tags-form').toggle().find('input[name="tags"]').inputFocus();
    })
    .on('click', '.btn-add', function() {
        $('#box-my-tags').find('.btn-settings').click();
    });

    $('#tags-form').submit(function() {
        var $form = $(this);
        var tags = $('input[name="tags"]', this).val();

        tags = filterTags(tags);
        $(':input', $form).attr('disabled', 'disabled');

        $.post($form.attr('action'), {'tags': tags}, function(html) {
            var object = $('#box-my-tags');

            object.find('.tag-clouds').replaceWith(html).show();
            $form.hide();

            $('.tagged').removeClass('tagged');
            applyTags(tags);
        }).always(function() {
            $(':input', $form).removeAttr('disabled');
        });

        return false;
    });

    var tags = $.trim($('#tags-form').find('input[name="tags"]').val());

    if (tags) {
        applyTags(filterTags(tags));
    }
});
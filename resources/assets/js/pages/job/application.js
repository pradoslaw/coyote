import initTinymce from '../../libs/tinymce';

$(function() {
    initTinymce();

    $('input[name="cv_placeholder"]').click(function() {
        $('input[name="cv"]').click();
    });

    $(':file').change(function() {
        $(this).next().val($(this).val());
    });
});

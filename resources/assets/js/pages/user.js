import '../plugins/uploader';

$(function () {
    $.uploader({
        onChanged: function(data) {
            $('.avatar img').attr('src', data.url);
        }
    });
});

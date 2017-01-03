require('perfect-scrollbar/jquery')($);

$(function () {
    $('#stream').perfectScrollbar({suppressScrollX: true});

    $('#forum-tabs a').click(function() {
        let index = $('#forum-tabs a').index(this);
        $.post(_config.settings_url, {'homepage_mode': index});
    });

    $('#reputation-tabs a').click(function() {
        let index = $('#reputation-tabs a').index(this);
        $.post(_config.settings_url, {'homepage_reputation': index});
    });
});

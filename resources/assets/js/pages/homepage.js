import PerfectScrollbar from 'perfect-scrollbar';

$(function () {
    new PerfectScrollbar(document.getElementById('stream'));

    let tabs = {'forum': $('#forum-tabs').find('a'), 'reputation': $('#reputation-tabs').find('a')};

    tabs.forum.click(function() {
        let index = tabs.forum.index(this);

        $.post(_config.settings_url, {'homepage_mode': index});
    });

    tabs.reputation.click(function() {
        let index = tabs.reputation.index(this);

        $.post(_config.settings_url, {'homepage_reputation': index});
    });
});

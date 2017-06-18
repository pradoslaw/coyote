let isOngoing = false;

$(document).ajaxStop(function() {
    isOngoing = false;
});

module.exports = function preventDuplicate(fn) {
    if (isOngoing) {
        return;
    }

    isOngoing = true;
    fn();
};

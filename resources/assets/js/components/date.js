Date.prototype.asInteger = function () {
    return String(this.getFullYear()) + String(this.getMonth() + 1) + String((this.getDate() < 10 ? '0' : '') + this.getDate());
};

Date.prototype.getHour = function () {
    return (this.getHours() < 10 ? '0' : '') + this.getHours();
};

Date.prototype.getMinute = function () {
    return (this.getMinutes() < 10 ? '0' : '') + this.getMinutes();
};

function getTimeSpan(remote) {
    return Math.abs(Math.round((new Date().getTime() / 1000) - remote));
}

function getDiffMinute(remote) {
    return Math.round(getTimeSpan(remote) / 60);
}

function getDiffSecond(remote) {
    return getTimeSpan(remote);
}

var countTime = function () {
    $('.timestamp[data-timestamp]').each(function () {
        var timestamp = $(this).attr('data-timestamp');

        if (getDiffMinute(timestamp) < 60) {
            if (getDiffSecond(timestamp) >= 60) {
                $(this).text(getDiffMinute(timestamp) + ' ' + declination(getDiffMinute(timestamp), ['minuta', 'minuty', 'minut']) + ' temu');
            }
            else {
                $(this).text(getDiffSecond(timestamp) + ' ' + declination(getDiffSecond(timestamp), ['sekunda', 'sekundy', 'sekund']) + ' temu');
            }
        }
        else {
            var currDate = new Date((new Date().getTime()));
            var currValue = currDate.asInteger();

            var spanDate = new Date(timestamp * 1000);
            var spanValue = spanDate.asInteger();

            if (spanValue == currValue) {
                $(this).text('dzisiaj, ' + spanDate.getHour() + ':' + spanDate.getMinute());
            }
            else if (spanValue == currValue - 1) {
                $(this).text('wczoraj, ' + spanDate.getHour() + ':' + spanDate.getMinute());
            }
        }
    });
};
setInterval(countTime, 30000); // 30 sek

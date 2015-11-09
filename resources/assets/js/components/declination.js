/**
 * Umozliwia odmiane polskich wyrazow
 *
 * @param value
 * @param declinationSet
 * @returns {*}
 */
function declination(value, declinationSet) {
    if (value == 1) {
        return declinationSet[0];
    }
    else {
        var unit = value % 10;
        var decimal = Math.round((value % 100) / 10);

        if ((unit == 2 || unit == 2 || unit == 3 || unit == 4) && (decimal != 1)) {
            return declinationSet[1];
        }
        else {
            return declinationSet[2];
        }
    }
}
var Session =
{
    isSupported: function () {
        return ('localStorage' in window && window.localStorage !== null);
    },

    setItem: function (key, value) {
        if (this.isSupported()) {
            try {
                localStorage.setItem(key, value);
            }
            catch (e) {
            }
        }
    },

    getItem: function (key) {
        var value = null;

        if (this.isSupported()) {
            try {
                value = localStorage.getItem(key);
            }
            catch (e) {
            }
        }

        return value;
    },

    removeItem: function (key) {
        if (this.isSupported()) {
            localStorage.removeItem(key);
        }
    },

    addListener: function (callback) {
        if (this.isSupported()) {
            try {
                window.addEventListener('storage', callback, true);
            }
            catch (e) {
            }
        }
    }
};

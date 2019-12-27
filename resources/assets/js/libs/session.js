class Session
{
    static isSupported() {
        return ('localStorage' in window && window['localStorage'] !== null);
    }

    static setItem(key, value) {
        if (this.isSupported()) {
            try {
                localStorage.setItem(key, value);
            }
            catch (e) {
                console.error(e);
            }
        }
    }

    static getItem(key, _default = null) {
        let value = null;

        if (this.isSupported()) {
            try {
                value = localStorage.getItem(key);
            }
            catch (e) {
                console.error(e);
            }
        }

        return value === null ? _default : value;
    }

    static removeItem(key) {
        if (this.isSupported()) {
            localStorage.removeItem(key);
        }
    }

    static addListener(callback) {
        if (this.isSupported()) {
            try {
                window.addEventListener('storage', callback, true);
            }
            catch (e) {
                console.error(e);
            }
        }
    }
}

module.exports = Session;

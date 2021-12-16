class Session {
  static isSupported() {
    try {
      return ('localStorage' in window && window['localStorage'] !== null);
    } catch (err) {
      console.log(err);

      return false;
    }
  }

  static setItem(key, value) {
    if (this.isSupported()) {
      localStorage.setItem(key, value);
    }
  }

  static getItem(key, _default = null) {
    let value = null;

    if (this.isSupported()) {
      value = localStorage.getItem(key);
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
      } catch (e) {
        console.error(e);
      }
    }
  }
}

module.exports = Session;

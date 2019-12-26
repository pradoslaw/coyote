let Config = {

  /**
   * Get config value.
   *
   * @param {string} name
   * @param _default
   * @return {*}
   */
  get: function (name, _default = null) {
    return name in _config ? _config[name] : _default;
  },

  /**
   * Get link to CDN url.
   *
   * @param {string} url
   * @return {string}
   */
  cdn: function (url) {
    return this.get('cdn') + url.startsWith('/') ? url : ('/' + url);
  },

  /**
   * @return {string}
   */
  csrfToken() {
    return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  }
};

export default Config;

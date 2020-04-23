/**
 * @deprecated
 * @type {{csrfToken(): string, get: (function(string, *=): *)}}
 */
const Config = {

  /**
   * Get config value.
   *
   * @param {string} name
   * @param _default
   * @return {*}
   */
  get: function (name, _default = null) {
    return name in __INITIAL_STATE ? __INITIAL_STATE[name] : _default;
  },

  /**
   * @return {string}
   */
  csrfToken() {
    return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  }
};

export default Config;

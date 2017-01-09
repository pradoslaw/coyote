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
     * @return {jQuery}
     */
    csrfToken() {
        return $('meta[name="csrf-token"]').attr('content');
    }
};

export default Config;

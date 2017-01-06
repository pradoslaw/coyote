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
    }
};

export default Config;

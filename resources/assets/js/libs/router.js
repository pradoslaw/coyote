class Router {
    constructor() {
        this._location = window.location.pathname.toLocaleLowerCase() || '/';
        this._routes = {};
    }

    /**
     * Add rule to router.
     *
     * @param {string|Array} path
     * @param cb
     * @return {Router}
     */
    on(path, cb) {
        if (Array.isArray(path)) {
            path.forEach(item => this._routes[item] = cb);
        } else {
            this._routes[path] = cb;
        }

        return this;
    }

    /**
     * Make a callback based on page path.
     */
    resolve() {
        for (let path in this._routes) {
            if (this._routes.hasOwnProperty(path)) {
                let re = new RegExp(this._compile(path), 'i');

                if (re.test(this._location)) {
                    this._routes[path]();

                    break;
                }
            }
        }
    }

    /**
     * Make a regexp from path string.
     *
     * @param {string} path
     * @return {string}
     * @private
     */
    _compile(path) {
        if (!path.startsWith('/')) {
            path = '/' + path;
        }

        return '^' + path.replace('*', '.*') + '$';
    }
}

export default Router;

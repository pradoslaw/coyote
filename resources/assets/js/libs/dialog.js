
class Button {
    /**
     * @param options
     */
    constructor(options) {
        let defaultOptions = {
            label: '',
            attr: {},
            onClick: () => {}
        };

        this._options = $.extend(defaultOptions, options);
    }

    get label() {
        return this._options.label;
    }

    get attr() {
        return this._options.attr;
    }

    get onClick() {
        return this._options.onClick;
    }

    set onClick (onClick) {
        this._options.onClick = onClick;
    }

    build() {
        return $('<button></button>').attr(this.attr).text(this.label).on('click', this.onClick);
    }
}

class Dialog {
    /**
     * @param options
     */
    constructor(options) {
        let defaultOptions = {
            title: '',
            message: '',
            buttons: [],
            form: {
                attr: {
                    action: '',
                    method: 'POST'
                },
                csrfToken: ''
            }
        };

        this._options = $.extend(defaultOptions, options);
        this.build();
    }

    /**
     * @return {string}
     */
    get title() {
        return this._options.title;
    }

    /**
     * @return {string}
     */
    get message() {
        return this._options.message;
    }

    /**
     * Get button by label name
     *
     * @param {string} label
     * @return {object}
     */
    getButton(label) {
        return this._options.buttons.filter(button => button.label == label)[0];
    }

    /**
     * Show dialog.
     */
    show() {
        this._modal.on('hidden.bs.modal', () => this.destroy()).modal('show');
    }

    /**
     * Close dialog
     */
    close() {
        this._modal.modal('hide');
        this.destroy();
    }

    /**
     * Remove modal from DOM.
     */
    destroy() {
        this._modal.remove();
    }

    /**
     * Build dialog and return jQuery object.
     *
     * @return {Dialog}
     */
    build() {
        this._modal = this._createDiv('modal fade').attr({role: 'dialog', 'aria-labelledby': 'alert-model', 'aria-hidden': true, tabindex: -1});

        let dialog = this._buildDialog();
        let content = this._buildContent();

        content
            .append(this._buildTitle())
            .append(this._buildBody())
            .append(this._buildFooter());

        dialog.append(content);

        this._modal.append(dialog);
        this._modal.appendTo('body');

        if (this._options.form.attr.action) {
            this._modal.wrap(() => {
                return $('<form>').attr(this._options.form.attr);
            });

            if (this._options.form.csrfToken) {
                this._modal.append('<input type="hidden" name="_token" value="' + this._options.form.csrfToken + '">');
            }
        }

        return this;
    }

    /**
     * @return {jQuery}
     * @private
     */
    _buildTitle() {
        return $('<h4></h4>')
            .addClass('modal-title')
            .text(this.title)
            .wrap('<div class="modal-header"></div>')
            .parent();
    }

    /**
     * @return {jQuery}
     * @private
     */
    _buildFooter() {
        let footer = this._createDiv('modal-footer');

        this._options.buttons.forEach(button => {
            footer.append(new Button(button).build());
        });

        return footer;
    }

    /**
     * @return {jQuery}
     * @private
     */
    _buildBody() {
        return this._createDiv('modal-body').html(this.message);
    }

    /**
     * @return {jQuery}
     * @private
     */
    _buildDialog() {
        return this._createDiv('modal-dialog');
    }

    /**
     * @return {jQuery}
     * @private
     */
    _buildContent() {
        return this._createDiv('modal-content');
    }

    /**
     * @param {string} className
     * @return {jQuery}
     * @private
     */
    _createDiv(className) {
        return $('<div></div>').addClass(className);
    }

    /**
     * Factory method.
     *
     * @param options
     * @return {Dialog}
     */
    static alert(options) {
        return new Dialog($.extend({
            title: 'Błąd',
            buttons: [{
                label: 'OK',
                attr: {
                    'class': 'btn btn-secondary',
                    'type': 'button',
                    'data-dismiss': 'modal'
                }
            }]
        }, options));
    }

    /**
     * Factory method.
     *
     * @param options
     * @return {Dialog}
     */
    static confirm(options) {
        return new Dialog($.extend({
            title: 'Czy chcesz kontynuować?',
            buttons: [{
                label: 'Anuluj',
                attr: {
                    'class': 'btn btn-secondary',
                    'type': 'button',
                    'data-dismiss': 'modal'
                }
            }, {
                label: 'Tak, usuń',
                attr: {
                    'class': 'btn btn-danger',
                    'type': 'submit',
                    'data-submit-state': 'Usuwanie...'
                }
            }]
        }, options));
    }
}

export default Dialog;

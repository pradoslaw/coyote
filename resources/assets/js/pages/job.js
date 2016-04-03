
class Filter {
    constructor(form) {
        this.form = $(form);

        this.onFilterClick();
    }

    onFilterClick() {
        let self = this;

        this.form.on('click', '.list-group-item a', (e) => {
            let checkbox = $(e.currentTarget).prev(':checkbox');

            checkbox.attr('checked', !checkbox.is(':checked'));
            self.onSubmit();
            return false;
        });
    }

    onSubmit() {
        this.form.find('form').submit();
    }
}

$(() => {
    'use strict';

    new Filter('#box-filter');

    $('.btn-subscribe').click((e) => {
        $(e.currentTarget).toggleClass('on');

        $.post($(e.currentTarget).attr('href')).fail((e) => {
            $('#modal-unauthorized').modal('show');
        });

        return false;
    });

    $('#editor').on('shown.bs.modal', (e) => {
        $('#tags').tag({
            validateUrl: _config.validateUrl,
            promptUrl: _config.promptUrl
        });

        $(e.currentTarget).off('shown.bs.modal');
    });

    $('#form-preferences').on('submit', (e) => {
        $.post($(e.currentTarget).attr('action'), $(e.currentTarget).serialize(), () => {
            $('#editor').modal('hide');
        })
        .fail((e) => {

        });

        return false;
    });

    $('#btn-editor').click((e) => {
        $('#editor').modal('show');

        return false;
    });
});
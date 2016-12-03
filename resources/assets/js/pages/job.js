
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

    $('#editor').on('shown.bs.modal', (e) => {
        $('#tags').tag({
            promptUrl: _config.promptUrl
        });

        $(e.currentTarget).off('shown.bs.modal');
    });

    /**
     * Save preferences form
     */
    $('#form-preferences').on('submit', (e) => {
        let form = $(e.currentTarget);

        $.post(form.attr('action'), form.serialize(), (url) => {
            $('#editor').modal('hide');
            window.location.href = url;
        })
        .fail((e) => {
            $('.has-error', form).removeClass('has-error');
            $('.help-block', form).text('');

            let errors = e.responseJSON;

            Object.keys(errors).forEach((key) => {
                form.find(`[data-column="${key}"]`).addClass('has-error').find('.help-block').text(errors[key][0]);
            });

            $(':submit', form).enable();
        });

        return false;
    });

    $('#btn-editor').tooltip({html: true, trigger: 'hover', placement: 'right'}).click(() => {
        $('#editor').modal('show');

        return false;
    });
});

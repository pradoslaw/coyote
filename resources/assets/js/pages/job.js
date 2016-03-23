
class Filter {
    constructor(form) {
        this.form = form;

        this.onFilterClick();
    }

    onFilterClick() {
        this.form.on('click', '.list-group-item a', (e) => {
            let checkbox = $(e.currentTarget).prev(':checkbox');

            checkbox.attr('checked', !checkbox.is(':checked'));
            this.onSubmit();
            return false;
        });
    }

    onSubmit() {
        this.form.find('form').submit();
    }
}

$(() => {
    'use strict';

    new Filter($('#box-filter'));

    //$('#box-filter').on('submit', 'form', () => {
    //
    //})

});
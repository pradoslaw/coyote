function initTinymce() {
    tinymce.init({
        selector: "textarea",
        //height: 150,
        plugins: [
            "advlist lists spellchecker",
            "code",
            "paste",
            "autoresize"
        ],

        toolbar1: "bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | cut copy paste | bullist numlist | undo redo | outdent indent",

        menubar: false,
        toolbar_items_size: 'small',
        elementpath: false,
        statusbar: false,
        force_br_newlines: false,
        force_p_newlines: false,
        forced_root_block: '',
        autoresize_bottom_margin: 20,

        content_style: "body, * {font-size: 14px !important; font-family: Arial, sans-serif !important;}",

        setup: function (ed) {
            ed.on('init', function (args) {
                if ('recruitment' === args.target.id) {
                    $('input[name="enable_apply"]:checked').trigger('change');
                }
            });
        }
    });
}

initTinymce();
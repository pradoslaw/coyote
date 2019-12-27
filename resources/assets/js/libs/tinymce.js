export default {
    selector: "textarea",
    plugins: [
        "advlist lists spellchecker",
        "code",
        "paste",
        "autoresize"
    ],

    toolbar1: "bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | cut copy paste | bullist numlist | undo redo | outdent indent | code",

    menubar: false,
    toolbar_items_size: 'small',
    elementpath: false,
    statusbar: false,
    force_br_newlines: false,
    force_p_newlines: false,
    forced_root_block: '',
    autoresize_bottom_margin: 20,
    // relative_urls: false,
    convert_urls: false,

    indentation : '16px',
    content_style: "body, * {font-size: 14px !important; font-family: Arial, sans-serif !important;}",

    paste_as_text: true
};

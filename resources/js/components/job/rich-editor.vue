<template>
  <vue-tinymce v-model="valueLocal" :init="options"/>
</template>

<script lang="ts">
import VueTinyMce from "@tinymce/tinymce-vue";
import mixin from "../mixins/form.js";

export default {
  mixins: [mixin],
  components: {
    'vue-tinymce': VueTinyMce,
  },
  data() {
    return {
      options: {
        selector: "textarea",
        plugins: ["advlist lists spellchecker", "code", "paste", "autoresize"],
        toolbar1: "bold italic underline strikethrough | bullist numlist | undo redo | outdent indent",
        menubar: false,
        toolbar_items_size: 'small',
        elementpath: false,
        statusbar: false,
        force_br_newlines: false,
        force_p_newlines: false,
        forced_root_block: '',
        autoresize_bottom_margin: 20,
        convert_urls: false,
        indentation: '16px',
        content_style: "body, * {font-size: 14px !important; font-family: Arial, sans-serif !important;}",
        paste_as_text: true,
      },
    };
  },
};
</script>

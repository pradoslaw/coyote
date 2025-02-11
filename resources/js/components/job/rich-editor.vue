<template>
  <vue-tinymce
    :api-key="apiKey"
    v-model="valueLocal"
    :init="options"/>
</template>

<script lang="ts">
import VueTinyMce from "@tinymce/tinymce-vue";
import mixin from "../mixins/form.js";

export default {
  mixins: [mixin],
  components: {
    'vue-tinymce': VueTinyMce,
  },
  props: {
    modelValue: {type: String},
  },
  data() {
    return {
      apiKey: atob('NXRqNW1mang1MDY4MXI2MnNreGhxa2g2dzlnMGl4Y2trdGg3eDl1bHVoYmI2bDl1'),
      options: {
        selector: 'textarea',
        plugins: ['lists', 'codesample', 'link'],
        toolbar1: "bold italic underline strikethrough | bullist numlist | undo redo | outdent indent",
        menubar: false,
        toolbar_items_size: 'small',
        elementpath: false,
        statusbar: false,
        force_br_newlines: false,
        force_p_newlines: false,
        forced_root_block: '',
        autoresize_bottom_margin: 10,
        convert_urls: false,
        indentation: '16px',
        content_style: "body, * {font-size: 14px !important; font-family: Arial, sans-serif !important;}",
        paste_as_text: true,
      },
    };
  },
};
</script>

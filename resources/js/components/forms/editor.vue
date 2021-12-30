<template>
  <div class="editor-4play" ref="view">
  </div>
</template>

<script>
  import { Editor4Play } from "@riddled/4play/src/Editor.js";

  export default {
    props: {
      value: {type: String, required: true},
      placeholder: {type: String, require: true},
      autocompleteSource: {type: Function, required: true},
      smartPaste: {type: Boolean, required: true},
    },

    data() {
      return {
        editor: null
      };
    },

    mounted() {
      this.editor = new Editor4Play(
        this.$refs.view,
        this.placeholder,
        this.value,
        {
          onChange: content => this.$emit('input', content),
          onTextPaste: pasteAsMarkdown => pasteAsMarkdown(this.smartPaste),
          onImagePaste: file => this.$emit('image', file),
          onSubmit: content => this.$emit('submit', content),
          onCancel: () => this.$emit('cancel'),
          onStateChange: state => this.$emit('state', state),
        },
        username => this.autocompleteSource(username)
      );
    },

    methods: {
      insertImage(href, title) {
        this.editor.insertImage(href, title);
      },
      makeBold() {
        this.editor.makeBold();
      },
      makeItalics() {
        this.editor.makeItalics();
      },
      makeStrikeThrough() {
        this.editor.makeStrikeThrough();
      },
      appendBlockQuote(content) {
        this.editor.appendBlockQuote(content);
      },
      focus() {
        this.editor.focus();
      }
    },

    watch: {
      value(newValue) {
        if (newValue === '') {
          this.editor.clear();
        }
      }
    },

    destroy() {
      this.editor.destroy();
    }
  };
</script>

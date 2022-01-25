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
      insertLink(href, title) {
        this.editor.insertLink(href, title);
      },
      makeBold() {
        this.editor.putBold();
      },
      makeItalics() {
        this.editor.putItalics();
      },
      makeUnderline() {
        this.editor.putUnderline();
      },
      makeStrikeThrough() {
        this.editor.putStrikeThrough();
      },
      insertBlockQuote(placeholder) {
        this.editor.putBlockQuote(placeholder);
      },
      makeLink(placeholder) {
        this.editor.putLink(placeholder);
      },
      makeImage(placeholder) {
        this.editor.putImage(placeholder);
      },
      makeKeyNotation(key) {
        this.editor.putKey(key);
      },
      appendBlockQuote(content) {
        this.editor.appendBlockQuote(content);
      },
      insertListOrdered(placeholder) {
        this.editor.putListOrdered(placeholder);
      },
      insertListUnordered(placeholder) {
        this.editor.putListUnordered(placeholder);
      },
      insertCodeBlock() {
        this.editor.putCodeBlock();
      },
      addTable(header, placeholder) {
        this.editor.putTable(index => header + ' ' + (index + 1), placeholder);
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

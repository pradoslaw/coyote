<template>
  <vue-prompt :source="source">
    <textarea
      v-autosize
      class="form-control"
      :maxlength="maxLength + 16"
      :placeholder="placeholder"
      :disabled="disabled"
      rows="1"

      :value="value"
      @input="input"

      v-paste:success="allowPaste ? paste : null"
      @keydown.ctrl.enter="save"
      @keydown.meta.enter="save"
      @keydown.esc="cancel"

      ref="textarea"
    />
    <slot/>
  </vue-prompt>
</template>

<script lang="ts">
import {autosizeDirective} from "../plugins/autosize.js";
import {pasteDirective} from "../plugins/paste.js";
import VuePrompt from "./forms/prompt.vue";

export default {
  props: {
    value: {type: String},
    source: {type: String, required: true},
    maxLength: {type: Number},
    placeholder: {type: String},
    disabled: {type: Boolean},
    allowPaste: {type: Boolean, default: false},
  },
  components: {
    'vue-prompt': VuePrompt,
  },
  directives: {
    paste: pasteDirective('/assets'),
    autosize: autosizeDirective,
  },
  methods: {
    input(event) {
      this.$emit('input', event.target.value);
    },
    focus() {
      this.$refs.textarea.focus();
    },
    save() {
      this.$emit('save');
    },
    cancel() {
      this.$emit('cancel');
    },
    paste(event) {
      this.$emit('paste', event);
    },
    inspect(inspector: (element: HTMLTextAreaElement) => void): void {
      inspector(this.$refs.textarea);
    },
  },
};
</script>

<template>
  <div class="position-relative">
    <textarea
      v-autosize
      class="comment-form"
      :maxlength="maxLength + 16"
      :placeholder="placeholder"
      :disabled="disabled"
      rows="1"

      :value="modelValue"
      @input="input"

      v-paste:success="allowPaste ? paste : null"
      @keydown.ctrl.enter="save"
      @keydown.meta.enter="save"
      @keydown.esc="cancel"

      @keyup="onKeyUp"
      @click="onKeyUp"
      @keydown="onKeyDown"

      ref="textarea"
    />
    <slot/>
    <vue-dropdown ref="dropdown" :items="items" @select="selectItem" :rect="rect"/>
  </div>
</template>

<script lang="ts">
import useBrackets from "../libs/prompt";
import Textarea from "../libs/textarea";
import {autosizeDirective} from "../plugins/autosize.js";
import {pasteDirective} from "../plugins/paste.js";
import store from "../store/index";
import {SpecialKeys} from "../types/keys";
import VueDropdown from "./forms/dropdown.vue";

export default {
  store,
  emits: ['save', 'cancel', 'paste'],
  model: {
    prop: 'modelValue',
    event: 'update:modelValue',
  },
  props: {
    modelValue: {type: String},
    source: {type: String, required: true},
    maxLength: {type: Number},
    placeholder: {type: String},
    disabled: {type: Boolean},
    allowPaste: {type: Boolean, default: false},
  },
  components: {
    'vue-dropdown': VueDropdown,
  },
  directives: {
    paste: pasteDirective('/assets'),
    autosize: autosizeDirective,
  },
  data() {
    return {
      items: [],
      rect: {left: 0, top: 0},
    };
  },
  computed: {
    isDropdownVisible() {
      return this.$refs.dropdown?.isDropdownVisible;
    },
  },
  methods: {
    input(event: InputEvent): void {
      this.$emit('update:modelValue', event.target.value);
    },
    focus(): void {
      this.$refs.textarea.focus();
    },
    save(): void {
      this.$emit('save');
    },
    cancel(): void {
      this.$emit('cancel');
    },
    paste(event): void {
      this.$emit('paste', event);
    },
    inspect(inspector: (element: HTMLTextAreaElement) => void): void {
      inspector(this.$refs.textarea);
    },

    onKeyUp(event: KeyboardEvent) {
      const caretPosition = this.getCaretPosition();
      const startIndex = this.getUserNamePosition(caretPosition);

      if (this.isDropdownVisible) {
        if (event.keyCode === SpecialKeys.ESC) {
          this.items = [];
          return;
        }
        if (event.keyCode === SpecialKeys.DOWN) {
          this.$refs.dropdown.goDown();
          return;
        }
        if (event.keyCode === SpecialKeys.UP) {
          this.$refs.dropdown.goUp();
          return;
        }
        if (event.keyCode === SpecialKeys.ENTER || event.keyCode === SpecialKeys.TAB) {
          const item = this.$refs.dropdown.getSelected();
          if (item) {
            this.applySelected(item.name, startIndex, caretPosition);
          }
          return; // item was selected so there is not point to look up for user name.
        }
      }

      // min length to search is 1 character
      if (startIndex > -1 && caretPosition - startIndex >= 1) {
        this.lookupName(this.$refs.textarea.value.substring(startIndex, caretPosition));
        return;
      }

      if (this.items?.length) {
        store.commit('prompt/cancel');
        this.items = [];
      }
    },

    selectItem(item: object): void {
      const caretPosition = this.getCaretPosition();
      const startIndex = this.getUserNamePosition(caretPosition);
      this.applySelected(item.name, startIndex, caretPosition);
    },

    onKeyDown(event: KeyboardEvent): void {
      if (this.isDropdownVisible && [SpecialKeys.ENTER, SpecialKeys.TAB, SpecialKeys.DOWN, SpecialKeys.UP, SpecialKeys.ESC].indexOf(event.keyCode) !== -1) {
        event.preventDefault();
      }
    },

    getCaretPosition(): number {
      return this.$refs.textarea.selectionStart;
    },

    getUserNamePosition(caretPosition: number) {
      let i = caretPosition;
      let result = -1;

      while (i > caretPosition - 50 && i > 0) {
        let char = this.$refs.textarea.value[i - 1];

        if (char === ' ' || char === "\n") {
          break;
        }
        // we must check if @ is not a part of email address
        else if (char === '@' && (i === 1 || (this.$refs.textarea.value[i - 2] === ' ' || this.$refs.textarea.value[i - 2] === "\n"))) {
          result = i;
          break;
        }

        i--;
      }
      return result;
    },
    lookupName(name: string): void {
      store.dispatch('prompt/request', {source: this.source, value: name}).then(items => {
        this.items = items;
        const {top, left} = new Textarea(this.$refs.textarea).getCaretCoordinates();
        this.rect = {top, left};
      });
    },
    applySelected(text: string, startIndex: number, caretPosition: number): void {
      if (!text.length) {
        this.items = [];
        return;
      }

      const append = useBrackets(text) + (startIndex === 1 ? ': ' : ' '); // add space at the end

      this.$refs.textarea.value = this.$refs.textarea.value.substring(0, startIndex) + append + this.$refs.textarea.value.substring(caretPosition);
      this.$refs.textarea.focus(); // when user clicks the item, we must restore focus on input
      this.$refs.textarea.dispatchEvent(new Event('input', {'bubbles': true}));
      this.items = []; // setting to empty array will trigger dropdown watcher

      let caret = startIndex + append.length;

      this.$refs.textarea.setSelectionRange(caret, caret);
    },
  },
};
</script>

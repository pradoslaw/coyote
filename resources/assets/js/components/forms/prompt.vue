<template>
  <div class="position-relative">
    <slot></slot>

    <vue-dropdown ref="dropdown" :items="items" @select="selectItem"></vue-dropdown>
    <vue-error :message="errors"></vue-error>
  </div>
</template>

<script>
  import VueDropdown from './dropdown.vue';
  import VueError from './error.vue';
  import axios from 'axios';
  import { SpecialKeys } from '../../types/keys.ts';

  export default {
    components: { 'vue-dropdown': VueDropdown, 'vue-error': VueError },
    props: {
      source: {
        type: String
      },
      errors: {
        type: Array,
        default: () => []
      }
    },
    data() {
      return {
        input: null,
        items: [],
        timerId: null
      }
    },
    mounted() {
      this.input = this.$slots.default[0].elm;

      this.input.addEventListener('keyup', this.onKeyUp);
      this.input.addEventListener('click', this.onKeyUp); // bind one listener to two events
      this.input.addEventListener('keydown', this.onKeyDown);
    },
    computed: {
      isDropdownVisible() {
        return this.$refs.dropdown.isDropdownVisible;
      }
    },
    methods: {
      onKeyUp(e) {
        let userName = '';

        const keyCode = e.keyCode;
        const caretPosition = this.getCaretPosition();
        const startIndex = this.getUserNamePosition(caretPosition);

        if (this.isDropdownVisible) {
          if (keyCode === SpecialKeys.ESC) {
            this.items = [];
          } else if (keyCode === SpecialKeys.DOWN) {
            this.$refs.dropdown.goDown();
          } else if (keyCode === SpecialKeys.UP) {
            this.$refs.dropdown.goUp();
          } else if (keyCode === SpecialKeys.ENTER) {
            const item = this.$refs.dropdown.getSelected();

            if (item) {
              this.applySelected(item.name, startIndex, caretPosition);
            }

            this.$refs.dropdown.hideDropdown();

            // item was selected so there is not point to look up for user name.
            return;
          }
        }

        if (startIndex > -1) {
          userName = this.input.value.substr(startIndex, caretPosition - startIndex);
        }

        this.lookupName(userName);
      },

      selectItem(item) {
        const caretPosition = this.getCaretPosition();
        const startIndex = this.getUserNamePosition(caretPosition);

        this.applySelected(item.name, startIndex, caretPosition);
      },

      onKeyDown(e) {
        if (this.isDropdownVisible && Object.values(SpecialKeys).indexOf(e.keyCode) !== -1) {
          e.preventDefault();
        }
      },

      getCaretPosition() {
        if (this.input.selectionStart || this.input.selectionStart === 0) {
          return this.input.selectionStart;
        }
        else if (document.selection) {
          this.input.focus();
          const sel = document.selection.createRange();

          sel.moveStart('character', -this.input.value.length);
          return (sel.text.length);
        }
      },

      getUserNamePosition(caretPosition) {
        let i = caretPosition;
        let result = -1;

        while (i > caretPosition - 50 && i >= 0) {
          let $val = this.input.value[i];

          if ($val === ' ') {
            break;
          }
          else if ($val === '@') {
            if (i === 0 || this.input.value[i - 1] === ' ' || this.input.value[i - 1] === "\n") {
              result = i + 1;
              break;
            }
          }
          i--;
        }

        return result;
      },

      lookupName(name) {
        if (name.length < 2) {
          this.items = [];

          return;
        }

        clearTimeout(this.timerId);

        this.timerId = setTimeout(() => axios.get(this.source, {params: {q: name}}).then(response => this.items = response.data.data), 200);

      },

      applySelected(text, startIndex, caretPosition) {
        if (!text.length) {
          this.items = [];

          return;
        }

        if (text.indexOf(' ') > -1 || text.indexOf('.') > -1) {
          text = '{' + text + '}';
        }

        if (startIndex === 1) {
          text += ': ';
        }

        this.input.value = this.input.value.substr(0, startIndex) + text + this.input.value.substring(caretPosition);
        this.input.focus();
        this.input.dispatchEvent(new Event('change', {'bubbles': true}));

        let caret = startIndex + text.length;

        if (this.input.setSelectionRange) {
          this.input.setSelectionRange(caret, caret);
        } else if (this.input.createTextRange) {
          let range = this.input.createTextRange();

          range.collapse(true);
          range.moveEnd('character', caret);
          range.moveStart('character', caret);
          range.select();
        }
      }
    }
  }
</script>

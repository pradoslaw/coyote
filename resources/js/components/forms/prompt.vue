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
  import { SpecialKeys } from '../../types/keys.ts';
  import store from '../../store';
  import useBrackets from "../../libs/prompt";

  export default {
    components: { 'vue-dropdown': VueDropdown, 'vue-error': VueError },
    store,
    props: {
      source: {
        type: String,
        default: '/User/Prompt'
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

            return; // break the code
          } else if (keyCode === SpecialKeys.DOWN) {
            this.$refs.dropdown.goDown();

            return;
          } else if (keyCode === SpecialKeys.UP) {
            this.$refs.dropdown.goUp();

            return;
          } else if (keyCode === SpecialKeys.ENTER || keyCode === SpecialKeys.TAB) {
            const item = this.$refs.dropdown.getSelected();

            if (item) {
              this.applySelected(item.name, startIndex, caretPosition);
            }

            // item was selected so there is not point to look up for user name.
            return;
          }
        }

        if (startIndex > -1) {
          userName = this.input.value.substr(startIndex, caretPosition - startIndex);
          this.lookupName(userName);

          return;
        }

        if (this.items.length) {
          store.commit('prompt/cancel');

          this.items = [];
        }
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
        return this.input.selectionStart;
      },

      getUserNamePosition(caretPosition) {
        let i = caretPosition;
        let result = -1;

        while (i > caretPosition - 50 && i >= 0) {
          let $val = this.input.value[i];

          if ($val === ' ' || $val === "\n") {
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
        store.dispatch('prompt/request', { source: this.source, value: name }).then(items => this.items = items);
      },

      applySelected(text, startIndex, caretPosition) {
        if (!text.length) {
          this.items = [];

          return;
        }

        text = useBrackets(text);
        text += startIndex === 1 ? ': ' : ' '; // add space at the end

        this.input.value = this.input.value.substr(0, startIndex) + text + this.input.value.substring(caretPosition);
        this.input.focus(); // when user clicks the item, we must restore focus on input
        this.input.dispatchEvent(new Event('input', {'bubbles': true}));
        this.items = []; // setting to empty array will trigger dropdown watcher

        let caret = startIndex + text.length;

        this.input.setSelectionRange(caret, caret);
      }
    }
  }
</script>

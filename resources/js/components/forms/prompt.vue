<template>
  <div class="position-relative">
    <slot></slot>

    <vue-dropdown ref="dropdown" :items="items" @select="selectItem"></vue-dropdown>
  </div>
</template>

<script>
  import VueDropdown from './dropdown.vue';
  import { SpecialKeys } from '@/types/keys';
  import store from '@/store';
  import useBrackets from "@/libs/prompt";
  import Textarea from "@/libs/textarea";

  export default {
    components: { 'vue-dropdown': VueDropdown },
    store,
    props: {
      source: {
        type: String,
        default: '/completion/prompt/users'
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

        // min length to search is 1 character
        if (startIndex > -1 && caretPosition - startIndex >= 1) {
          this.lookupName(this.input.value.substr(startIndex, caretPosition - startIndex));

          return;
        }

        if (this.items?.length) {
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
        if (this.isDropdownVisible && [SpecialKeys.ENTER, SpecialKeys.TAB, SpecialKeys.DOWN, SpecialKeys.UP, SpecialKeys.ESC].indexOf(e.keyCode) !== -1) {
          e.preventDefault();
        }
      },

      getCaretPosition() {
        return this.input.selectionStart;
      },

      getUserNamePosition(caretPosition) {
        let i = caretPosition;
        let result = -1;

        while (i > caretPosition - 50 && i > 0) {
          let char = this.input.value[i - 1];

          if (char === ' ' || char === "\n") {
            break;
          }
          // we must check if @ is not a part of email address
          else if (char === '@' && (i === 1 || (this.input.value[i - 2] === ' ' || this.input.value[i - 2] === "\n"))) {
            result = i;
            break;
          }

          i--;
        }

        return result;
      },

      lookupName(name) {
        store.dispatch('prompt/request', { source: this.source, value: name }).then(items => {
          this.items = items;

          if (!(this.input instanceof HTMLTextAreaElement)) {
            return;
          }

          const rect = new Textarea(this.input).getCaretCoordinates();

          this.$refs['dropdown'].$el.style.top = rect.top + 'px';
          this.$refs['dropdown'].$el.style.left = rect.left + 'px';
        });
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

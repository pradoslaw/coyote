<template>
  <div class="position-relative">
    <input
      ref="autocomplete"
      type="search"
      class="form-control"
      autofocus
      :class="{'is-invalid': errors.length}"
      :id="id"
      :placeholder="placeholder"
      :tabindex="tabindex"
      autocomplete="off"
      v-model="valueLocal"
      @click="emitFocus"
      @focus="emitFocus"
      @keyup.up.prevent="$refs.dropdown.goUp"
      @keyup.down.prevent="$refs.dropdown.goDown"
      @keyup.esc="toggleDropdown(false)"
      @keydown.enter.prevent="changeItem"
    >

    <vue-dropdown ref="dropdown" :items="items" @select="selectItem"></vue-dropdown>
    <vue-error :message="errors"></vue-error>
  </div>
</template>

<script>
  import { default as mixins } from '../mixins/form';
  import VueDropdown from './dropdown.vue';
  import VueError from './error.vue';
  import axios from "axios";

  export default {
    mixins: [ mixins ],
    components: { 'vue-dropdown': VueDropdown, 'vue-error': VueError },
    props: {
      placeholder: {
        type: String
      },
      id: {
        type: String
      },
      value: {
        type: String,
        default: ''
      },
      tabindex: {
        type: Number
      },
      errors: {
        type: Array,
        default: () => []
      },
      typing: {
        type: Function,
        default: (value) => {
          if (!value.trim().length) {
            return Promise.resolve([]);
          }

          return axios.get('/User/Prompt', {params: {q: value}}).then(response => {
            let items = response.data.data;

            if (items.length === 1 && items[0].name.toLowerCase() === value.toLowerCase()) {
              items = [];
            }

            return items;
          });
        }
      }
    },
    data: () => ({
      items: []
    }),
    mounted() {
      this.$refs.autocomplete.addEventListener('search', this.changeItem);
    },
    methods: {
      emitFocus() {
        this.$emit('focus');
      },

      selectItem(item) {
        this.$emit('select', item);
        this.toggleDropdown(false);
      },

      changeItem() {
        this.$emit('select', this.$refs.dropdown.getSelected());

        this.toggleDropdown(false);
      },

      toggleDropdown(flag) {
        this.$refs.dropdown.toggleDropdown(flag);
      }
    },
    watch: {
      valueLocal: function (newValue, oldValue) {
        if (newValue && oldValue && newValue.toLowerCase() === oldValue.toLowerCase()) {
          return;
        }

        this.typing(newValue).then(items => this.items = items);
      }
    }
  }
</script>

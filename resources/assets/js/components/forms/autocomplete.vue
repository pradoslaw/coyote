<template>
  <div class="position-relative">
    <input
      type="text"
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
      @keyup.esc="$refs.dropdown.hideDropdown"
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

  export default {
    mixins: [ mixins ],
    components: { 'vue-dropdown': VueDropdown, 'vue-error': VueError },
    props: {
      items: {
        type: Array,
        default: () => []
      },
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
      }
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
        const selected = this.$refs.dropdown.getSelected();

        if (selected) {
          this.$emit('select', selected);
        }

        this.toggleDropdown(false);
      },

      toggleDropdown(flag) {
        this.$refs.dropdown.toggleDropdown(flag);
      }
    }
  }
</script>

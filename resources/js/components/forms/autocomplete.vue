<template>
  <div class="position-relative">
    <input
      ref="autocomplete"
      type="search"
      class="form-control"
      autofocus
      :class="{'is-invalid': errors.length}"
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
    <vue-dropdown ref="dropdown" :items="items" @select="selectItem"/>
    <vue-error :message="errors"/>
  </div>
</template>

<script>
import store from '../../store/index';
import {default as mixins} from '../mixins/form.js';
import VueDropdown from './dropdown.vue';
import VueError from './error.vue';

export default {
  mixins: [mixins],
  store,
  components: {'vue-dropdown': VueDropdown, 'vue-error': VueError},
  props: {
    placeholder: {type: String},
    modelValue: {type: String, default: ''},
    tabindex: {type: Number},
    errors: {type: Array, default: () => []},
  },
  data: () => ({
    items: [],
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
      this.$emit('select', this.$refs.dropdown.getSelected() ?? {name: this.modelValue});
      this.toggleDropdown(false);
    },
    toggleDropdown(flag) {
      this.$refs.dropdown.toggleDropdown(flag);
    },
    handler(value) {
      return store.dispatch('prompt/request', {value, source: '/completion/prompt/users'});
    },
  },
  watch: {
    valueLocal(newValue, oldValue) {
      if (newValue && oldValue && newValue.toLowerCase() === oldValue.toLowerCase()) {
        return;
      }
      this.handler(newValue).then(items => this.items = items);
    },
  },
};
</script>

<template>
  <input
    :type="type"
    :name="name"
    v-model="valueLocal"
    class="form-control" :class="{'is-invalid': isInvalid}"
    ref="input"
    @keydown.enter="accept"
    @blur="leave"/>
</template>

<script>
import {default as mixins} from '../mixins/form';

export default {
  props: {
    name: {
      type: String,
      require: true,
    },
    modelValue: {
      type: [String, Number],
    },
    isInvalid: {
      type: Boolean,
      default: false,
    },
    type: {
      type: String,
      default: 'text',
    },
  },
  mixins: [mixins],
  methods: {
    accept(event) {
      event.preventDefault();
      this.$emit('accept');
    },
    leave() {
      this.$emit('leave');
    },
    focus() {
      this.$refs.input.focus();
    },
  },
};
</script>

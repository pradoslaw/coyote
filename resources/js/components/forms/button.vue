<template>
  <button :disabled="disabled || processing" @click="click" :type="submit ? 'submit' : 'button'">
    <i v-if="processing" class="fa fa-spinner fa-spin fa-fw"/>
    <slot/>
  </button>
</template>

<script>
export default {
  emits: ['click'],
  props: {
    disabled: {type: Boolean, default: false},
    processing: {type: Boolean, default: false},
    submit: {type: Boolean, default: false},
  },
  methods: {
    click(event) {
      this.$emit('click', event);
    },
  },
};
</script>

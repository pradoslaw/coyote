<template>
  <button :disabled="disabled" @click="click" :type="submit ? 'submit' : 'button'">
    <i v-if="disabled" class="fa fa-spinner fa-spin fa-fw"/>
    <slot/>
  </button>
</template>

<script>
export default {
  props: {
    disabled: {
      type: Boolean,
      default: false,
    },
    submit: {
      type: Boolean,
      default: false,
    },
  },
  methods: {
    click(event) {
      this.$emit('click', event);
    },
  },
};
</script>

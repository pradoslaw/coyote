<template>
  <button :disabled="disabled" @click="click" type="button">
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
  },
  methods: {
    click(event) {
      this.$emit('click', event);
    },
  },
};
</script>

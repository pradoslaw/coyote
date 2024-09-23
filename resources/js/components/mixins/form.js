export default {
  model: {
    prop: 'modelValue',
    event: 'update:modelValue',
  },
  computed: {
    valueLocal: {
      get() {
        return this.modelValue;
      },
      set(value) {
        this.$emit('update:modelValue', value);
      },
    },
  },
};

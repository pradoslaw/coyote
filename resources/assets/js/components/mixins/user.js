export default {
  directives: {
    profile: {
      bind(el, binding) {
        el.href = `/Profile/${binding.value}`;
      }
    }
  },

  filters: {
    number(value) {
      return value.toLocaleString();
    }
  }
};

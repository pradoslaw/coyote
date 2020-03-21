import declination from '../../components/declination';

export default {
  directives: {
    profile: {
      bind(el, binding) {
        if (!binding.value) {
          return;
        }

        el.href = `/Profile/${binding.value}`;
        el.dataset.userId = binding.value;
      }
    }
  },

  filters: {
    number(value) {
      return value.toLocaleString();
    },

    declination(count, set) {
      return declination(count, set);
    }
  }
};

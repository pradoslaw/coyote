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
      return Math.abs(value) > 999 ? Math.sign(value) * ((Math.abs(value)/1000).toFixed(1)) + 'k' : Math.sign(value) * Math.abs(value);
    },

    declination(count, set) {
      return declination(count, set);
    }
  }
};

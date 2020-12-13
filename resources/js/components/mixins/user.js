import declination from '../../libs/declination';

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

    size(size) {
      return size > 1024 * 1024 ? Math.round(size / 1024 / 1024) + ' MB' : Math.round(size / 1024) + ' KB';
    },

    declination(count, set) {
      return declination(count, set);
    }
  },

  methods: {
    checkAuth(cb, ...args) {
      if (!this.isAuthorized) {
        this.$notify({
          type: 'error',
          // @ts-ignore
          width: '400px',
          title: 'Logowanie wymagane',
          text: '<a href="/Login">Zaloguj się</a>, aby skorzystać z tej funkcjonalności.'
        });

        return;
      }

      cb(...args);
    }
  }
};

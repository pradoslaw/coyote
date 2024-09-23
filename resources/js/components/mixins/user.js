import declination from '../../libs/declination';
import {notify} from '../../toast';

export default {
  directives: {
    profile: {
      beforeMount(el, binding) {
        if (!binding.value) {
          return;
        }

        el.href = `/Profile/${binding.value}`;
        el.dataset.userId = binding.value;
      },
    },
  },

  methods: {
    declination(count, set) {
      return declination(count, set);
    },

    number(value) {
      if (Math.abs(value) >= 1000000) {
        return (value / 1000000).toFixed(1) + 'm';
      }
      if (Math.abs(value) >= 1000) {
        return (value / 1000).toFixed(1) + 'k';
      }
      return value.toString();
    },

    size(size) {
      return size > 1024 * 1024 ? Math.round(size / 1024 / 1024) + ' MB' : Math.round(size / 1024) + ' KB';
    },

    checkAuth(cb, ...args) {
      if (!this.isAuthorized) {
        notify({
          type: 'error',
          title: 'Logowanie wymagane',
          text: '<a href="/Login">Zaloguj się</a>, aby skorzystać z tej funkcjonalności.',
        });

        return;
      }

      cb(...args);
    },
  },
};

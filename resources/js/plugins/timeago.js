import timeago from '../libs/timeago';
import store from '../store';
// esm import mode includes all locales (don't know why)
const format = require('date-fns/format');
const locale = require('date-fns/locale/pl');

export const createTimeago = (options = {}) => {
  return {
    name,
    store,
    props: {
      datetime: {
        required: true
      },

      autoUpdate: {
        default: 60
      },

      format: {
        type: String,
        default: store.getters['user/dateFormat']('yyyy-MM-dd HH:mm')
      }
    },

    data() {
      return {
        timeago: this.getTimeago(),
      };
    },

    mounted() {
      this.startUpdater();
    },

    beforeDestroy() {
      this.stopUpdater();
    },

    render(h) {
      return h(
        'time',
        {
          attrs: {
            title: this.datetime === null ? '' : format(new Date(this.datetime), this.format, { locale })
          }
        },
        [this.timeago]
      );
    },

    methods: {
      getTimeago () {
        if (!this.datetime) {
          return;
        }

        const date = new Date(this.datetime);
        const value = timeago(date.getTime() / 1000);

        return value ? value : format(date, this.format, { locale });
      },

      startUpdater() {
        if (this.autoUpdate) {
          this.updater = setInterval(() => this.update(), this.autoUpdate * 1000);
        }
      },

      stopUpdater() {
        if (this.updater) {
          clearInterval(this.updater);

          this.updater = null;
        }
      },

      update() {
        this.timeago = this.getTimeago();
      }
    },

    watch: {
      datetime() {
        this.update();
      }
    }
  };
};

export const install = (Vue, options) => {
  const Component = createTimeago(options);

  Vue.component('VueTimeago', Component);
};

export default install;

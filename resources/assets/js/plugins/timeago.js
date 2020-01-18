import toNow from 'date-fns/formatDistanceToNow';
import format from 'date-fns/format';
import differenceInHours from 'date-fns/differenceInHours';

const locales = {
  pl: require('date-fns/locale/pl')
};

export const createTimeago = (options = {}) => {
  const locale = options.locale || 'en';

  return {
    name,
    props: {
      datetime: {
        required: true
      },

      autoUpdate: {
        default: 60
      },

      format: {
        type: String,
        default: 'yyyy-MM-dd HH:mm'
      }
    },

    data() {
      return {
        timeago: this.getTimeago()
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
          // attrs: {
          //   title: this.datetime === null ? '' : format(this.datetime, this.format)
          // }
        },
        [this.timeago]
      );
    },

    methods: {
      getTimeago () {
        // const now = new Date();
        // const then = new Date(this.datetime);
        // const hours = differenceInHours(now, then);

        // return this.datetime;
        return format(new Date(this.datetime), this.format);


          // return toNow(this.datetime, {locale: locales[locale || 'en'], includeSeconds: true, addSuffix: true});



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

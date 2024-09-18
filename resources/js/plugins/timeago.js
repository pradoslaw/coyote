import format from 'date-fns/format';
import pl from 'date-fns/locale/pl';

import timeago from '../libs/timeago.js';
import store from '../store/index';

const defaultTimeFormat = store.getters['user/dateFormat']('yyyy-MM-dd HH:mm');

export const VueTimeAgo = {
  store,
  props: {
    datetime: {required: true},
    autoUpdate: {default: 60},
    format: {type: String, default: defaultTimeFormat},
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
    const title = this.datetime === null ? '' : format(new Date(this.datetime), this.format, {locale: pl});
    return h('time', {attrs: {title}}, [this.timeago]);
  },
  methods: {
    getTimeago() {
      if (!this.datetime) {
        return;
      }
      const date = new Date(this.datetime);
      const value = timeago(date.getTime() / 1000);
      return value ? value : format(date, this.format, {locale: pl});
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
    },
  },
  watch: {
    datetime() {
      this.update();
    },
  },
};

import fnsFormat from 'date-fns/format';
import fnsLocalePl from 'date-fns/locale/pl';

import timeago from '../libs/timeago.js';
import store from '../store/index';

const defaultTimeFormat = store.getters['user/dateFormat']('yyyy-MM-dd HH:mm');

export function format(datetime) {
  if (datetime) {
    return fnsFormat(new Date(datetime), defaultTimeFormat, {locale: fnsLocalePl});
  }
  return '';
}

export function formatTimeAgo(datetime) {
  if (!datetime) {
    return;
  }
  const date = new Date(datetime);
  const value = timeago(date.getTime() / 1000);
  if (value) {
    return value;
  }
  return format(date);
}

export const VueTimeAgo = {
  store,
  props: {
    datetime: {required: true},
    autoUpdate: {default: 60},
  },
  data() {
    return {
      timeago: this.getTimeago(),
    };
  },
  mounted() {
    this.startUpdater();
  },
  beforeUnmount() {
    this.stopUpdater();
  },
  template: '<time :title="title" v-text="this.timeago"/>',
  computed: {
    title() {
      return format(this.datetime, defaultTimeFormat);
    },
  },
  methods: {
    getTimeago() {
      return formatTimeAgo(this.datetime);
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

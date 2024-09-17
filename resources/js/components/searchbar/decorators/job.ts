import Decorator from './decorator.vue';

export default {
  extends: Decorator,
  data() {
    return {
      text: this.item.title,
    };
  },
};

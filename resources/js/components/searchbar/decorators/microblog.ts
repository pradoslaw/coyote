import Vue from 'vue';
import Decorator from './decorator.vue';

export default Vue.extend({
  extends: Decorator,
  data() {
    return {
      text: this.item.text,
    };
  },
});

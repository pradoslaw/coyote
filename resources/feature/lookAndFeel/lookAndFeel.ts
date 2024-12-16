import axios from "axios";
import {createVueApp} from "../../js/vue";

const lookAndFeelSwitcher = {
  methods: {
    lookAndFeelSwitchModern(): void {
      this.changeLookAndFeel('modern');
    },
    lookAndFeelSwitchLegacy(): void {
      this.changeLookAndFeel('legacy');
    },
    changeLookAndFeel(lookAndFeel: 'modern' | 'legacy'): void {
      axios.post('/LookAndFeel', {lookAndFeel})
        .then(() => window.location.reload());
    },
  },
};

window.addEventListener('load', () => {
  createVueApp('LookAndFeelSwitcher', '#lookAndFeelSwitcher', lookAndFeelSwitcher);
});

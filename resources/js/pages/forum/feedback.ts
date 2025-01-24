import axios from "axios";
import {nextTick} from "../../vue";

export default {
  delimiters: ['${', '}'],
  data() {
    return {
      state: 'initial',
      placeholder: 'Co możemy usprawnić na forum?',
      feedback: '',
      success: false,
    };
  },
  methods: {
    edit(): void {
      this.$data.state = 'typing';
      nextTick(() => {
        this.$refs.inputField.focus();
      });
    },
    cancel(): void {
      this.$data.state = 'initial';
    },
    send(): void {
      this.$data.state = 'success';
      axios.post('/User/Settings/Ajax', {feedback: this.$data.feedback});
    },
  },
  computed: {
    isInitial(): boolean {
      return this.$data.state === 'initial';
    },
    isSuccess(): boolean {
      return this.$data.state === 'success';
    },
  },
};

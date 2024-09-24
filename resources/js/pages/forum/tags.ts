import axios from "axios";
import VueButton from "../../components/forms/button.vue";
import {nextTick} from "../../vue";

export default {
  delimiters: ['${', '}'],
  components: {'vue-button': VueButton},
  data: () => ({
    tags: window.tags,
    isProcessing: false,
    isEditing: false,
  }),
  methods: {
    saveTags() {
      this.isProcessing = true;

      const tags = (this.$refs.input as HTMLInputElement).value.replace(new RegExp(',', 'g'), ' ').split(' ').filter(tag => tag !== '');

      axios.post<any>('/Forum/Tag/Save', {tags})
        .then(result => {
          this.tags = result.data;
          this.isEditing = false;
        })
        .finally(() => this.isProcessing = false);
    },

    toggleEdit() {
      this.isEditing = !this.isEditing;

      if (this.isEditing) {
        nextTick(() => (this.$refs.input as HTMLInputElement).focus());
      }
    },
  },
  computed: {
    inlineTags() {
      return this.tags.map(tag => tag.name).join(', ');
    },
  },
};

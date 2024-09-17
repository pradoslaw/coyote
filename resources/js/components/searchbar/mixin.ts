export default {
  props: {
    value: {
      type: String,
      required: true,
    },
    item: {
      type: Object,
      required: true,
    },
  },
  methods: {
    highlight(text: string): string {
      if (!this.value) {
        return text;
      }
      const value = this.value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
      const ascii = value.normalize('NFD').replace(/[\u0300-\u036f]/g, "");
      const re = new RegExp(`\\b(${value}|${ascii})`, "i");
      return text.replace(re, "<strong>$1</strong>");
    },
  },
};

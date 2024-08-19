import Vue from "vue";

export type ToggleValue = 'first' | 'second';

interface Instance extends Vue, Data {
  selected: ToggleValue;
}

interface Data {
  active: ToggleValue;
}

export default {
  props: ['first', 'second', 'selected'],
  template: `
    <div class="survey-toggle">
      <span class="first" :class="{active: 'first' === active}" @click="select('first')">
        {{ first }}
      </span>
      <span class="second" :class="{active: 'second' === active}" @click="select('second')">
        {{ second }}
      </span>
    </div>
  `,
  data(this: Instance): Data {
    return {
      active: this.selected,
    };
  },
  methods: {
    select(this: Instance, value: ToggleValue): void {
      this.active = value;
      this.$emit('change', this.active);
    },
  },
};

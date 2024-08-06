import Vue from "vue";

export default {
  template: `
    <div class="survey">
      <div class="badge d-flex align-items-baseline">
        <span>Zmieniaj forum na lepsze!</span>
        <button class="btn btn-primary ms-2" @click="engage">
          <i class="fa-solid fa-toggle-off"></i>
          Testuj
        </button>
      </div>
    </div>
  `,
  methods: {
    engage(this: Vue): void {
      this.$emit('engage');
    },
  },
};

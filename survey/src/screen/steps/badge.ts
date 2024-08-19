import Vue from "vue";

export default {
  props: {
    tooltip: {type: Boolean},
  },
  template: `
    <div :class="{survey:true, overlay:tooltip}">
      <div class="badge d-flex align-items-baseline">
        <div class="survey-tooltip-container position-relative" v-if="tooltip">
          <div class="survey-tooltip position-absolute p-3">
            <h6 class="mb-3" style="font-weight:bold;">
              Menu Testów
            </h6>
            Tutaj możesz zmienić swój wybór w dowolnym momencie.
            <hr class="my-2"/>
            <div class="d-flex justify-content-end">
              <button class="btn btn-primary btn-notice" @click="notice">Rozumiem</button>
            </div>
          </div>
        </div>
        <span>Zmieniaj forum na lepsze!</span>
        <button class="btn btn-primary btn-engage ms-2" @click="engage">
          <i class="fa-solid fa-toggle-off"/>
          Testuj
        </button>
      </div>
    </div>
  `,
  methods: {
    engage(this: Vue): void {
      this.$emit('engage');
    },
    notice(this: Vue): void {
      this.$emit('notice');
    },
  },
};

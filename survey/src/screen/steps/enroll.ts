import {VueInstance} from "../../vue";

export default {
  template: `
    <div class="survey overlay">
      <section class="enroll">
        <div class="image-container">
        </div>
        <div class="main-container d-flex flex-column justify-content-between">
          <div>
            <h3 class="mb-3">Zmieniaj forum na lepsze!</h3>
            <p>
              Cześć! Wprowadzamy zmiany na forum i chcielibyśmy, żebyś miał
              wpływ na to jak będzie wyglądało to miejsce. Przyłącz się do testów!
            </p>
          </div>
          <div>
            <button class="btn btn-primary mb-2 w-100" @click="enrollOptIn">
              Testuj
            </button>
            <button class="btn btn-secondary mb-2 w-100" @click="enrollOptOut">
              Zmiany są zbędne
            </button>
          </div>
        </div>
      </section>
    </div>
  `,
  methods: {
    enrollOptIn(this: VueInstance): void {
      this.$emit('enrollOpt', 'in');
    },
    enrollOptOut(this: VueInstance): void {
      this.$emit('enrollOpt', 'out');
    },
  },
};

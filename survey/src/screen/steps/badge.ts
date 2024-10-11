import VueIcon from "../../../../resources/js/components/icon";
import {VueInstance} from "../../vue";

export default {
  components: {VueIcon},
  props: {
    tooltip: {type: Boolean},
    long: {type: Boolean},
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
        <div class="collapse-toggle" @click="collapse">
          <vue-icon name="surveyBadgeShorten" v-if="long"/>
          <vue-icon name="surveyBadgeEnlarge" v-else/>
        </div>
        <span v-if="long" class="ms-2">Zmieniaj forum na lepsze!</span>
        <button class="btn btn-primary btn-engage ms-2" :class="{narrow:!long}" @click="engage">
          <vue-icon name="surveyExperimentOpen" class="me-1"/>
          <template v-if="long">Testuj</template>
        </button>
      </div>
    </div>
  `,
  methods: {
    engage(this: VueInstance): void {
      this.$emit('engage');
    },
    notice(this: VueInstance): void {
      this.$emit('notice');
    },
    collapse(this: VueInstance): void {
      this.$emit('collapse', !this.$props.long);
    },
  },
};

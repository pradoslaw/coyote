import VueIcon from "../../../../resources/js/components/icon";
import {VueInstance} from "../../vue";
import VueToggle, {type ToggleValue} from "../toggle";

/** @deprecated Use trial and user session instead */
interface Participate extends VueInstance, Data {
  experiment: Experiment;
  optedIn: boolean;
  choice: ExperimentChoice;
}

/** @deprecated Use trial and user session instead */
export interface Experiment {
  title: string;
  optedIn: ExperimentOpt;
  dueTime: string;
  reason: string;
  solution: string;
  imageLegacy: string;
  imageModern: string;
}

/** @deprecated Use choice and assortment instead */
export type ExperimentOpt = 'none-modern' | 'none-legacy' | 'legacy' | 'modern';
/** @deprecated Use choice instead */
export type ExperimentChoice = 'in' | 'out';

interface Data {
  selected: ToggleValue;
}

export default {
  props: {experiment: {type: Object}},
  components: {'vue-toggle': VueToggle, 'vue-icon': VueIcon},
  template: `
    <div class="survey overlay">
      <section class="participate d-flex">
        <div class="preview-container me-4" :class="{active: isInitialSelection}">
          <div class="timer">
            Do końca testu:
            <span>
              <vue-icon name="surveyExperimentDueTime"/>
              {{ experiment.dueTime }}
            </span>
          </div>
          <div class="d-flex justify-content-center image-height-holder align-items-center">
            <img :src="experiment.imageLegacy" alt="Legacy post comment style" v-if="selected === 'first'"/>
            <img :src="experiment.imageModern" alt="Modern post comment style" v-else/>
          </div>
          <div class="d-flex justify-content-center">
            <vue-toggle
              first="Pierwotna wersja"
              second="Nowa wersja"
              :selected="selected"
              @change="toggleChange"
            />
          </div>
        </div>
        <div class="content-container d-flex flex-column">
          <h3 class="mb-4">
            {{ experiment.title }}
          </h3>
          <p class="reason mb-4">
            <b>Dlaczego?</b>
            {{ ' ' }}
            <span v-html="experiment.reason"/>
          </p>
          <p class="solution mb-4">
            <b>Jak?</b>
            {{ ' ' }}
            <span v-html="experiment.solution"/>
          </p>
          <button class="btn btn-primary mt-auto mb-2" @click="experimentOpt" :disabled="isInitialSelection">
            <vue-icon name="surveyExperimentChoiceLegacy" v-if="selected === 'first'"/>
            <vue-icon name="surveyExperimentChoiceModern" v-else/>
            Zapisz wybór
          </button>
          <button class="btn btn-secondary" @click="close">
            Anuluj
          </button>
        </div>
      </section>
    </div>
  `,
  data(this: Participate): Data {
    return {
      selected: initiallySelected(this.experiment.optedIn),
    };
  },
  methods: {
    close(this: Participate): void {
      this.$emit('close');
    },
    experimentOpt(this: Participate): void {
      this.$emit('experimentOpt', this.choice);
    },
    toggleChange(this: Participate, value: ToggleValue): void {
      this.selected = value;
      this.$emit('experimentPreview', this.choice);
    },
  },
  computed: {
    isInitialSelection(this: Participate): boolean {
      return isPreviewActive(this.experiment.optedIn, this.selected);
    },
    choice(this: Participate): ExperimentChoice {
      return this.selected === 'second' ? 'in' : 'out';
    },
  },
};

function initiallySelected(opt: ExperimentOpt): ToggleValue {
  if (opt === 'modern' || opt === 'none-modern') {
    return 'second';
  }
  return 'first';
}

function isPreviewActive(opt: ExperimentOpt, selected: ToggleValue): boolean {
  if (opt === 'none-legacy' || opt === 'none-modern') {
    return false;
  }
  if (opt === 'modern') {
    return selected === 'second';
  }
  return selected === 'first';
}

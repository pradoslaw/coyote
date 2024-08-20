import Vue from "vue";
import VueToggle, {type ToggleValue} from "../toggle";

interface Participate extends Vue, Data {
  experiment: Experiment;
  optedIn: boolean;
}

export interface Experiment {
  title: string;
  optedIn: ExperimentOpt;
  dueTime: string;
  reason: string;
  solution: string;
  imageLegacy: string;
  imageModern: string;
}

export type ExperimentOpt = 'none' | 'legacy' | 'modern';

interface Data {
  selected: ToggleValue;
}

export default {
  props: {experiment: {type: Object}},
  components: {'vue-toggle': VueToggle},
  template: `
    <div class="survey overlay">
      <section class="participate d-flex">
        <div class="preview-container me-4" :class="{active: isInitialSelection}">
          <div class="timer">
            Do końca testu:
            <span>
              <i class="fa-regular fa-clock"/>
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
            <span v-html="experiment.reason"/>
          </p>
          <p class="solution mb-4">
            <b>Jak?</b>
            <span v-html="experiment.solution"/>
          </p>
          <button class="btn btn-primary mt-auto mb-2" @click="experimentOpt" :disabled="isInitialSelection">
            <i class="fa-solid" :class="selected === 'first' ? 'fa-toggle-off' : 'fa-toggle-on'"/>
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
      selected: this.experiment.optedIn === 'modern' ? 'second' : 'first',
    };
  },
  methods: {
    close(this: Participate): void {
      this.$emit('close');
    },
    experimentOpt(this: Participate): void {
      this.$emit('experimentOpt', this.selected === 'second' ? 'in' : 'out');
    },
    toggleChange(this: Participate, value: ToggleValue): void {
      this.selected = value;
      this.$emit('experimentPreview', this.selected === 'second' ? 'in' : 'out');
    },
  },
  computed: {
    isInitialSelection(this: Participate): boolean {
      return isPreviewActive(this.experiment.optedIn, this.selected);
    },
  },
};

function isPreviewActive(opt: ExperimentOpt, selected: ToggleValue): boolean {
  if (opt === 'none') {
    return false;
  }
  if (opt === 'modern') {
    return selected === 'second';
  }
  return selected === 'first';
}

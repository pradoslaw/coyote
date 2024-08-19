import Vue from "vue";
import VueToggle, {type ToggleValue} from "../toggle";

interface Participate extends Vue, Data {
  experiment: Experiment;
  optedIn: boolean;
}

export interface Experiment {
  title: string;
  optedIn: boolean;
  dueTime: string;
  reason: string;
  solution: string;
}

interface Data {
  selected: ToggleValue;
}

export default {
  props: {experiment: {type: Object}},
  components: {'vue-toggle': VueToggle},
  template: `
    <div class="survey overlay">
      <section class="participate d-flex">
        <div class="preview-container me-4" :class="{highlight: disabled}">
          <div class="timer">
            Do końca testu:
            <span>
              <i class="fa-regular fa-clock"/>
              {{ experiment.dueTime }}
            </span>
          </div>
          <div class="d-flex justify-content-center">
            <img src="/img/survey/postCommentStyleLegacy.png?v3" alt="Legacy post comment style" v-if="selected === 'first'"/>
            <img src="/img/survey/postCommentStyleModern.png?v5" alt="Modern post comment style" v-else/>
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
          <button class="btn btn-primary mt-auto mb-2" @click="experimentOpt" :disabled="disabled">
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
      selected: selected(this.experiment),
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
    },
  },
  computed: {
    disabled(this: Participate): boolean {
      return this.selected === selected(this.experiment);
    },
  },
};

function selected(experiment: Experiment): ToggleValue {
  return experiment.optedIn ? 'second' : 'first';
}

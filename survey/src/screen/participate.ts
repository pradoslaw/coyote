import Vue from "vue";

interface Participate extends Vue {
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

export default {
  props: {experiment: {type: Object}},
  template: `
    <div class="survey overlay">
      <section class="participate">
        <div class="d-flex align-items-center mb-1">
          <h3 class="mb-0">
            Aktualne testy
          </h3>
          <span class="experiments-count ms-2">1</span>
          <button class="btn ms-auto align-self-start" @click="close">
            <i class="fa-solid fa-close"></i>
          </button>
        </div>
        <p>
          Wybierz opcję, która Twoim zdaniem jest bardziej funkcjonalna i przejrzysta.
        </p>
        <div class="experiment">
          <div class="presentation mt-4">
            <ol start="1" class="mb-0">
              <li>
                <h4 class="mb-3">{{ experiment.title }}</h4>
                <p class="reason">
                  <b>Dlaczego?</b>
                  <span v-html="experiment.reason"/>
                </p>
                <p class="solution">
                  <b>Jak?</b>
                  <span v-html="experiment.solution"/>
                </p>
                <label class="experimentOpt">
                  <div class="switch me-2">
                    <input
                      type="checkbox"
                      :checked="experiment.optedIn"
                      @change="experimentOpt"
                    />
                    <span class="slider"></span>
                  </div>
                  <span style="line-height:20px;">Włącz nową wersję</span>
                </label>
              </li>
            </ol>
          </div>
          <div class="d-flex justify-content-end">
            <div class="timer mt-2">
              Do końca testu:
              <span>
                <i class="far fa-clock"></i>
                {{ experiment.dueTime }}
              </span>
            </div>
          </div>
        </div>
      </section>
    </div>
  `,
  methods: {
    close(this: Participate): void {
      this.$emit('close');
    },
    experimentOpt(this: Participate, event: InputEvent): void {
      const input = event.target as HTMLInputElement;
      this.$emit('experimentOpt', input.checked ? 'in' : 'out');
    },
  },
};

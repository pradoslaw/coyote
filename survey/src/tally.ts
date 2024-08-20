import Vue from "vue";
import SurveyScreen, {Experiment, type Screen} from "./screen/screen";
import {ExperimentChoice} from "./screen/steps/participate";

export {type Screen};

export type State =
  | 'survey-none'
  | 'survey-invited'
  | 'survey-declined'
  | 'survey-accepted'
  | 'survey-instructed'
  | 'survey-gone';

interface Instance extends Vue, Data {
  state: State;
  experiment: Experiment;
  changeSurveyState(state: State): void;
  notifyEnroll(text: string, fontAwesomeIcon: string): void;
  notifyExperiment(text: string, fontAwesomeIcon: string): void;
  notify(title: string, text: string, fontAwesomeIcon: string): void;
  experimentClose(): void;
}

interface Data {
  screen: Screen;
}

export default {
  props: ['state', 'experiment'],
  components: {'vue-survey-screen': SurveyScreen},
  template: `
    <vue-survey-screen
      :screen="screen"
      :experiment="experiment"
      @enrollOptIn="enrollOptIn"
      @enrollOptOut="enrollOptOut"
      @experimentClose="experimentClose"
      @experimentOptIn="experimentOpt('in')"
      @experimentOptOut="experimentOpt('out')"
      @experimentPreview="experimentPreview"
      @badgeEngage="badgeEngage"
      @badgeNotice="badgeNotice"
    />`,
  data(this: Instance): Data {
    return {
      screen: initialScreenFor(this.state),
    };
  },
  methods: {
    enrollOptIn(this: Instance): void {
      this.screen = 'participate';
      this.notifyEnroll('Dołączyłeś do testów forum!', 'fa-flask');
      this.changeSurveyState('survey-accepted');
    },
    enrollOptOut(this: Instance): void {
      this.screen = 'none';
      this.notifyEnroll('Wypisano z udziału w testach.', 'fa-bug-slash');
      this.changeSurveyState('survey-declined');
    },
    experimentOpt(this: Instance, choice: ExperimentChoice): void {
      this.experimentClose();
      const optIn = choice === 'in';
      this.$emit('experimentOpt', optIn ? 'modern' : 'legacy');
      if (optIn) {
        this.notifyExperiment('Uruchomiono nową wersję.', 'fa-toggle-on');
      } else {
        this.notifyExperiment('Przywrócono pierwotną wersję.', 'fa-toggle-off');
      }
    },
    experimentPreview(this: Vue, choice: ExperimentChoice): void {
      this.$emit('experimentPreview', choice === 'out' ? 'legacy' : 'modern');
    },
    experimentClose(this: Instance): void {
      if (this.state === 'survey-instructed') {
        this.screen = 'badge';
      } else {
        this.screen = 'badge-tooltip';
      }
    },
    badgeEngage(this: Data): void {
      this.screen = 'participate';
    },
    badgeNotice(this: Instance): void {
      this.screen = 'badge';
      this.changeSurveyState('survey-instructed');
    },
    changeSurveyState(this: Vue, state: State): void {
      this.$emit('change', state);
    },
    notifyEnroll(this: Instance, text: string, fontAwesomeIcon: string): void {
      this.notify('Zmieniaj forum na lepsze!', text, fontAwesomeIcon);
    },
    notifyExperiment(this: Instance, text: string, fontAwesomeIcon: string): void {
      this.notify(this.experiment.title, text, fontAwesomeIcon);
    },
    notify(this: Instance, title: string, text: string, fontAwesomeIcon: string): void {
      this.$notify({clean: true});
      this.$notify({
        type: 'success',
        title,
        text: `<i class="fa-solid ${fontAwesomeIcon}"></i> ` + text,
      });
    },
  },
};

function initialScreenFor(state: State): Screen {
  const screens: { [keyof: string]: Screen } = {
    'survey-invited': 'enroll',
    'survey-accepted': 'badge-tooltip',
    'survey-instructed': 'badge',
  };
  return screens[state] || 'none';
}

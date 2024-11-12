import {iconHtml} from "../../resources/js/components/icon";
import {notify} from "../../resources/js/toast";
import SurveyScreen, {Experiment, type Screen} from "./screen/screen";
import {ExperimentChoice} from "./screen/steps/participate";
import {VueInstance} from "./vue";

export {type Screen};

/** @deprecated Use Stage, instead of State */
export type State =
  | 'survey-none'
  | 'survey-invited'
  | 'survey-declined'
  | 'survey-accepted'
  | 'survey-instructed'
  | 'survey-gone';

interface Instance extends VueInstance, Data {
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
  props: ['state', 'experiment', 'badgeLong'],
  inject: ['icons'],
  components: {'vue-survey-screen': SurveyScreen},
  template: `
    <vue-survey-screen
      :screen="screen"
      :experiment="experiment"
      :badge-long="badgeLong"
      @enrollOptIn="enrollOptIn"
      @enrollOptOut="enrollOptOut"
      @experimentClose="experimentClose"
      @experimentOptIn="experimentOpt('in')"
      @experimentOptOut="experimentOpt('out')"
      @experimentPreview="experimentPreview"
      @badgeEngage="badgeEngage"
      @badgeNotice="badgeNotice"
      @badgeCollapse="badgeCollapse"
    />`,
  data(this: Instance): Data {
    return {
      screen: initialScreenFor(this.state),
      showExperimentOptNotification: false,
    };
  },
  methods: {
    enrollOptIn(this: Instance): void {
      this.screen = 'participate';
      this.notifyEnroll('Dołączyłeś do testów forum!', 'surveyExperimentJoined');
      this.changeSurveyState('survey-accepted');
    },
    enrollOptOut(this: Instance): void {
      this.screen = 'none';
      this.notifyEnroll('Wypisano z udziału w testach.', 'surveyExperimentLeft');
      this.changeSurveyState('survey-declined');
    },
    experimentOpt(this: Instance, choice: ExperimentChoice): void {
      this.experimentClose();
      const optIn = choice === 'in';
      this.$emit('experimentOpt', optIn ? 'modern' : 'legacy');
      if (this.state === 'survey-instructed') {
        if (optIn) {
          this.notifyExperiment('Korzystasz z nowej wersji.', 'surveyExperimentEnabledModern');
        } else {
          this.notifyExperiment('Korzystasz z pierwotnej wersji.', 'surveyExperimentEnabledLegacy');
        }
      }
    },
    experimentPreview(this: VueInstance, choice: ExperimentChoice): void {
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
    badgeCollapse(this: VueInstance, long: boolean): void {
      this.$emit('badgeCollapse', long);
    },
    changeSurveyState(this: VueInstance, state: State): void {
      this.$emit('change', state);
    },
    notifyEnroll(this: Instance, text: string, fontAwesomeIcon: string): void {
      this.notify('Zmieniaj forum na lepsze!', text, fontAwesomeIcon);
    },
    notifyExperiment(this: Instance, text: string, fontAwesomeIcon: string): void {
      this.notify(this.experiment.title, text, fontAwesomeIcon);
    },
    notify(this: Instance, title: string, text: string, fontAwesomeIcon: string): void {
      notify({clean: true});
      notify({
        type: 'success',
        title,
        text: iconHtml(this.icons, fontAwesomeIcon) + ' ' + text,
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

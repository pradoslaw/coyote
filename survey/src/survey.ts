import axios from "axios";

import store from "../../resources/js/store/index";
import {createVueApp} from "../../resources/js/vue";
import {type Experiment} from "./screen/screen";
import {ExperimentOpt} from "./screen/steps/participate";
import SurveyTally, {type State} from "./tally";
import {trial} from "./trial";
import {VueInstance} from "./vue";

window.addEventListener('load', () => {
  const darkTheme: boolean = document.body.classList.contains('theme-dark');
  const surveyElement = document.getElementById('survey');
  if (!surveyElement) {
    return;
  }
  const survey: Survey = JSON.parse(surveyElement!.textContent!);

  interface Survey {
    surveyState: State;
    surveyChoice: ExperimentOpt;
    surveyBadgeLong: boolean;
  }

  interface Data {
    state: State,
    experiment: Experiment;
    badgeLong: boolean;
  }

  createVueApp('Survey', '#js-survey', {
    components: {'vue-survey-tally': SurveyTally},
    template: `
      <vue-survey-tally
        :state="state"
        :experiment="experiment"
        :badge-long="badgeLong"
        @experimentOpt="experimentOpt"
        @experimentPreview="experimentPreview"
        @change="change"
        @badgeCollapse="badgeCollapse"
      />
    `,
    data(): Data {
      const experiment: Experiment = {
        ...trial,
        ...darkTheme ? trial.dark : trial.light,
        optedIn: survey.surveyChoice,
      };
      return {
        state: survey.surveyState,
        experiment,
        badgeLong: survey.surveyBadgeLong,
      };
    },
    methods: {
      experimentOpt(this: Data, optIn: ExperimentOpt): void {
        this.experiment.optedIn = optIn;
        experimentChangeStyle(optIn);
      },
      experimentPreview(opt: ExperimentOpt): void {
        storeSurveyPreview(opt);
      },
      change(this: Data, state: State): void {
        storeSurveyState(state);
        this.state = state;
      },
      setTheme(this: Data, dark: boolean): void {
        const theme = dark ? trial.dark : trial.light;
        this.experiment.imageLegacy = theme.imageLegacy;
        this.experiment.imageModern = theme.imageModern;
      },
      badgeCollapse(this: VueInstance, long: boolean): void {
        this.$data['badgeLong'] = long;
        storeSurveyBadgeState(long);
      },
    },
  });

  function experimentChangeStyle(style: ExperimentOpt): void {
    store.commit('user/changePostStyle', style);
    axios.post('/survey', {surveyChoice: style});
  }

  function storeSurveyState(surveyState: State): void {
    axios.post('/survey', {surveyState});
  }

  function storeSurveyPreview(surveyChoicePreview: ExperimentOpt): void {
    axios.post('/survey', {surveyChoicePreview});
  }

  function storeSurveyBadgeState(badgeLong: boolean): void {
    axios.post('/survey', {surveyBadgeState: badgeLong});
  }
});

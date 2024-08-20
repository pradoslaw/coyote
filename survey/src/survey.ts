import axios from "axios";
import Vue from "vue";

import store from "../../resources/js/store/index";
import {type Experiment} from "./screen/screen";
import {ExperimentOpt} from "./screen/steps/participate";
import SurveyTally, {type State} from "./tally";
import {trial} from "./trial";

window.addEventListener('load', () => {
  const darkTheme: boolean = document.body.classList.contains('theme-dark');
  const survey: Survey = JSON.parse(document.getElementById('survey')!.textContent!);

  interface Survey {
    surveyState: State;
    surveyChoice: ExperimentOpt;
  }

  interface Data {
    state: State,
    experiment: Experiment;
  }

  const app = new Vue({
    name: 'Survey',
    el: '#js-survey',
    components: {'vue-survey-tally': SurveyTally},
    template: `
      <vue-survey-tally
        :state="state"
        :experiment="experiment"
        @experimentOpt="experimentOpt"
        @experimentPreview="experimentPreview"
        @change="change"/>
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
      };
    },
    methods: {
      experimentOpt(optIn: ExperimentOpt): void {
        this.experiment.optedIn = optIn;
        experimentChangeStyle(optIn);
      },
      experimentPreview(opt: ExperimentOpt): void {
        storeSurveyPreview(opt);
      },
      change(state: State): void {
        storeSurveyState(state);
        this.state = state;
      },
      setTheme(dark: boolean): void {
        const theme = dark ? trial.dark : trial.light;
        this.experiment.imageLegacy = theme.imageLegacy;
        this.experiment.imageModern = theme.imageModern;
      },
    },
  });

  store.subscribe((mutation, state) => {
    if (mutation.type === 'theme/CHANGE_THEME') {
      const dark = state.theme.darkTheme;
      app.setTheme(dark);
    }
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
});

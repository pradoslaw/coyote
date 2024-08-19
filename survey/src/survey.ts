import axios from "axios";
import Vue from "vue";

import store from "../../resources/js/store/index";
import {type Experiment} from "./screen/screen";
import {ExperimentOpt} from "./screen/steps/participate";
import SurveyTally, {type State} from "./tally";
import {trial} from "./trial";

const survey: Survey = JSON.parse(document.getElementById('survey')!.textContent!);

interface Survey {
  surveyState: State;
  surveyChoice: ExperimentOpt;
}

interface Data {
  state: State,
  experiment: Experiment;
}

new Vue({
  name: 'Survey',
  el: '#js-survey',
  components: {'vue-survey-tally': SurveyTally},
  template: `
    <vue-survey-tally
      :state="state"
      :experiment="experiment"
      @experimentOpt="experimentOpt"
      @change="change"/>
  `,
  data(): Data {
    const experiment: Experiment = {
      ...trial,
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
      experimentChangeStyle(optIn ? 'modern' : 'legacy');
    },
    change(state: State): void {
      storeSurveyState(state);
      this.state = state;
    },
  },
});

function experimentChangeStyle(style: ExperimentOpt): void {
  store.commit('user/changePostStyle', style);
  axios.post('/User/Settings/Ajax', {postCommentStyle: style});
}

function storeSurveyState(surveyState: State): void {
  axios.post('/User/Settings/Ajax', {surveyState});
}

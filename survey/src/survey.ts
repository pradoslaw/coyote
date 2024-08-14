import axios from "axios";
import Vue from "vue";

import store from "../../resources/js/store/index";
import {experiment} from "./experiment";
import {type Experiment} from "./screen/screen";
import SurveyTally, {type State} from "./tally";

type PostStyle = 'modern' | 'legacy';

const survey = JSON.parse(document.getElementById('survey')!.textContent!);
const surveyState: State = survey['surveyState'];
const postCommentStyle: PostStyle | 'none' = survey['surveyChoice'];

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
      @change="change"/>`,
  data(): Data {
    return {
      state: surveyState,
      experiment: {
        ...experiment,
        optedIn: postCommentStyle === 'modern',
      },
    };
  },
  methods: {
    experimentOpt(optIn: boolean): void {
      this.experiment.optedIn = optIn;
      experimentChangeStyle(optIn ? 'modern' : 'legacy');
    },
    change(state: State): void {
      storeSurveyState(state);
      this.state = state;
    },
  },
});

function experimentChangeStyle(style: PostStyle): void {
  store.commit('user/changePostStyle', style);
  axios.post('/User/Settings/Ajax', {postCommentStyle: style});
}

function storeSurveyState(surveyState: State): void {
  axios.post('/User/Settings/Ajax', {surveyState});
}
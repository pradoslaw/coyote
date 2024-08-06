import axios from "axios";
import Vue from "vue";
import {mapMutations} from "vuex";

import store from "../../resources/js/store/index";
import SurveyBadge from "./screen/badge";
import SurveyEnroll from "./screen/enroll";
import SurveyParticipate, {Experiment} from "./screen/participate";
import diffFormat from "./time/diffFormat";

const initialScreen = screen(window['surveyState']);
const initiallyOptedIn = window['postCommentStyle'] === 'modern';

function screen(surveyState: string): Screen {
  if (surveyState === 'enroll') {
    return 'enroll';
  }
  if (surveyState === 'enrollOptIn') {
    return 'badge';
  }
  return 'none';
}

new Vue({
  name: 'Survey',
  el: '#survey',
  store,
  components: {
    'vue-survey-enroll': SurveyEnroll,
    'vue-survey-participate': SurveyParticipate,
    'vue-survey-badge': SurveyBadge,
  },
  template: `
    <div>
      <vue-survey-enroll
        v-if="screen === 'enroll'"
        @enrollOpt="enrollOpt"/>
      <vue-survey-participate
        v-if="screen === 'participate'"
        :experiment="experiment"
        @experimentOpt="experimentOpt"
        @close="close"/>
      <vue-survey-badge
        v-if="screen === 'badge'"
        @engage="engage"/>
    </div>
  `,
  data(this: Instance): Members {
    return {
      screen: initialScreen,
      experiment: {
        title: 'Układ treści w komentarzach.',
        optedIn: initiallyOptedIn,
        reason: 'Hierarchia informacji w obecnym układzie utrudnia szybkie zweryfikowanie kto jest autorem komentarza ' +
          'oraz kiedy komentarz został napisany.',
        solution: 'Proponujemy zmianę, która zakłada uporządkowanie treści według następującej hierarchii: ' +
          '<code>kto?</code>, <code>kiedy?</code>, <code>co?</code>. ' +
          'Dzięki temu szybko uzyskamy informację o autorze komentarza, dacie jego napisania oraz jego treści.',
        dueTime: diffFormat(this.secondsUntil('2024-08-12 14:00:00')),
      },
    };
  },
  methods: {
    ...mapMutations('user', ['changePostStyle']),
    secondsUntil(dateFormat: string): number {
      const timestampDifference = new Date(dateFormat).getTime() - new Date().getTime();
      return timestampDifference / 1000;
    },
    enrollOpt(opt: string): void {
      if (opt === 'in') {
        this.screen = 'participate';
        storeSurveyState('enrollOptIn');
      } else {
        this.screen = 'none';
        storeSurveyState('enrollOptOut');
      }
    },
    experimentOpt(opt: string): void {
      this.experiment.optedIn = opt === 'in';
      const style: PostStyle = opt === 'in' ? 'modern' : 'legacy';
      storePostCommentStyle(style);
      this.changePostStyle(style);
    },
    close(): void {
      this.screen = 'badge';
    },
    engage(): void {
      this.screen = 'participate';
    },
  },
});

type Screen = 'enroll' | 'participate' | 'badge' | 'none';
type PostStyle = 'modern' | 'legacy';

interface Instance extends Members {
  secondsUntil(dateFormat: string): number;
}

interface Members {
  screen: Screen,
  experiment: Experiment;
}

function storePostCommentStyle(postCommentStyle: PostStyle): void {
  axios.post('/User/Settings/Ajax', {postCommentStyle});
}

function storeSurveyState(surveyState: string): void {
  axios.post('/User/Settings/Ajax', {surveyState});
}

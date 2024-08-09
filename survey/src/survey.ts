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
const initialTooltip = window['surveyState'] === 'enroll';

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
        :tooltip="tooltip"
        @engage="engage"
        @notice="tooltip=false"
      />
    </div>
  `,
  data(this: Instance): Members {
    return {
      screen: initialScreen,
      tooltip: initialTooltip,
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
      const optIn = opt === 'in';
      if (optIn) {
        this.screen = 'participate';
        storeSurveyState('enrollOptIn');
      } else {
        this.screen = 'none';
        storeSurveyState('enrollOptOut');
      }
      this.$notify({
        type: 'success',
        title: 'Zmieniaj forum na lepsze!',
        text: optIn
          ? '<i class="fa-solid fa-flask fa-fw"></i> Dołączyłeś do testów forum!'
          : '<i class="fa-solid fa-bug-slash fa-fw"></i> Wypisano z udziału w testach.',
      });
    },
    experimentOpt(opt: string): void {
      const optIn = opt === 'in';
      this.experiment.optedIn = optIn;
      const style: PostStyle = optIn ? 'modern' : 'legacy';
      storePostCommentStyle(style);
      this.changePostStyle(style);
      this.$notify({
        type: 'success',
        title: 'Układ treści w komentarzach.',
        text: optIn
          ? '<i class="fa-solid fa-toggle-on fa-fw"></i> Włączono testową wersję.'
          : '<i class="fa-solid fa-toggle-off fa-fw"></i> Przywrócono poprzednią wersję.',
      });
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
  tooltip: boolean;
}

function storePostCommentStyle(postCommentStyle: PostStyle): void {
  axios.post('/User/Settings/Ajax', {postCommentStyle});
}

function storeSurveyState(surveyState: string): void {
  axios.post('/User/Settings/Ajax', {surveyState});
}

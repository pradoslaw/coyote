import Vue from "vue";
import VueSurveyBadge from "./steps/badge";
import VueSurveyEnroll from "./steps/enroll";
import VueSurveyParticipate from "./steps/participate";

export {type Experiment} from "./steps/participate";

export type Screen = 'none' | 'enroll' | 'participate' | 'badge' | 'badge-tooltip';

export default {
  components: {
    'vue-survey-enroll': VueSurveyEnroll,
    'vue-survey-participate': VueSurveyParticipate,
    'vue-survey-badge': VueSurveyBadge,
  },
  props: ['screen', 'experiment'],
  template: `
    <div>
      <vue-survey-enroll
        v-if="screen === 'enroll'"
        @enrollOpt="enrollOpt"
      />
      <vue-survey-participate
        v-if="screen === 'participate'"
        :experiment="experiment"
        @experimentOpt="experimentOpt"
        @close="experimentClose"
      />
      <vue-survey-badge
        v-if="screen === 'badge'"
        @engage="badgeEngage"
      />
      <vue-survey-badge
        v-if="screen === 'badge-tooltip'"
        tooltip
        @engage="badgeEngage"
        @notice="badgeNotice"
      />
    </div>
  `,
  methods: {
    enrollOpt(this: Vue, opt: string): void {
      if (opt === 'in') {
        this.$emit('enrollOptIn');
      } else {
        this.$emit('enrollOptOut');
      }
    },
    experimentOpt(this: Vue, opt: string): void {
      if (opt === 'in') {
        this.$emit('experimentOptIn');
      } else {
        this.$emit('experimentOptOut');
      }
    },
    experimentClose(this: Vue): void {
      this.$emit('experimentClose');
    },
    badgeEngage(this: Vue): void {
      this.$emit('badgeEngage');
    },
    badgeNotice(this: Vue): void {
      this.$emit('badgeNotice');
    },
  },
};
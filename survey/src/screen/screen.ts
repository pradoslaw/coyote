import Vue from "vue";
import VueSurveyBadge from "./steps/badge";
import VueSurveyEnroll from "./steps/enroll";
import VueSurveyParticipate, {ExperimentChoice} from "./steps/participate";

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
        @experimentPreview="experimentPreview"
        @close="experimentClose"
      />
      <vue-survey-badge
        v-if="screen === 'badge'"
        :long="badgeLong"
        @engage="badgeEngage"
        @collapse="badgeCollapse"
      />
      <vue-survey-badge
        v-if="screen === 'badge-tooltip'"
        :long="badgeLong"
        tooltip
        @collapse="badgeCollapse"
        @engage="badgeEngage"
        @notice="badgeNotice"
      />
    </div>
  `,
  data() {
    return {
      badgeLong: true,
    };
  },
  methods: {
    enrollOpt(this: Vue, opt: string): void {
      if (opt === 'in') {
        this.$emit('enrollOptIn');
      } else {
        this.$emit('enrollOptOut');
      }
    },
    experimentOpt(this: Vue, opt: ExperimentChoice): void {
      if (opt === 'in') {
        this.$emit('experimentOptIn');
      } else {
        this.$emit('experimentOptOut');
      }
    },
    experimentPreview(this: Vue, opt: ExperimentChoice): void {
      this.$emit('experimentPreview', opt);
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
    badgeCollapse(this: Vue, long: boolean): void {
      this.$data.badgeLong = long;
    },
  },
};

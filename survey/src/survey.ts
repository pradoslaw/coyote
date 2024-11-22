import axios from "axios";
import {createVueApp} from "../../resources/js/vue";
import {type Experiment} from "./screen/screen";
import {ExperimentOpt} from "./screen/steps/participate";
import SurveyTally, {type State} from "./tally";
import diffFormat from "./time/diffFormat";
import {VueInstance} from "./vue";

interface Data {
  state: State,
  experiment: Experiment;
  badgeLong: boolean;
}

type Stage = 'stage-none' | 'stage-invited' | 'stage-declined' | 'stage-accepted' | 'stage-instructed' | 'stage-gone';
type Choice = 'choice-pending' | 'choice-modern' | 'choice-legacy';
type Assortment = 'assortment-legacy' | 'assortment-modern';

function secondsUntil(dateFormat: string): number {
  return millisecondsDifference(dateFormat) / 1000;
}

function millisecondsDifference(dateFormat: string): number {
  return new Date(dateFormat).getTime() - new Date().getTime();
}

function translateInput(backendInput: string): Data {
  const survey: Survey = JSON.parse(backendInput);

  interface ImageSet {
    imageLegacy: string;
    imageModern: string;
  }

  interface Trial {
    title: string;
    reason: string;
    solution: string;
    dueDateTime: string;
    imageLight: ImageSet;
    imageDark: ImageSet;
  }

  interface UserSession {
    stage: Stage;
    choice: Choice;
    badgeNarrow: boolean;
    assortment: Assortment;
  }

  interface Survey {
    trial: Trial;
    userSession: UserSession;
    userThemeDark: boolean;
  }

  const imageSet: ImageSet = survey.userThemeDark ? survey.trial.imageDark : survey.trial.imageLight;

  return {
    state: mapStageToLegacySurveyState(survey.userSession.stage),
    badgeLong: !survey.userSession.badgeNarrow,
    experiment: {
      title: survey.trial.title,
      dueTime: diffFormat(secondsUntil(survey.trial.dueDateTime)),
      reason: survey.trial.reason,
      solution: survey.trial.solution,
      imageLegacy: imageSet.imageLegacy,
      imageModern: imageSet.imageModern,
      optedIn: mapStageAndAssortmentToLegacyExperimentOpt(survey.userSession.choice, survey.userSession.assortment),
    },
  };
}

function mapStageAndAssortmentToLegacyExperimentOpt(choice: Choice, assortment: Assortment): ExperimentOpt {
  if (choice === 'choice-modern') {
    return 'modern';
  }
  if (choice === 'choice-legacy') {
    return 'legacy';
  }
  if (choice === 'choice-pending') {
    if (assortment === 'assortment-legacy') {
      return 'none-legacy';
    }
    return 'none-modern';
  }
}

function mapStageToLegacySurveyState(stage: Stage): State {
  const map: { [keyof: string]: State } = {
    'stage-none': 'survey-none',
    'stage-invited': 'survey-invited',
    'stage-declined': 'survey-declined',
    'stage-accepted': 'survey-accepted',
    'stage-instructed': 'survey-instructed',
    'stage-gone': 'survey-gone',
  };
  return map[stage];
}

function mapLegacySurveyStateToStage(state: State): Stage {
  const map: { [keyof: string]: Stage } = {
    'survey-none': 'stage-none',
    'survey-invited': 'stage-invited',
    'survey-declined': 'stage-declined',
    'survey-accepted': 'stage-accepted',
    'survey-instructed': 'stage-instructed',
    'survey-gone': 'stage-gone',
  };
  return map[state];
}

window.addEventListener('load', () => {
  const surveyElement = document.getElementById('survey');
  if (!surveyElement) {
    return;
  }
  if (surveyElement!.textContent! === 'null') {
    return;
  }
  const data = translateInput(surveyElement!.textContent!);
  if (data.state === 'survey-invited') {
    setTimeout(() => {
      runSurvey();
      storeSurveyEnroll();
    }, 6 * 1000);
  } else {
    runSurvey();
  }

  function runSurvey() {
    renderVueApp(
      data,
      experimentChangeStyle,
      storeSurveyPreview,
      storeSurveyState,
      storeSurveyBadgeState,
    );
  }

  function experimentChangeStyle(style: ExperimentOpt): void {
    axios.post('/trial/choice', {choice: 'choice-' + style})
      .then(() => window.location.reload());
  }

  function storeSurveyState(surveyState: State): void {
    axios.post('/trial/stage', {stage: mapLegacySurveyStateToStage(surveyState)});
  }

  function storeSurveyPreview(surveyChoicePreview: ExperimentOpt): void {
    axios.post('/trial/preview', {preview: surveyChoicePreview});
  }

  function storeSurveyEnroll(): void {
    axios.post('/trial/enroll', {});
  }

  function storeSurveyBadgeState(badgeLong: boolean): void {
    axios.post('/trial/badge', {badge: badgeLong ? 'long' : 'narrow'});
  }
});

function renderVueApp(
  data: Data,
  experimentChangeStyle: (style: ExperimentOpt) => void,
  storeSurveyPreview: (surveyChoicePreview: ExperimentOpt) => void,
  storeSurveyState: (surveyState: State) => void,
  storeSurveyBadgeState: (badgeLong: boolean) => void,
): void {
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
      return data;
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
        throw new Error('To be implemented, changing themed images in runtime.');
      },
      badgeCollapse(this: VueInstance, long: boolean): void {
        this.$data['badgeLong'] = long;
        storeSurveyBadgeState(long);
      },
    },
  });
}

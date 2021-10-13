import { Context, Hit } from "./hit";
import { Model } from "./models";

export type HitCategory = {[key: string]: {children: Hit[], model: string, context: string}}

type ModelType = {
  [key in Model]: string;
};

type ContextType = {
  [key in Context]: string;
}

type ModelContextType = {
  [key in Model]: ContextType;
};

export const Contexts: ModelContextType = {
  [Model.Topic]: {
    [Context.User]: 'Twoje wątki',
    [Context.Subscriber]: 'Obserwowane wątki',
    [Context.Participant]: 'Twoje dyskusje',
  },
  [Model.User]: {
    [Context.User]: '',
    [Context.Subscriber]: '',
    [Context.Participant]: '',
  },
  [Model.Job]: {
    [Context.User]: 'Twoje oferty pracy',
    [Context.Subscriber]: 'Zapisane oferty pracy',
    [Context.Participant]: '',
  },
  [Model.Wiki]: {
    [Context.User]: 'Twoje artykuły',
    [Context.Subscriber]: 'Obserwowane artykuły',
    [Context.Participant]: 'Artykuły z Twoim udziałem'
  },
  [Model.Microblog]: {
    [Context.User]: 'Twoje wpisy na mikroblogu',
    [Context.Subscriber]: '',
    [Context.Participant]: ''
  },
  [Model.Guide]: {
    [Context.User]: 'Twoje pytania rekrutacyjne',
    [Context.Subscriber]: '',
    [Context.Participant]: ''
  }
};

export const Models: ModelType = {
  [Model.Topic]: 'Wątki na forum',
  [Model.Job]: 'Oferty pracy',
  [Model.User]: 'Użytkownicy',
  [Model.Wiki]: 'Artykuły',
  [Model.Microblog]: 'Mikroblogi',
  [Model.Guide]: 'Pytania rekrutacyjne'
};

import {Model} from "./models";

export interface Hit {
    model:                Model;
    id:                   number;
    score:                number | null;
    replies:              number | null;
    subject:              string | null;
    title:                string | null;
    salary:               number | null;
    last_post_created_at: Date | null;
    url:                  string;
    user_id:              number | null;
    forum:                Forum | null;
    _score?:              number;
    context?:             Context;
    index?:               number;
}

export interface Forum {
    id:   number;
    name: string;
    slug: string;
    url:  string;
}

export enum Context {
    User = 'user',
    Subscriber = 'subscriber',
    Participant = 'participant'
}

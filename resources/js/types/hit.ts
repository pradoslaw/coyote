import {Links, Meta, Model} from "./models";

export interface Hit {
  model: Model;
  id: number;
  score: number | null;
  replies: number | null;
  subject: string | null;
  title: string | null;
  salary: number | null;
  last_post_created_at: Date | null;
  url: string;
  user_id: number | null;
  forum: Forum | null;
  _score?: number;
  context?: Context;
  index?: number;
  text?: string;
  posts?: Post[];
  breadcrumbs?: Breadcrumb[];
}

export interface Forum {
  id: number;
  name: string;
  slug: string;
  url: string;
}

export interface Post {
  id: number;
  created_at: Date;
  text: string;
}

export enum Context {
  User = 'user',
  Subscriber = 'subscriber',
  Participant = 'participant'
}

export interface Hits {
  data: Hit[];
  links: Links;
  meta: Meta;
}

export interface Breadcrumb {
  name: string;
  url: string;
}

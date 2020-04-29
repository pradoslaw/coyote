export enum Model {
    Topic = 'Topic',
    User = 'User',
    Microblog = 'Microblog',
    Job = 'Job',
    Wiki = 'Wiki'
}

export interface User {
  id: number;
  name: string;
  deleted_at?: Date;
  is_blocked: boolean;
  photo: string;
}

export interface Microblog {
  id: number;
  votes: number;
  is_voted: boolean;
  is_subscribed: boolean;
  created_at: Date;
  updated_at: Date;
  html: string;
  comments: Microblog[];
  user: User;
  editable?: boolean;
}

export interface Links {
  first: string;
  last: string;
  prev?: string;
  next: string;
}

export interface Meta {
  current_page: number;
  from: number;
  last_page: number;
  path: string;
  per_page: number;
  to: number;
  total: number;
}

export interface Paginator {
  data: Microblog[];
  links: Links;
  meta: Meta;
}

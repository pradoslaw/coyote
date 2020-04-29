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

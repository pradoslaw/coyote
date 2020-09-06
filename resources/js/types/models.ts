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
  posts?: number;
  created_at?: Date;
  visited_at?: Date;
  allow_smilies?: boolean;
  allow_count?: boolean;
  allow_sig?: boolean;
  location?: string;
  sig?: string;
  group?: string;
}

export interface Media {
  thumbnail: string;
  url: string;
  name: string;
}

export interface Microblog {
  id: number | null;
  parent_id?: number;
  votes: number;
  is_voted: boolean;
  is_subscribed: boolean;
  is_sponsored: boolean;
  created_at: Date | null;
  updated_at: Date | null;
  html: string;
  text: string;
  url: string;
  comments: Microblog[];
  user: User | null
  media: Media[];
  editable?: boolean;
  comments_count?: number;
  voters?: string[];
}

export interface Forum {
  id: number;
  slug: string;
  name: string;
}

interface PostPermission {
  write: boolean;
  delete: boolean;
  update: boolean;
  merge: boolean;
  sticky: boolean;
  adm_access: boolean;
}

export interface PostComment {
  id: number;
  post_id: number;
  created_at: Date | null;
  updated_at: Date | null;
  text: string;
  html: string;
  user: User;
}

export interface PostAttachment {
  id: number;
  name: string;
  file: string;
  mime: string;
  created_at: Date | null;
  size: number;
  url: string;
}

export interface Post {
  id: number;
  user?: User;
  editor?: User;
  deleter_name?: string;
  delete_reason?: string;
  created_at: Date | null;
  updated_at: Date | null;
  deleted_at: Date | null;
  text: string;
  html: string;
  score: number;
  url: string;
  is_read: boolean;
  is_locked: boolean;
  is_subscribed: boolean;
  is_accepted: boolean;
  is_voted: boolean;
  permissions: PostPermission;
  ip?: string;
  comments: PostComment[];
  attachments: PostAttachment[];
  edit_count?: number;
}

export interface Tag {
  id: number;
  name: string;
  url: string;
  real_name?: string;
  logo?: string;
}

export interface Topic {
  id: number;
  is_locked: boolean;
  is_sticky?: boolean;
  subject: string;
  first_post_id?: number;
  is_subscribed?: boolean;
  tags?: Tag[];
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

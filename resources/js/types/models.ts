
export enum Model {
  Topic = 'Topic',
  User = 'User',
  Microblog = 'Microblog',
  Job = 'Job',
  Wiki = 'Wiki'
}

export interface Flag {
  id: number;
  name: string;
  text: string;
  created_at: Date;
  user: User;
  resources: FlagResource;
}

export interface FlagType {
  id: number;
  name: string;
  description: string;
}

export interface FlagResource {
  resource_id: number;
  resource_type: string;
}

export interface User {
  id: number;
  name: string;
  deleted_at?: Date;
  is_blocked: boolean;
  is_online?: boolean;
  photo: string;
  posts?: number;
  created_at?: Date;
  visited_at?: Date;
  allow_smilies?: boolean;
  allow_count?: boolean;
  allow_sig?: boolean;
  location?: string;
  sig?: string;
  group_name?: string;
}

export interface Asset {
  id: number;
  thumbnail: string;
  url: string;
  name?: string;
  mime?: string;
}

export interface Microblog {
  id: number | null;
  parent_id?: number;
  votes: number;
  is_voted: boolean;
  is_subscribed: boolean;
  is_sponsored: boolean;
  is_editing?: boolean;
  is_read?: boolean;
  created_at: Date | null;
  updated_at: Date | null;
  html: string;
  text: string;
  url: string;
  comments: Microblog[];
  user: User | null
  assets: Asset[];
  permissions: MicroblogPermission;
  comments_count?: number;
  voters?: string[];
  metadata: string;
}

export interface Forum {
  id: number;
  slug: string;
  name: string;
}

export interface PollItem {
  id: number;
  text: string;
  total: number;
}

export interface Poll {
  title: string;
  length: number;
  max_items: number;
  expired_at?: Date;
  expired?: boolean;
  votes?: number[];
  items: PollItem[];
}

interface MicroblogPermission {
  update: boolean;
}

interface PostPermission {
  write: boolean;
  delete: boolean;
  update: boolean;
  merge: boolean;
  sticky: boolean;
  adm_access: boolean;
  accept: boolean;
}

export interface PostComment {
  id: number;
  post_id: number;
  created_at: Date | null;
  updated_at: Date | null;
  text: string;
  html: string;
  user: User;
  editable?: boolean;
}

export interface Post {
  id: number;
  user?: User;
  user_id: number | null;
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
  browser?: string;
  comments: PostComment[];
  comments_count: number;
  assets: Asset[];
  edit_count?: number;
  metadata?: string;
  voters?: string[];
}

export interface Tag {
  id?: number;
  name: string;
  url?: string;
  real_name?: string;
  logo?: string;
  priority?: number;
}

export interface Topic {
  id: number;
  is_locked: boolean;
  is_sticky?: boolean;
  is_read?: boolean;
  subject: string;
  first_post_id?: number;
  is_subscribed?: boolean;
  tags?: Tag[];
  subscribers?: number;
  owner_id?: number | null;
}

export interface Paginator {
  data: Microblog[] | Post[];
  current_page: number;
  from: number;
  last_page: number;
  path: string;
  per_page: number;
  to: number;
  total: number;
}

export interface JobLocation {
  label?: string;
  street?: string;
  street_number?: string;
  city?: string;
  country?: string;
}

export interface JobFeature {
  id: number;
  default: string;
  name: string;
  value: string;
  checked: boolean;
}

export enum Rate {
  monthly = 'miesiecznie',
  yearly = 'rocznie',
  weekly = 'tygodniowo',
  hourly = 'godzinowo'
}

export enum Employment {
  employment = 'Umowa o pracę',
  mandatory = 'Umowa zlecenie',
  contract = 'Umowa o dzieło',
  b2b = 'Kontrakt'
}

export enum Seniority {
  student = 'Stażysta',
  junior = 'Junior',
  mid = 'Mid-level',
  senior = 'Senior',
  lead = 'Lead',
  manager = 'Manager'
}

export interface Currency {
  id: number;
  name: string;
  symbol: string;
}

export interface Job {
  id: number;
  plan_id: number;
  title: string;
  user?: User;
  locations: JobLocation[];
  enable_apply: boolean;
  email: string;
  tags: Tag[];
  features: JobFeature[];
  description: string;
  recruitment: string;
  salary_from: number;
  salary_to: number;
  currency_id: number;
  is_gross: boolean;
  is_remote: boolean;
  remote_range: number;
  rate: Rate;
  employment: Employment;
  seniority: Seniority;
  firm?: Firm;
}

export interface Firm {
  id: number;
  name: string;
  is_agency: boolean;
  city: string;
  country: string;
  street: string;
  street_number: string;
  postcode: string;
  latitude: number;
  longitude: number;
  employees: number;
  founded: number;
  youtube_url: string;
  logo: string | null;
  assets: Asset[];
  benefits: string[];
  website: string;
  description: string;
}

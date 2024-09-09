import {Post, PostComment} from '../types/models';
import client from './client';

export async function getPost(id: number) {
  return client.get<Post>(`/Forum/Post/${id}`);
}

export async function getPostComment(id: number) {
  return client.get<PostComment>(`/Forum/Comment/${id}`);
}

import client from './client';
import { Post, PostComment, PostLog } from '@/types/models';

export async function getPost(id: number) {
  return client.get<Post>(`/Forum/Post/${id}`);
}

export async function getPostComment(id: number) {
  return client.get<PostComment>(`/Forum/Comment/${id}`);
}

export async function rollback(log: PostLog) {
  return client.post<{ url: string }>(`/Forum/Post/Rollback/${log.post_id}/${log.id}`);
}

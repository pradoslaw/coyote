import client from './client';
import { Post } from '@/types/models';

export async function getPost(id: number) {
  return client.get<Post>(`/Forum/Post/${id}`);
}

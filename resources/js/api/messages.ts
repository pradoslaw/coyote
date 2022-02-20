import client from './client';
import axios from "axios";
import * as models from '@/types/models';

export async function getInbox() {
  return client.get('/User/Pm/Inbox')
}

export async function submitMessage(message: models.Message) {
  return axios.post('/User/Pm/Submit', message);
}

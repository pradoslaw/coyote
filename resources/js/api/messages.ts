import axios from "axios";

import * as models from '../types/models';
import client from './client';

export async function getInbox() {
  return client.get('/User/Pm/Inbox');
}

export async function submitMessage(message: models.Message) {
  return axios.post('/User/Pm/Submit', message);
}

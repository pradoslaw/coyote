import client from './client';

export async function getInbox() {
  return client.get('/User/Pm/Inbox')
}

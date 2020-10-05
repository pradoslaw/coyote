import { Post, Topic } from "./types/models";
const faker = require('faker');

export function post(props?: any): Post {
  const lorem = faker.lorem.text();

  return Object.assign({
    comments: [],
    created_at: new Date(),
    deleted_at: null,
    html: lorem,
    id: faker.random.number(),
    is_accepted: false,
    is_locked: false,
    is_read: false,
    is_subscribed: false,
    is_voted: false,
    permissions: {
      write: true,
      delete: false,
      update: false,
      merge: false,
      adm_access: false
    },
    score: 0,
    text: lorem,
    updated_at: new Date(),
    url: "",
  }, props);
}

export function topic(props?: any): Topic {
  const lorem = faker.lorem.text();

  return Object.assign({
    id: faker.random.number(),
    subject: faker.lorem.string(),
    is_locked: false,
    is_read: false,
    is_sticky: false,
    first_post_id: faker.random.number()
  }, props);
}

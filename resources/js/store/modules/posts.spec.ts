import store from "./posts";
import { Post } from "../../types/models";
const faker = require('faker');
import axios from 'axios';

const { mutations, actions } = store;

function fake(): Post {
  return {
    comments: [],
    created_at: new Date(),
    deleted_at: null,
    html: "",
    id: 0,
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
    text: "",
    updated_at: new Date(),
    url: "",
  };
}

describe('posts mutation', () => {
  test('vote', () => {
    const post = fake();
    const state = {data: [post]};

    expect(post.score).toEqual(0);

    mutations.vote(state, post);

    expect(post.score).toEqual(1);
    expect(post.is_voted).toBeTruthy();

    mutations.vote(state, post);

    expect(post.score).toEqual(0);
    expect(post.is_voted).toBeFalsy();
  });
});

describe('posts actions', () => {
  jest.mock('axios', () => ({
    post: Promise.resolve(1)
  }));

  test('votes', async () => {
    const post = fake();
    const commit = jest.fn();

    await actions.vote({ commit }, post);

    expect(commit).toHaveBeenCalledWith("vote", post);
  })
});

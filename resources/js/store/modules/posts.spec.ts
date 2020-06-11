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
    text: "",
    updated_at: new Date(),
    url: "",
  };
}

describe('posts mutation', () => {
  test('votes to a post', () => {
    const post = fake();
    const state = {data: {[post.id]: post}};

    expect(post.score).toEqual(0);

    mutations.vote(state, post);

    expect(post.score).toEqual(1);
    expect(post.is_voted).toBeTruthy();

    mutations.vote(state, post);

    expect(post.score).toEqual(0);
    expect(post.is_voted).toBeFalsy();
  });

  test('subscribe to a post', () => {
    const post = fake();
    const state = {data: {[post.id]: post}};

    expect(post.is_subscribed).toBeFalsy();

    mutations.subscribe(state, post);

    expect(post.is_subscribed).toBeTruthy();
  });

  test('accepts post', () => {
    const post = fake();
    const state = {data: {[post.id]: post}};

    expect(post.is_accepted).toBeFalsy();

    mutations.accept(state, post);

    expect(post.is_accepted).toBeTruthy();

    mutations.accept(state, post);

    expect(post.is_accepted).toBeFalsy();
  });

  test('accepts different post', () => {
    const postAccepted = Object.assign(fake(), {'is_accepted': true});
    const post = fake();

    const state = {data: {[postAccepted.id]: postAccepted, [post.id]: post}};

    mutations.accept(state, post);

    expect(post.is_accepted).toBeTruthy();
    expect(postAccepted.is_accepted).toBeFalsy();
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

  // test('accepts answer', async () => {
  //   const post = fake();
  //   const commit = jest.fn();
  //
  //   await actions.vote({ commit }, post);
  //
  //   expect(commit).toHaveBeenCalledWith("vote", post);
  // })
});

import {beforeAll, describe, test} from "@jest/globals";

describe('vuex', () => {
  let store;

  beforeAll(async () => {
    window['__INITIAL_STATE'] = {user: {}};
    store = (await import ("../index")).default;
  });

  test('current page', () => {
    store.commit('posts/init', {current_page: 1});
    expect(store.getters['posts/currentPage']).toBe(1);
  });
});

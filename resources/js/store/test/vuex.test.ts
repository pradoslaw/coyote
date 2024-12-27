import {beforeAll, describe, test} from "@jest/globals";
import {Store} from "vuex";

import {assertEquals} from "../../../../survey/test/assert";
import {Post} from "../../types/models";

describe('vuex', () => {
  let store: StoreDsl;

  beforeAll(async () => {
    store = new StoreDsl(await loadVuexStore());
  });
  afterEach(() => {
    store.clear();
  });

  async function loadVuexStore(): Promise<Store<any>> {
    window['__INITIAL_STATE'] = {user: {}};
    return (await import ("../index")).default;
  }

  test('current page', () => {
    store.postsCurrentPage = 1;
    store.init();
    expect(store.readCurrentPage()).toBe(1);
  });

  describe('folded posts member name', () => {
    test('return post author name', () => {
      store.preparePost(237, 'Mark');
      store.init();
      assertEquals(['Mark'], store.readTreeTopicSubtreeMembers(237));
    });

    test('return post author names', () => {
      store.preparePost(237, 'Mark');
      store.preparePost(238, 'John', 237);
      store.init();
      assertEquals(['Mark', 'John'], store.readTreeTopicSubtreeMembers(237));
    });

    test('only return members of particular subtree', () => {
      store.preparePost(237, 'Mark');
      store.preparePost(238, 'John', 237);
      store.preparePost(239, 'Luke', 238);
      store.init();
      assertEquals(['John', 'Luke'], store.readTreeTopicSubtreeMembers(238));
    });
  });
});

class StoreDsl {
  public postsCurrentPage = 0;
  private postsData: { [keyof: number]: Post } = {};

  constructor(private store: Store<any>) {
  }

  preparePost(postId: number, userAuthorName: string, parentPostId?: number): void {
    this.postsData[postId] = {
      id: postId,
      parentPostId: parentPostId || null,
      user: {name: userAuthorName},
    };
  }

  init(): void {
    this.store.commit('posts/init', {
      current_page: this.postsCurrentPage,
      data: this.postsData,
    });
  }

  readCurrentPage(): number {
    return this.store.getters['posts/currentPage'];
  }

  readTreeTopicSubtreeMembers(postId: number): string[] {
    return this.store.getters['posts/postAnswersAuthors'](postId).map(user => user.name);
  }

  clear(): void {
    this.postsCurrentPage = 0;
    this.postsData = {};
    this.init();
  }
}

import {beforeAll, describe, test} from "@jest/globals";
import {Store} from "vuex";
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
});

class StoreDsl {
  public postsCurrentPage = 0;
  private postsData: { [keyof: number]: Post } = {};

  constructor(private store: Store<any>) {
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

  clear(): void {
    this.postsCurrentPage = 0;
    this.postsData = {};
    this.init();
  }
}

import store from "./microblogs";
import {Microblog} from "../../types/models";
const faker = require('faker');

const { mutations } = store;

function fake(): Microblog {
  return {
    comments: [],
    created_at: new Date(),
    editable: true,
    html: faker.lorem.words(),
    id: faker.random.number(),
    media: [],
    text: faker.lorem.words(),
    updated_at: new Date(),
    user: {id: 1, name: faker.name.firstName(), deleted_at: undefined, is_blocked: false, photo: ''},
    votes: 0,
    is_voted: false,
    is_subscribed: false,
    is_sponsored: false,
    comments_count: 0,
    url: '',
    metadata: ''
  };
}

describe('microblog mutation', () => {
  test('add microblog', () => {
    const state = {data: []};
    const microblog = fake();

    mutations.add(state, microblog);

    expect(microblog.id! in state.data).toBeTruthy();
  });

  test('delete microblog', () => {
    const microblog = fake();
    const state = {data: [microblog]};

    mutations.delete(state, microblog);

    expect(microblog.id! in state.data).toBeFalsy();
  });

  test('update microblog', () => {
    const microblog = fake();
    const state = {data: [microblog]};

    let text;

    microblog.text = text = faker.lorem.words();

    mutations.update(state, microblog);

    expect(state.data[microblog.id!]['text']).toMatch(text);
  });

  test('add comment', () => {
    const parent = fake();
    const comment = Object.assign(fake(), {parent_id: parent.id}) as Microblog;
    const state = {
      data: []
    };

    mutations.add(state, parent);
    mutations.addComment(state, { parent, comment });

    expect(parent.id! in state.data).toBeTruthy();
    // @ts-ignore
    expect(state.data[parent.id!].comments[comment.id]).toBeInstanceOf(Object);
    // @ts-ignore
    expect(state.data[parent.id!].comments_count).toBe(1);
  });

  test('edit', () => {
    const parent = fake();
    const state = {
      data: [parent]
    };

    mutations.edit(state, parent);

    expect(parent.is_editing).toBeTruthy();
  });
});

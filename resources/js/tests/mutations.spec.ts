import { mutations} from "../store";
import {Microblog} from "../types/models";

function fake(): Microblog {
  return {
    comments: [],
    created_at: new Date(),
    editable: true,
    html: "<p>test</p>",
    id: 62108,
    media: [],
    text: "test",
    updated_at: new Date(),
    user: {id: 1, name: "Adam Boduch", deleted_at: undefined, is_blocked: false, photo: ''},
    votes: 0,
    is_voted: false,
    is_subscribed: false,
    is_sponsored: false
  };
}

test('add microblog', () => {
  const state = {data: []};
  const microblog = fake();

  mutations.update(state, microblog);

  expect(microblog.id! in state.data).toBeTruthy();
});

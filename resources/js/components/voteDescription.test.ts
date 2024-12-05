import {describe, test} from "@jest/globals";

import {assertEquals} from "../../../survey/test/assert";
import {voteDescription} from "./voteDescription";

describe('post', () => {
  describe('voteDescription()', () => {
    test('post with 0 votes encourages to vote', () =>
      assertEquals(voteDescription(0, false, []), 'Doceń post'));

    test('post with one vote, informs about his vote', () =>
      assertEquals(voteDescription(1, false, ['Mark']), 'Mark docenił post'));

    test('post with one vote (from you), informs about your vote', () =>
      assertEquals(voteDescription(1, true, []), 'Doceniłeś post'));

    test('post with two votes (one from you), informs about your and his vote', () =>
      assertEquals(voteDescription(2, true, ['Mark']), 'Ty i Mark doceniliście post'));

    test('post with two votes (not from you), informs about their vote', () =>
      assertEquals(voteDescription(2, false, ['George', 'Mark']), 'George i Mark docenili post'));

    test('post with three votes (one from you), informs about your and their votes', () =>
      assertEquals(voteDescription(3, true, ['Mark', 'George']), 'Ty, Mark i George doceniliście post'));
  });
});

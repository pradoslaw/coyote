import {beforeEach, describe, test} from "@jest/globals";
import {assertEquals} from "../../../survey/test/assert";
import {TreeList} from "./treeList";

describe('tree list', () => {
  let topic: TreeList<string>;

  beforeEach(() => {
    topic = new TreeList(() => 0);
  });

  test('emptyTopic hasNoPosts', () => {
    assertEquals([], topic.asList());
  });

  test('topicWithOnePost hasOnePost', () => {
    topic.add(1, 'foo');
    assertEquals(['foo'], topic.asList());
  });

  test('answerPost isBelowHisParentPost', () => {
    topic.add(4, 'blue');
    topic.add(5, 'red');
    topic.addChild(6, 4, 'green');
    assertEquals(['blue', 'green', 'red'], topic.asList());
  });

  test('secondLevelAnswer isCloserToParent thanFirstLevelAnswer', () => {
    topic.add(4, 'one');
    topic.addChild(5, 4, 'two-1');
    topic.addChild(6, 5, 'three');
    topic.addChild(7, 4, 'two-2');
    assertEquals(['one', 'two-1', 'three', 'two-2'], topic.asList());
  });

  test('sort first level children in ascending order', () => {
    const topic = new TreeList<number>((a, b) => a - b);
    topic.add(4, 4);
    topic.addChild(5, 4, 2);
    topic.addChild(6, 4, 3);
    topic.addChild(6, 4, 1);
    assertEquals([4, 1, 2, 3], topic.asList());
  });

  test('sort first level children in descending order', () => {
    const topic = new TreeList<number>((a, b) => b - a);
    topic.add(4, 4);
    topic.addChild(5, 4, 2);
    topic.addChild(6, 4, 3);
    topic.addChild(6, 4, 1);
    assertEquals([4, 3, 2, 1], topic.asList());
  });
});
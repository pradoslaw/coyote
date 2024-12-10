import {beforeEach, describe, test} from "@jest/globals";
import {assertEquals} from "../../../survey/test/assert";
import {TreeList} from "./treeList";

describe('tree list', () => {
  let topic: TreeList<string>;

  beforeEach(() => {
    topic = new TreeList(() => 0);
  });

  function addChild<T>(topic: TreeList<T>, id: number, parentId: number, payload: T, ignoreChildren: boolean = false): void {
    topic.addChild(id, parentId, payload, ignoreChildren);
  }

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
    addChild(topic, 6, 4, 'green');
    assertEquals(['blue', 'green', 'red'], topic.asList());
  });

  test('secondLevelAnswer isCloserToParent thanFirstLevelAnswer', () => {
    topic.add(4, 'one');
    addChild(topic, 5, 4, 'two-1');
    addChild(topic, 6, 5, 'three');
    addChild(topic, 7, 4, 'two-2');
    assertEquals(['one', 'two-1', 'three', 'two-2'], topic.asList());
  });

  test('sort first level children in ascending order', () => {
    const topic = new TreeList<number>((a, b) => a - b);
    topic.add(4, 4);
    addChild(topic, 5, 4, 2);
    addChild(topic, 6, 4, 3);
    addChild(topic, 6, 4, 1);
    assertEquals([4, 1, 2, 3], topic.asList());
  });

  test('sort first level children in descending order', () => {
    const topic = new TreeList<number>((a, b) => b - a);
    topic.add(4, 4);
    addChild(topic, 5, 4, 2);
    addChild(topic, 6, 4, 3);
    addChild(topic, 6, 4, 1);
    assertEquals([4, 3, 2, 1], topic.asList());
  });

  test('the root is last child', () => {
    const topic = new TreeList<string>(() => 0);
    topic.add(4, 'root');
    assertEquals([{item: 'root', nestLevel: 0, hasNextSibling: false}], topic.treeItems());
  });

  test('the only child is the last child', () => {
    const topic = new TreeList<string>(() => 0);
    topic.add(15, 'root');
    addChild(topic, 16, 15, 'child');
    assertEquals([
      {nestLevel: 0, item: 'root', hasNextSibling: false},
      {nestLevel: 1, item: 'child', hasNextSibling: false},
    ], topic.treeItems());
  });

  test('the first child is not the last child', () => {
    const topic = new TreeList<string>(() => 0);
    topic.add(15, 'root');
    addChild(topic, 16, 15, 'child');
    addChild(topic, 17, 15, 'last child');
    assertEquals([
      {nestLevel: 0, item: 'root', hasNextSibling: false},
      {nestLevel: 1, item: 'child', hasNextSibling: true},
      {nestLevel: 1, item: 'last child', hasNextSibling: false},
    ], topic.treeItems());
  });

  test('children can be excluded', () => {
    const topic = new TreeList<string>(() => 0);
    topic.add(15, 'root');
    addChild(topic, 16, 15, 'without children', true);
    addChild(topic, 17, 16, 'child');
    addChild(topic, 18, 17, "grand child");
    assertEquals([
      {nestLevel: 0, item: 'root', hasNextSibling: false},
      {nestLevel: 1, item: 'without children', hasNextSibling: false},
    ], topic.treeItems());
  });
});

import {beforeEach, describe, test} from "@jest/globals";
import {assertEquals} from "../../../survey/test/assert";
import {MultipleRootsError, TreeList} from "./treeList";

describe('tree list', () => {
  let topic: TreeList<string>;

  beforeEach(() => {
    topic = new TreeList(() => 0);
  });

  function addChild<T>(topic: TreeList<T>, id: number, parentId: number, payload: T, ignoreChildren: boolean = false): void {
    topic.addChild(id, parentId, payload, ignoreChildren);
  }

  function treeItems<T>(topic: TreeList<T>): T[] {
    return topic.flatTreeItems().map(record => record.item);
  }

  test('emptyTopic hasNoPosts', () => {
    assertEquals([], treeItems(topic));
  });

  test('topicWithOnePost hasOnePost', () => {
    topic.setRoot(1, 'foo');
    assertEquals(['foo'], treeItems(topic));
  });

  test('answerPost isBelowHisParentPost', () => {
    topic.setRoot(1, 'root');
    addChild(topic, 4, 1, 'blue');
    addChild(topic, 5, 1, 'red');
    addChild(topic, 6, 4, 'green');
    assertEquals(['root', 'blue', 'green', 'red'], treeItems(topic));
  });

  test('secondLevelAnswer isCloserToParent thanFirstLevelAnswer', () => {
    topic.setRoot(4, 'one');
    addChild(topic, 5, 4, 'two-1');
    addChild(topic, 6, 5, 'three');
    addChild(topic, 7, 4, 'two-2');
    assertEquals(['one', 'two-1', 'three', 'two-2'], treeItems(topic));
  });

  test('sort first level children in ascending order', () => {
    const topic = new TreeList<number>((a, b) => a - b);
    topic.setRoot(4, 4);
    addChild(topic, 5, 4, 2);
    addChild(topic, 6, 4, 3);
    addChild(topic, 6, 4, 1);
    assertEquals([4, 1, 2, 3], treeItems(topic));
  });

  test('sort first level children in descending order', () => {
    const topic = new TreeList<number>((a, b) => b - a);
    topic.setRoot(4, 4);
    addChild(topic, 5, 4, 2);
    addChild(topic, 6, 4, 3);
    addChild(topic, 6, 4, 1);
    assertEquals([4, 3, 2, 1], treeItems(topic));
  });

  test('the root is last child', () => {
    const topic = new TreeList<string>(() => 0);
    topic.setRoot(4, 'root');
    assertEquals([{item: 'root', nestLevel: 0, hasNextSibling: false}], topic.flatTreeItems());
  });

  test('the only child is the last child', () => {
    const topic = new TreeList<string>(() => 0);
    topic.setRoot(15, 'root');
    addChild(topic, 16, 15, 'child');
    assertEquals([
      {nestLevel: 0, item: 'root', hasNextSibling: false},
      {nestLevel: 1, item: 'child', hasNextSibling: false},
    ], topic.flatTreeItems());
  });

  test('the first child is not the last child', () => {
    const topic = new TreeList<string>(() => 0);
    topic.setRoot(15, 'root');
    addChild(topic, 16, 15, 'child');
    addChild(topic, 17, 15, 'last child');
    assertEquals([
      {nestLevel: 0, item: 'root', hasNextSibling: false},
      {nestLevel: 1, item: 'child', hasNextSibling: true},
      {nestLevel: 1, item: 'last child', hasNextSibling: false},
    ], topic.flatTreeItems());
  });

  test('children can be excluded', () => {
    const topic = new TreeList<string>(() => 0);
    topic.setRoot(15, 'root');
    addChild(topic, 16, 15, 'without children', true);
    addChild(topic, 17, 16, 'child');
    addChild(topic, 18, 17, "grand child");
    assertEquals([
      {nestLevel: 0, item: 'root', hasNextSibling: false},
      {nestLevel: 1, item: 'without children', hasNextSibling: false},
    ], topic.flatTreeItems());
  });

  test('only one root is allowed', () => {
    const topic = new TreeList<string>(() => 0);
    topic.setRoot(15, 'root');
    expect(() => topic.setRoot(15, 'root')).toThrow(MultipleRootsError);
  });

  test('listing records without root returns an empty array', () => {
    const topic = new TreeList<string>(() => 0);
    assertEquals([], topic.flatTreeItems());
  });
});

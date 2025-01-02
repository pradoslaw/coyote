import {describe, test} from "@jest/globals";

import {assertEquals} from "../../../survey/test/assert";
import {MissingParentError, TreeMap} from "./treeMap";

describe('tree map', () => {
  test('empty map, childrenOf() returns an empty array', () => {
    const map = new TreeMap();
    assertEquals([], map.childrenOf(1));
  });

  test("childrenOf() includes pivot post child", () => {
    const map = new TreeMap<number, string>();
    map.put(1, 'mark');
    map.put(2, 'john');
    map.put(3, 'luke', 2);
    assertEquals(['luke'], map.childrenOf(2));
  });

  test("childrenOf() includes pivot post next level child", () => {
    const map = new TreeMap<number, string>();
    map.put(1, 'mark');
    map.put(2, 'john', 1);
    map.put(3, 'luke', 2);
    assertEquals(['john', 'luke'], map.childrenOf(1));
  });

  test("childrenOf() includes the second next level child", () => {
    const map = new TreeMap<number, string>();
    map.put(1, 'mark');
    map.put(2, 'john', 1);
    map.put(3, 'luke', 2);
    map.put(4, 'paul', 3);
    assertEquals(['john', 'luke', 'paul'], map.childrenOf(1));
  });

  test('fail if new item does not link to an existing item', () => {
    const map = new TreeMap<number, string>();
    expect(() => map.put(3, 'luke', 2)).toThrow(MissingParentError);
  });
});

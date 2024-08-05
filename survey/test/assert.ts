import {strict as assert} from "node:assert";

export function assertTrue(condition: boolean): void {
  assert(condition);
}

export function assertFalse(condition: boolean): void {
  assertTrue(!condition);
}

export function assertEquals(actual: any, expected: any): void {
  assert.deepStrictEqual(actual, expected);
}

export function assertMatch(string: string, regexp: RegExp): void {
  assert.match(string, regexp);
}

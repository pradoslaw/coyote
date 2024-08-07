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

export function assertContains(array: any[], expected: any): void {
  assertTrue(array.includes(expected));
}

export function assertNotContains(array: any[], expected: any): void {
  assertFalse(array.includes(expected));
}

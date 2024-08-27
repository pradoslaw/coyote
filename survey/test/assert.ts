import {strict as assert} from "node:assert";

export function assertTrue(condition: boolean): void {
  assert(condition);
}

export function assertFalse(condition: boolean): void {
  assert.equal(condition, false);
}

export function assertEquals(actual: any, expected: any): void {
  assert.deepStrictEqual(actual, expected);
}

export function assertMatch(string: string, regexp: RegExp): void {
  assert.match(string, regexp);
}

export function assertContains(array: any[], value: any): void {
  if (!array.includes(value)) {
    assert.fail(containsMessage(array, value));
  }
}

function containsMessage(array: any[], value: any): string {
  return `Failed to assert that array ${JSON.stringify(array)} contains ${JSON.stringify(value)}.`;
}

export function assertNotContains(array: any[], expected: any): void {
  assertFalse(array.includes(expected));
}

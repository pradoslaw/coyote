import {strict as assert} from "node:assert";

export function assertMatch(string: string, regexp: RegExp): void {
  assert.match(string, regexp);
}

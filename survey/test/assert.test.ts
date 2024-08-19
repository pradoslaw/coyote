import {describe, test} from "@jest/globals";
import {strict as assert} from "node:assert";

import {assertContains, assertEquals, assertFalse, assertMatch, assertNotContains, assertTrue} from "./assert";

describe('assert', () => {
  describe('assertTrue', () => {
    test('pass', () => passes(() => assertTrue(true)));
    test('fail', () => fails(() => assertTrue(false)));
  });

  describe('assertFalse', () => {
    test('pass', () => passes(() => assertFalse(false)));
    test('fail', () => fails(() => assertFalse(true)));
  });

  describe('assertEquals', () => {
    test('pass', () => passes(() => assertEquals('foo', 'foo')));
    test('fail', () => fails(() => assertEquals('foo', 'bar')));

    test('pass for array', () => passes(() => assertEquals(['foo'], ['foo'])));
    test('fail for array', () => fails(() => assertEquals(['foo'], ['bar'])));
  });

  describe('assertMatch', () => {
    test('pass', () => passes(() => assertMatch('foo', /foo/)));
    test('fail', () => fails(() => assertMatch('foo', /bar/)));
  });

  describe('assertContains', () => {
    test('pass', () => passes(() => assertContains(['foo', 'bar'], 'bar')));
    test('fail', () => fails(() => assertContains(['foo'], 'bar')));
  });

  describe('assertNotContains', () => {
    test('pass', () => passes(() => assertNotContains(['foo'], 'bar')));
    test('fail', () => fails(() => assertNotContains(['foo', 'bar'], 'bar')));
  });

  function passes(operation: () => void): void {
    operation();
  }

  function fails(operation: () => void): void {
    assert.throws(operation, assert.AssertionError);
  }
});

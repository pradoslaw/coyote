import {describe, test} from '@jest/globals';

import {assertEquals} from "../../test/assert";
import diffFormat from "./diffFormat";

describe('survey', () => {
  describe('time difference', () => {
    test('0 seconds', () =>
      assertFormat(0, '0t:0d:00h:00min'));
    test('1 second', () =>
      assertFormat(1, '0t:0d:00h:01min'));
    test('60 seconds', () =>
      assertFormat(60, '0t:0d:00h:01min'));
    test('61 seconds', () =>
      assertFormat(61, '0t:0d:00h:02min'));
    test('120 seconds', () =>
      assertFormat(120, '0t:0d:00h:02min'));
    test('121 seconds', () =>
      assertFormat(121, '0t:0d:00h:03min'));
    test('59 minutes', () =>
      assertFormat(60 * 59, '0t:0d:00h:59min'));
    test('59 minutes, one second', () =>
      assertFormat(60 * 59 + 1, '0t:0d:01h:00min'));
    test('1 hour', () =>
      assertFormat(60 * 60, '0t:0d:01h:00min'));
    test('1 day', () =>
      assertFormat(60 * 60 * 24, '0t:1d:00h:00min'));
    test('1 week', () =>
      assertFormat(60 * 60 * 24 * 7, '1t:0d:00h:00min'));
    test('negative seconds', () =>
      assertFormat(-60 * 60, '0t:0d:00h:00min'));

    function assertFormat(seconds: number, expectedFormat: string): void {
      assertEquals(diffFormat(seconds), expectedFormat);
    }
  });
});

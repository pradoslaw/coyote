import {describe, test} from "@jest/globals";
import {assertEquals, assertTrue} from "../../test/assert";
import {Component, render} from "../../test/render";

import VueToggle from "./toggle";

describe('survey', () => {
  describe('toggle', () => {
    test('options', () => {
      const toggle: Component = render(VueToggle, {
        first: 'foo',
        second: 'bar',
      });
      assertEquals(toggle.textBy('.first'), 'foo');
      assertEquals(toggle.textBy('.second'), 'bar');
    });

    test('first selected', () => {
      const toggle: Component = render(VueToggle, {selected: 'first'});
      assertTrue(toggle.exists('.first.active'));
    });

    test('second selected', () => {
      const toggle: Component = render(VueToggle, {selected: 'second'});
      assertTrue(toggle.exists('.second.active'));
    });

    test('select second', async () => {
      const toggle: Component = render(VueToggle, {selected: 'first'});
      await toggle.click('.second');
      assertTrue(toggle.exists('.second.active'));
    });

    test('select first', async () => {
      const toggle: Component = render(VueToggle, {selected: 'second'});
      await toggle.click('.first');
      assertTrue(toggle.exists('.first.active'));
    });

    test('emit selected', async () => {
      const toggle: Component = render(VueToggle, {selected: 'first'});
      await toggle.click('.second');
      assertTrue(toggle.emitted('change'));
      assertEquals(toggle.emittedValue('change'), 'second');
    });
  });
});

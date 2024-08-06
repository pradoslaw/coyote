import {beforeEach, describe, test} from '@jest/globals';
import {assertMatch, assertTrue} from "../../test/assert";
import {type Component, render} from "../../test/render";
import SurveyBadge from "./badge";

describe('badge screen', () => {
  let badge: Component;

  beforeEach(() => {
    badge = render(SurveyBadge);
  });

  test('render badge screen', () => {
    assertMatch(badge.text(), /Zmieniaj forum na lepsze!/);
  });

  test('badge emits engage event', async () => {
    await badge.click('button.btn');
    assertTrue(badge.emitted('engage'));
  });
});

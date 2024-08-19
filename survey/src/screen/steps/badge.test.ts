import {describe, test} from '@jest/globals';
import {assertContains, assertFalse, assertMatch, assertNotContains, assertTrue} from "../../../test/assert";
import {render} from "../../../test/render";
import SurveyBadge from "./badge";

describe('badge step', () => {
  test('render badge step', () => {
    const badge = render(SurveyBadge);
    assertMatch(badge.text(), /Zmieniaj forum na lepsze!/);
  });

  test('badge emits engage event', async () => {
    const badge = render(SurveyBadge);
    await badge.click('button.btn-engage');
    assertTrue(badge.emitted('engage'));
  });

  describe('tooltip', () => {
    test('shows tooltip', () => {
      const badge = render(SurveyBadge, {tooltip: true});
      assertTrue(badge.exists('.survey-tooltip-container'));
    });

    test('hides tooltip', () => {
      const badge = render(SurveyBadge, {tooltip: false});
      assertFalse(badge.exists('.survey-tooltip-container'));
    });

    test('badge emits notice event', async () => {
      const badge = render(SurveyBadge, {tooltip: true});
      await badge.click('button.btn-notice');
      assertTrue(badge.emitted('notice'));
    });
  });

  describe('overlay', () => {
    test('shows overlay with tooltip', () => {
      const badge = render(SurveyBadge, {tooltip: true});
      assertContains(badge.classes(), 'overlay');
    });

    test('hides overlay without tooltip', () => {
      const badge = render(SurveyBadge, {tooltip: false});
      assertNotContains(badge.classes(), 'overlay');
    });
  });
});

import {describe, test} from '@jest/globals';
import {assertContains, assertEquals, assertFalse, assertMatch, assertNotContains, assertTrue} from "../../../test/assert";
import {Component, render} from "../../../test/render";
import SurveyBadge from "./badge";

describe('badge step', () => {
  test('render badge step', () => {
    assertMatch(renderBadge().text(), /Zmieniaj forum na lepsze!/);
  });

  test('badge emits engage event', async () => {
    const badge = render(SurveyBadge);
    await badge.click('button.btn-engage');
    assertTrue(badge.emitted('engage'));
  });

  describe('tooltip', () => {
    test('shows tooltip', () => {
      const badge = renderBadge({tooltip: true});
      assertTrue(badge.exists('.survey-tooltip-container'));
    });

    test('hides tooltip', () => {
      const badge = renderBadge({tooltip: false});
      assertFalse(badge.exists('.survey-tooltip-container'));
    });

    test('badge emits notice event', async () => {
      const badge = renderBadge({tooltip: true});
      await badge.click('button.btn-notice');
      assertTrue(badge.emitted('notice'));
    });
  });

  describe('overlay', () => {
    test('shows overlay with tooltip', () => {
      const badge = renderBadge({tooltip: true});
      assertContains(badge.classes(), 'overlay');
    });

    test('hides overlay without tooltip', () => {
      const badge = renderBadge({tooltip: false});
      assertNotContains(badge.classes(), 'overlay');
    });
  });

  describe('long badge', () => {
    describe('button button', () => {
      test('add toggle button', () => {
        const badge = renderBadge({tooltip: false});
        assertTrue(badge.exists('.collapse-toggle'));
      });
      test('toggle button should be left, if badge is long', () => {
        const badge = renderBadge({long: true});
        assertContains(badge.classesOf('.collapse-toggle i'), 'fa-chevron-right');
      });
      test('toggle button should be right, if badge is short', () => {
        const badge = renderBadge({long: false});
        assertContains(badge.classesOf('.collapse-toggle i'), 'fa-chevron-left');
      });
    });

    describe('changing state', () => {
      test('emit event on toggle click', async () => {
        const badge = renderBadge();
        await badge.click('.collapse-toggle');
        assertTrue(badge.emitted('collapse'));
      });
      test('collapse down', async () => {
        const badge = renderBadge({long: true});
        await badge.click('.collapse-toggle');
        assertEquals(badge.emittedValue('collapse'), false);
      });
      test('collapse up', async () => {
        const badge = renderBadge({long: false});
        await badge.click('.collapse-toggle');
        assertEquals(badge.emittedValue('collapse'), true);
      });
    });

    describe('collapsed view', () => {
      test('hide text in collapsed', () => {
        const badge = renderBadge({long: false});
        assertFalse(badge.exists('span'));
        assertEquals(badge.textBy('button'), '');
      });
      test('show text in long badge', () => {
        const badge = renderBadge({long: true});
        assertEquals(badge.textBy('span'), 'Zmieniaj forum na lepsze!');
        assertEquals(badge.textBy('button'), 'Testuj');
      });
    });
  });

  function renderBadge({long = true, tooltip = false} = {}): Component {
    return render(SurveyBadge, {tooltip, long});
  }
});

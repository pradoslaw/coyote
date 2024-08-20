import {describe, test} from '@jest/globals';
import {assertEquals, assertMatch, assertTrue} from "../../test/assert";
import {Component, render} from "../../test/render";
import VueScreen, {Experiment, Screen} from "./screen";

describe('survey screen', () => {
  describe('screens', () => {
    test('enroll', () => {
      assertMatch(
        renderScreen('enroll').text(),
        /Zmieniaj forum na lepsze!/);
    });

    test('participate', () => {
      const screen = renderScreen('participate', {title: 'Foo'});
      assertMatch(screen.text(), /Pierwotna wersja/);
    });

    test('badge', () => {
      assertMatch(
        renderScreen('badge').text(),
        /Testuj/);
    });

    test('badge tooltip', () => {
      assertMatch(
        renderScreen('badge-tooltip').text(),
        /Tutaj możesz zmienić swój wybór w dowolnym momencie./);
    });

    test('none', () => {
      assertEquals(renderScreen('none').text(), '');
    });
  });

  describe('events', () => {
    describe('enroll', () => {
      test('opt in', async () => {
        const screen = renderScreen('enroll');
        await screen.click('button.btn-primary');
        assertTrue(screen.emitted('enrollOptIn'));
      });
      test('opt out', async () => {
        const screen = renderScreen('enroll');
        await screen.click('button.btn-secondary');
        assertTrue(screen.emitted('enrollOptOut'));
      });
    });

    describe('participate', () => {
      test('opt in', async () => {
        const screen = renderScreen('participate', {optedIn: 'legacy'});
        await screen.click('.survey-toggle span.second');
        await screen.click('button.btn-primary');
        assertTrue(screen.emitted('experimentOptIn'));
      });

      test('opt out', async () => {
        const screen = renderScreen('participate', {optedIn: 'modern'});
        await screen.click('.survey-toggle span.first');
        await screen.click('button.btn-primary');
        assertTrue(screen.emitted('experimentOptOut'));
      });

      test('preview', async () => {
        const screen = renderScreen('participate', {optedIn: 'modern'});
        await screen.click('.survey-toggle span.first');
        assertTrue(screen.emitted('experimentPreview'));
        assertEquals(screen.emittedValue('experimentPreview'), 'out');
      });

      test('close', async () => {
        const screen = renderScreen('participate');
        await screen.click('button.btn.btn-secondary');
        assertTrue(screen.emitted('experimentClose'));
      });
    });

    describe('badge', () => {
      test('engage', async () => {
        const screen = renderScreen('badge');
        await screen.click('button.btn-engage');
        assertTrue(screen.emitted('badgeEngage'));
      });
      test('engage with tooltip', async () => {
        const screen = renderScreen('badge-tooltip');
        await screen.click('button.btn-engage');
        assertTrue(screen.emitted('badgeEngage'));
      });
      test('notice', async () => {
        const screen = renderScreen('badge-tooltip');
        await screen.click('button.btn-notice');
        assertTrue(screen.emitted('badgeNotice'));
      });
    });
  });

  function renderScreen(screen: Screen, experiment: Experiment | object = {}): Component {
    return render(VueScreen, {screen, experiment});
  }
});

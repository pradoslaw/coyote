import {describe, test} from "@jest/globals";

import {assertEquals, assertFalse, assertMatch, assertTrue} from "../test/assert";
import {type Component, render} from "../test/render";
import SurveyScreen, {Screen} from "./screen/screen";
import VueTally, {type State} from "./tally";

describe('survey', () => {
  describe('tally', () => {
    describe('screens', () => {
      describe('initial screen', () => {
        test('anonymous user sees nothing', () =>
          assertScreen(renderTally('survey-none'), 'none'));

        test('invited user sees enroll', () =>
          assertScreen(renderTally('survey-invited'), 'enroll'));

        test('user who declined sees nothing', () =>
          assertScreen(renderTally('survey-declined'), 'none'));

        test('user who just accepted sees badge with tooltip', () =>
          assertScreen(renderTally('survey-accepted'), 'badge-tooltip'));

        test('user who already saw tooltip, sees regular badge', () =>
          assertScreen(renderTally('survey-instructed'), 'badge'));

        test('user has left the survey, sees nothing', () =>
          assertScreen(renderTally('survey-gone'), 'none'));
      });

      test('pass experiment to screen', () => {
        const tally = renderTally('survey-none', {title: 'foo'});
        assertEquals(tally.passedTo(SurveyScreen, 'experiment'), {title: 'foo'});
      });

      describe('user actions', () => {
        test('decline invitation', async () =>
          assertScreen(await tallyWithEnrollAction('enrollOptOut'), 'none'));
        test('accept invitation', async () =>
          assertScreen(await tallyWithEnrollAction('enrollOptIn'), 'participate'));

        test('decline invitation, see notification', async () =>
          assertNotification(
            await tallyWithEnrollAction('enrollOptOut'),
            'Zmieniaj forum na lepsze!',
            '<i class="fa-solid fa-bug-slash"></i> Wypisano z udziału w testach.'));

        test('accept invitation, see notification', async () =>
          assertNotification(
            await tallyWithEnrollAction('enrollOptIn'),
            'Zmieniaj forum na lepsze!',
            '<i class="fa-solid fa-flask"></i> Dołączyłeś do testów forum!'));

        describe('close participate', () => {
          test('when user is uninstructed', async () =>
            assertScreen(
              await tallyWithParticipateAction('experimentClose'),
              'badge-tooltip'));

          test('when user is instructed', async () =>
            assertScreen(
              await tallyWithParticipateAction('experimentClose', {}, 'survey-instructed'),
              'badge'));
        });

        test('notice tooltip', async () => {
          const tally = renderTally('survey-accepted');
          await userAction(tally, 'badgeNotice');
          assertScreen(tally, 'badge');
        });

        test('engage', async () => {
          const tally = renderTally('survey-instructed');
          await userAction(tally, 'badgeEngage');
          assertScreen(tally, 'participate');
        });

        test('experiment opt-in', async () =>
          assertScreen(
            await tallyWithParticipateAction('experimentOptIn'),
            'badge-tooltip'));

        test('experiment opt-out', async () =>
          assertScreen(
            await tallyWithParticipateAction('experimentOptOut'),
            'badge-tooltip'));

        test('experiment opt-out, when instructed', async () =>
          assertScreen(
            await tallyWithParticipateAction('experimentOptOut', {}, 'survey-instructed'),
            'badge'));

        test('experiment opt-in, see notification', async () =>
          assertNotification(
            await tallyWithParticipateAction('experimentOptIn', {title: 'Foo'}),
            'Foo',
            '<i class="fa-solid fa-toggle-on"></i> Uruchomiono nową wersję.'));

        test('experiment opt-out, see notification', async () =>
          assertNotification(
            await tallyWithParticipateAction('experimentOptOut', {title: 'Foo'}),
            'Foo',
            '<i class="fa-solid fa-toggle-off"></i> Przywrócono pierwotną wersję.'));
      });

      describe('notify backend', () => {
        describe('experiment opt', () => {
          test('emit event', async () => {
            const tally = await tallyWithParticipateAction('experimentOptOut');
            assertTrue(tally.emitted('experimentOpt'));
          });
          test("closing doesn't notify", async () => {
            const tally = await tallyWithParticipateAction('experimentClose');
            assertFalse(tally.emitted('experimentOpt'));
          });
          test('notify experiment opt-out', async () => {
            const tally = await tallyWithParticipateAction('experimentOptOut');
            assertEquals(tally.emittedValue('experimentOpt'), false);
          });
          test('notify experiment opt-in', async () => {
            const tally = await tallyWithParticipateAction('experimentOptIn');
            assertEquals(tally.emittedValue('experimentOpt'), true);
          });
        });

        describe('survey state change', () => {
          test('emit event', async () => {
            const tally = await tallyWithEnrollAction('enrollOptOut');
            assertTrue(tally.emitted('change'));
          });
          test('decline survey', async () => {
            const tally = await tallyWithEnrollAction('enrollOptOut');
            assertEquals(tally.emittedValue('change'), 'survey-declined');
          });
          test('accept survey', async () => {
            const tally = await tallyWithEnrollAction('enrollOptIn');
            assertEquals(tally.emittedValue('change'), 'survey-accepted');
          });
          test('notice badge', async () => {
            const tally = renderTally('survey-accepted');
            await userAction(tally, 'badgeNotice');
            assertEquals(tally.emittedValue('change'), 'survey-instructed');
          });
        });
      });

      test('show only last notification', async () => {
        const tally = renderTally('survey-accepted');
        await userAction(tally, 'experimentOptIn');
        await userAction(tally, 'experimentOptOut');
        assertEquals(tally.notifications.count(), 1);
        assertMatch(tally.notifications.content(), /Przywrócono pierwotną wersję./);
      });

      function assertScreen(tally: Component, expected: Screen): void {
        assertEquals(tally.passedTo(SurveyScreen, 'screen'), expected);
      }

      function renderTally(state: State, experiment?: object): Component {
        return render(VueTally, {state, experiment: experiment || {}});
      }

      function userAction(tally: Component, eventName: string): Promise<void> {
        return tally.emitFrom(SurveyScreen, eventName);
      }

      async function tallyWithParticipateAction(eventName: string, experiment?: object, state?: State): Promise<Component> {
        const tally = await tallyWithEnrollAction('enrollOptIn', experiment, state);
        await userAction(tally, eventName);
        return tally;
      }

      async function tallyWithEnrollAction(eventName: string, experiment?: object, state?: State): Promise<Component> {
        const tally = renderTally(state || 'survey-invited', experiment);
        await userAction(tally, eventName);
        return tally;
      }

      function assertNotification(tally: Component, expectedTitle: string, expectedText: string): void {
        assertEquals(tally.notifications.title(), expectedTitle);
        assertEquals(tally.notifications.content(), expectedText);
        assertEquals(tally.notifications.content(), expectedText);
      }
    });
  });
});

import {describe, test} from '@jest/globals';
import {assertEquals, assertFalse, assertMatch, assertTrue} from "../../test/assert";
import {Component, render} from "../../test/render";
import SurveyParticipate from "./participate";

describe('participate screen', () => {
  test('render participate screen title', () => {
    assertMatch(renderParticipate().text(), /Aktualne testy/);
  });

  test('emit close event', () => {
    const participate = renderParticipate();
    participate.click('button.btn');
    assertTrue(participate.emitted('close'));
  });

  describe('survey experiment', () => {
    test('experiment title', () => {
      const participate = renderParticipate({title: 'Styl komentarzy.'});
      assertEquals(participate.textBy('h4'), 'Styl komentarzy.');
    });

    test('experiment reason', () => {
      const participate: Component = renderParticipate({reason: 'Lorem <code>ipsum</code>'});
      assertEquals(participate.innerHtml('p.reason span'), 'Lorem <code>ipsum</code>');
    });

    test('experiment solution', () => {
      const participate: Component = renderParticipate({solution: 'dolor sit'});
      assertEquals(participate.textBy('p.solution span'), 'dolor sit');
    });

    test('experiment due time', () => {
      const participate = renderParticipate({dueTime: '7t:2d:07h:57min'});
      assertEquals(participate.textBy('.timer span'), '7t:2d:07h:57min');
    });

    test('experiment due time', () => {
      const participate = renderParticipate({dueTime: '7t:2d:07h:57min'});
      assertEquals(participate.textBy('.timer span'), '7t:2d:07h:57min');
    });

    describe('experiment opted', () => {
      test('opted in', () => {
        const participate = renderParticipate({optedIn: true});
        assertTrue(participate.inputChecked('input'));
      });
      test('opted out', () => {
        const participate = renderParticipate({optedIn: false});
        assertFalse(participate.inputChecked('input'));
      });
    });

    describe('experiment opt', () => {
      test('emit event on input change', async () => {
        const participate = renderParticipate();
        await participate.inputToggle('label.experimentOpt input');
        assertTrue(participate.emitted('experimentOpt'));
      });

      test('emit event with optIn', async () => {
        const participate = renderParticipate({optedIn: false});
        await participate.inputToggle('label.experimentOpt input');
        assertEquals(participate.emittedValue('experimentOpt'), 'in');
      });

      test('emit event with optOut', async () => {
        const participate = renderParticipate({optedIn: true});
        await participate.inputToggle('label.experimentOpt input');
        assertEquals(participate.emittedValue('experimentOpt'), 'out');
      });
    });
  });

  function renderParticipate({
                               title = '',
                               reason = '',
                               solution = '',
                               dueTime = '',
                               optedIn = false,
                             } = {}): Component {
    return render(SurveyParticipate, {
      experiment: {title, optedIn, reason, solution, dueTime},
    });
  }
});

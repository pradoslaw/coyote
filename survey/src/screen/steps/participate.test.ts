import {describe, test} from '@jest/globals';
import {assertEquals, assertMatch, assertTrue} from "../../../test/assert";
import {Component, render} from "../../../test/render";
import SurveyParticipate from "./participate";

describe('participate step', () => {
  test('render participate title', () => {
    assertMatch(renderParticipate().text(), /Pierwotna wersja/);
  });

  test('emit close event', () => {
    const participate = renderParticipate();
    participate.click('button.btn.btn-secondary');
    assertTrue(participate.emitted('close'));
  });

  describe('survey experiment', () => {
    test('experiment title', () => {
      const participate = renderParticipate({title: 'Styl komentarzy.'});
      assertEquals(participate.textBy('h3'), 'Styl komentarzy.');
    });

    test('experiment reason', () => {
      const participate: Component = renderParticipate({reason: 'Lorem <code>ipsum</code>'});
      assertEquals(participate.innerHtml('p.reason span'), 'Lorem <code>ipsum</code>');
    });

    test('experiment solution', () => {
      const participate: Component = renderParticipate({solution: 'dolor sit'});
      assertEquals(participate.textBy('p.solution span'), 'dolor sit');
    });

    test('experiment legacy image', () => {
      const participate: Component = renderParticipate({imageLegacy: 'foo.png'});
      assertEquals(participate.attributeOf('img', 'src'), 'foo.png');
    });

    test('experiment modern image', async () => {
      const participate: Component = renderParticipate({imageModern: 'bar.png', optedIn: true});
      assertEquals(participate.attributeOf('img', 'src'), 'bar.png');
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
        assertTrue(participate.exists('.survey-toggle .second.active'));
      });
      test('opted out', () => {
        const participate = renderParticipate({optedIn: false});
        assertTrue(participate.exists('.survey-toggle .first.active'));
      });
    });

    describe('experiment opt', () => {
      test('emit event with optIn', async () => {
        const participate = renderParticipate({optedIn: false});
        await participate.click('.survey-toggle span.second');
        await participate.click('button.btn-primary');
        assertEquals(participate.emittedValue('experimentOpt'), 'in');
      });

      test('emit event with optOut', async () => {
        const participate = renderParticipate({optedIn: true});
        await participate.click('.survey-toggle span.first');
        await participate.click('button.btn-primary');
        assertEquals(participate.emittedValue('experimentOpt'), 'out');
      });
    });
  });

  function renderParticipate({
                               title = '',
                               reason = '',
                               solution = '',
                               dueTime = '',
                               imageLegacy = '',
                               imageModern = '',
                               optedIn = false,
                             } = {}): Component {
    return render(SurveyParticipate, {
      experiment: {title, optedIn, reason, solution, dueTime, imageLegacy, imageModern},
    });
  }
});

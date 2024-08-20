import {describe, test} from '@jest/globals';

import {assertEquals, assertFalse, assertMatch, assertTrue} from "../../../test/assert";
import {Component, render} from "../../../test/render";
import type {ToggleValue} from "../toggle";
import SurveyParticipate, {ExperimentOpt} from "./participate";

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
      const participate: Component = renderParticipate({imageLegacy: 'foo.png', optedIn: 'legacy'});
      assertEquals(participate.attributeOf('img', 'src'), 'foo.png');
    });

    test('experiment modern image', async () => {
      const participate: Component = renderParticipate({imageModern: 'bar.png', optedIn: 'modern'});
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
        const participate = renderParticipate({optedIn: 'modern'});
        assertTrue(participate.exists('.survey-toggle .second.active'));
      });
      test('opted out', () => {
        const participate = renderParticipate({optedIn: 'legacy'});
        assertTrue(participate.exists('.survey-toggle .first.active'));
      });
    });

    describe('experiment opt', () => {
      test('emit event with optIn', async () => {
        const participate = renderParticipate({optedIn: 'legacy'});
        await toggleExperimentOptIn(participate);
        await participate.click('button.btn-primary');
        assertEquals(participate.emittedValue('experimentOpt'), 'in');
      });

      test('emit event with optOut', async () => {
        const participate = renderParticipate({optedIn: 'modern'});
        await toggleExperimentOptOut(participate);
        await participate.click('button.btn-primary');
        assertEquals(participate.emittedValue('experimentOpt'), 'out');
      });
    });

    describe('active selection', () => {
      test('opted out, select is active', () =>
        assertTrue(isSelectionActive(renderParticipate({optedIn: 'legacy'}))));
      test('opted in, select is active', () =>
        assertTrue(isSelectionActive(renderParticipate({optedIn: 'modern'}))));

      test('after opting in, active disappears', async () => {
        const participate = renderParticipate({optedIn: 'legacy'});
        await toggleExperimentOptIn(participate);
        assertFalse(isSelectionActive(participate));
      });

      test('after opting out, active disappears', async () => {
        const participate = renderParticipate({optedIn: 'modern'});
        await toggleExperimentOptOut(participate);
        assertFalse(isSelectionActive(participate));
      });

      test('when opted nothing', () => {
        const participate = renderParticipate({optedIn: 'none'});
        assertFalse(isSelectionActive(participate));
      });

      test('when opted nothing, after opting in', async () => {
        const participate = renderParticipate({optedIn: 'none'});
        await toggleExperimentOptIn(participate);
        assertFalse(isSelectionActive(participate));
      });

      test('when opted nothing, after opting and out', async () => {
        const participate = renderParticipate({optedIn: 'none'});
        await toggleExperimentOptIn(participate);
        await toggleExperimentOptOut(participate);
        assertFalse(isSelectionActive(participate));
      });
    });
  });

  describe('isPreviewActive()', () => {
    test('choice legacy is active with first option', async () =>
      assertTrue(await isPreviewActive("legacy", 'first')));

    test('choice legacy is not active with first option', async () =>
      assertFalse(await isPreviewActive("legacy", 'second')));

    test('choice modern is active with second option', async () =>
      assertTrue(await isPreviewActive("modern", 'second')));

    test('choice modern is not active with first option', async () =>
      assertFalse(await isPreviewActive("modern", 'first')));

    test('choice none is not active with first or second option', async () => {
      assertFalse(await isPreviewActive("none", 'first'));
      assertFalse(await isPreviewActive("none", 'second'));
    });

    async function isPreviewActive(opt: ExperimentOpt, selected: ToggleValue): Promise<boolean> {
      const participate = renderParticipate({optedIn: opt});
      if (selected === 'first') {
        await toggleExperimentOptOut(participate);
      } else {
        await toggleExperimentOptIn(participate);
      }
      return isSelectionActive(participate);
    }
  });

  describe('notify about choice preview', () => {
    test('preview modern', async () => {
      const participate = renderParticipate({optedIn: 'modern'});
      await toggleExperimentOptOut(participate);
      assertEquals(participate.emittedValue('experimentPreview'), 'out');
    });
    test('preview legacy', async () => {
      const participate = renderParticipate({optedIn: 'legacy'});
      await toggleExperimentOptIn(participate);
      assertEquals(participate.emittedValue('experimentPreview'), 'in');
    });
  });

  function isSelectionActive(component: Component): boolean {
    return component.classesOf('.preview-container').includes('active');
  }

  async function toggleExperimentOptIn(participate: Component): Promise<void> {
    await participate.click('.survey-toggle span.second');
  }

  async function toggleExperimentOptOut(participate: Component): Promise<void> {
    await participate.click('.survey-toggle span.first');
  }

  function renderParticipate({
                               title = '',
                               reason = '',
                               solution = '',
                               dueTime = '',
                               imageLegacy = '',
                               imageModern = '',
                               optedIn = 'legacy',
                             }: ExperimentBuilder = {}): Component {
    return render(SurveyParticipate, {
      experiment: {title, optedIn, reason, solution, dueTime, imageLegacy, imageModern},
    });
  }
});

interface ExperimentBuilder {
  title?: string;
  reason?: string;
  solution?: string;
  dueTime?: string;
  imageLegacy?: string;
  imageModern?: string;
  optedIn?: ExperimentOpt;
}

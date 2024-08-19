import {beforeEach, describe, test} from '@jest/globals';
import {assertEquals, assertMatch, assertTrue} from "../../../test/assert";
import {type Component, render} from "../../../test/render";
import SurveyEnroll from "./enroll";

describe('enroll step', () => {
  let enroll: Component;

  beforeEach(() => {
    enroll = render(SurveyEnroll);
  });

  test('render enroll title', () => {
    assertMatch(enroll.text(), /Zmieniaj forum na lepsze!/);
  });

  test('enroll opt-in for testing', async () => {
    await enroll.click('button.btn-primary');
    assertEmitted('enrollOpt', 'in');
  });

  test('enroll opt-out of testing', async () => {
    await enroll.click('button.btn-secondary');
    assertEmitted('enrollOpt', 'out');
  });

  function assertEmitted(eventName: string, value: string): void {
    assertTrue(enroll.emitted(eventName));
    assertEquals(enroll.emittedValue(eventName), value);
  }
});

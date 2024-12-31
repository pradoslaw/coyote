import {describe, test} from '@jest/globals';

import {assertEquals} from "../../../survey/test/assert";
import declination, {Inflection} from "./declination";

describe('declination', () => {
  test('inflection of 1 as singular accusative', () => {
    assertEquals('1 pozostała odpowiedź', inflection(1));
  });
  test('inflection of paucal number as plural accusative', () => {
    assertEquals('2 pozostałe odpowiedzi', inflection(2));
    assertEquals('3 pozostałe odpowiedzi', inflection(3));
    assertEquals('4 pozostałe odpowiedzi', inflection(4));
  });
  test('inflection of high numbers as genitive', () => {
    assertEquals('5 pozostałych odpowiedzi', inflection(5));
    assertEquals('6 pozostałych odpowiedzi', inflection(6));
  });
  test('inflection of teens as genitive', () => {
    assertEquals('11 pozostałych odpowiedzi', inflection(11));
    assertEquals('12 pozostałych odpowiedzi', inflection(12));
    assertEquals('13 pozostałych odpowiedzi', inflection(13));
    assertEquals('14 pozostałych odpowiedzi', inflection(14));
    assertEquals('15 pozostałych odpowiedzi', inflection(15));
  });
  test('inflection of compound numbers as genitive', () => {
    assertEquals('21 pozostałych odpowiedzi', inflection(21));
    assertEquals('25 pozostałych odpowiedzi', inflection(25));
  });
  test('inflection of compound numbers with paucal number as accusative', () => {
    assertEquals('22 pozostałe odpowiedzi', inflection(22));
    assertEquals('23 pozostałe odpowiedzi', inflection(23));
    assertEquals('24 pozostałe odpowiedzi', inflection(24));
  });
  test('inflection of teens in hundreds as genitive', () => {
    assertEquals('112 pozostałych odpowiedzi', inflection(112));
  });

  function inflection(value: number): string {
    const forms: Inflection = ['pozostała odpowiedź', 'pozostałe odpowiedzi', 'pozostałych odpowiedzi'];
    return value + ' ' + declination(value, forms);
  }
});

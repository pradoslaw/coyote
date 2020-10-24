import declination from "./declination";

test('declination test', () => {
  expect(declination(1, ['wątek', 'wątków', 'wątków'])).toBe('wątek');
  expect(declination(2, ['głos', 'głosy', 'głosów'])).toBe('głosy');
});

import isImage from "./media";

test('declination test', () => {
  expect(isImage('foo.png')).toBe(true);
  expect(isImage('foo.gif')).toBe(true);
  expect(isImage('foo.jpg')).toBe(true);
  expect(isImage('foo.bar.jpg')).toBe(true);
  expect(isImage('http://foo.com/path/to/some.jpg')).toBe(true);
});

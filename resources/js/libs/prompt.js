export default function useBrackets(name) {
  if (name.indexOf(' ') > -1 || name.indexOf('.') > -1) {
    name = '{' + name + '}';
  }

  return name;
}

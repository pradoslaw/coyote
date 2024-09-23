export default function useBrackets(name: string): string {
  if (name.includes(' ') || name.includes('.')) {
    return '{' + name + '}';
  }
  return name;
}

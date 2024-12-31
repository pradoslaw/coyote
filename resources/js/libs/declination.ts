export type Inflection = [string, string, string];

export default function declination(value: number, [accusativeSingular, accusativePlural, genitivePlural]: Inflection): string {
  if (value === 1) {
    return accusativeSingular;
  }
  if (isPaucalNumber(value) && !isTeen(value)) {
    return accusativePlural;
  }
  return genitivePlural;
};

function isPaucalNumber(value: number): boolean {
  const lastDigit = value % 10;
  return lastDigit === 2 || lastDigit === 3 || lastDigit === 4;
}

function isTeen(value: number): boolean {
  const lastDigits = value % 100;
  return lastDigits > 10 && lastDigits < 20;
}

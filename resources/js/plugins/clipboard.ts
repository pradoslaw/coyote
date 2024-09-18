export function copyToClipboard(message: string): boolean {
  const input = document.createElement('input');

  input.value = message;
  input.style.border = 'none';
  input.style.outline = 'none';
  input.style.boxShadow = 'none';
  input.style.background = 'transparent';

  try {
    document.body.appendChild(input);
    input.select();
    return document.execCommand('copy');
  } finally {
    document.body.removeChild(input);
  }
}

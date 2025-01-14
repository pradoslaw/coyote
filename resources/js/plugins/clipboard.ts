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

export function copyToClipboardMultiline(message: string): boolean {
  const textarea = document.createElement('textarea');
  textarea.textContent = message;
  textarea.style.border = 'none';
  textarea.style.outline = 'none';
  textarea.style.boxShadow = 'none';
  textarea.style.background = 'transparent';
  try {
    document.body.appendChild(textarea);
    textarea.select();
    return document.execCommand('copy');
  } finally {
    document.body.removeChild(textarea);
  }
}

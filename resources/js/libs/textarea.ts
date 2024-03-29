class Textarea {
  private readonly textarea: HTMLTextAreaElement;

  constructor(textarea: HTMLTextAreaElement) {
    this.textarea = textarea;
  }

  /**
   * Get textarea selection.
   *
   * @return {string}
   */
  getSelection(): string {
    const startPos = this.textarea.selectionStart;
    const endPos = this.textarea.selectionEnd;

    return this.textarea.value.substring(startPos, endPos);
  }

  /**
   * Insert text at selection.
   *
   * @param {string} startsWith
   * @param {string} endsWith
   * @param {string} value
   */
  insertAtCaret(startsWith: string, endsWith: string, value: string): void {
    const startPos = this.textarea.selectionStart;
    const endPos = this.textarea.selectionEnd;
    const scrollTop = this.textarea.scrollTop;

    value = startsWith + value + endsWith;

    this.textarea.value = this.textarea.value.substring(0, startPos) + value + this.textarea.value.substring(endPos, this.textarea.value.length);

    this.textarea.focus();
    this.textarea.selectionStart = startPos + value.length;
    this.textarea.selectionEnd = startPos + value.length;
    this.textarea.scrollTop = scrollTop;
  }

  /**
   * Return TRUE if text is selected within textarea.
   *
   * @return {boolean}
   */
  isSelected(): boolean {
    return (this.textarea.selectionEnd - this.textarea.selectionStart) > 0;
  }

  getCaretCoordinates(): DOMRect {
    const phantom = document.createElement('div');
    const styles = window.getComputedStyle(this.textarea);

    // copy all css properties from textarea to phantom <div>
    phantom.style.cssText = Object.values(styles).reduce((css, propertyName) => `${css}${propertyName}: ${styles.getPropertyValue(propertyName)};`);

    phantom.style.position = 'absolute';
    phantom.style.overflow = 'hidden'; // no scrollbar
    phantom.style.visibility = 'hidden';
    phantom.textContent = this.textarea.value.substr(0, this.textarea.selectionStart).replace("\r\n", "\n");

    document.body.appendChild(phantom);

    const caret = document.createElement('span');

    // copy the rest of text to inline element
    caret.textContent = this.textarea.value.substring(this.textarea.selectionStart) || '.';
    phantom.appendChild(caret);

    const rect = new DOMRect(
      caret.offsetLeft + parseInt(styles.paddingLeft),
      // add padding and line-height (get position in next line)
      caret.offsetTop + parseInt(styles.paddingTop) + parseInt(styles.lineHeight),
      0,
      0
    );

    document.body.removeChild(phantom);

    return rect;
  }

  get value(): string {
    return this.textarea.value;
  }
}

export default Textarea;
export const languages = {
  'actionscript': 'ActionScript',
  'ada': 'ADA',
  'asm': 'Asm',
  'bash': 'Bash',
  'brainfuck': 'BrainFuck',
  'c': 'C',
  'cpp': 'C++',
  'csharp': 'C#',
  'clojure': 'Clojure',
  'css': 'CSS',
  'delphi': 'Delphi',
  'diff': 'Diff',
  'elixir': 'Elixir',
  'fsharp': 'F#',
  'fortan': 'Fortran',
  'groovy': 'Groovy',
  'go': 'Go',
  'graphql': 'GraphQL',
  'haskell': 'Haskell',
  'html': 'HTML',
  'ini': 'INI',
  'java': 'Java',
  'javascript': 'JavaScript',
  'julia': 'Julia',
  'kotlin': 'Kotlin',
  'latex': 'LaTeX',
  'lisp': 'Lisp',
  'lua': 'Lua',
  'matlab': 'Matlab',
  'pascal': 'Pascal',
  'perl': 'Perl',
  'php': 'PHP',
  'plsql': 'PL/SQL',
  'prolog': 'Prolog',
  'python': 'Python',
  'r': 'R',
  'ruby': 'Ruby',
  'rust': 'Rust',
  'scala': 'Scala',
  'smalltalk': 'Smalltalk',
  'sql': 'SQL',
  'twig': 'Twig',
  'visual-basic': 'Visual Basic',
  'xml': 'XML',
  'yaml': 'Yaml'
};

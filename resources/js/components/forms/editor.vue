<template>
  <div class="editor-4play" ref="view">
  </div>
</template>

<script lang="ts">
import {CodeBlockLanguages, Editor4Play, EditorState, Emojis, EmojiUrl} from "@riddled/4play";
import Vue from 'vue';
import Component from 'vue-class-component';
import {Emit, Prop, Ref, Watch} from "vue-property-decorator";

@Component
export default class VueEditor extends Vue {

  @Prop()
  readonly value!: string;
  @Prop()
  readonly placeholder!: string;
  @Prop()
  readonly autocompleteSource!: Function
  @Prop()
  readonly emojiUrl!: EmojiUrl
  @Prop()
  readonly emojis!: Emojis

  @Ref('view')
  readonly view!: HTMLElement;

  editor: Editor4Play | null = null;

  mounted() {
    this.editor = new Editor4Play(
      this.view,
      this.placeholder,
      this.value,
      {onChange: this.input, onSubmit: this.submit, onCancel: this.cancel, onStateChange: this.state},
      username => this.autocompleteSource(username),
      'Brak kolorowania',
      this.codeBlockLanguages(),
      this.emojiUrl,
      this.emojis,
      'Emotikony',
      'Użytkownicy',
      'Kolorowanie składni',
    );
  }

  @Emit()
  input(content: string) {
  }

  @Emit()
  submit(content: string) {
  }

  @Emit()
  cancel() {
  }

  @Emit()
  state(state: EditorState) {
  }

  destroy() {
    this.editor!.destroy();
  }

  @Watch('value')
  onValueChanged(newValue: string, oldValue: string) {
    if (newValue === '') {
      this.editor!.clear();
    }
  }

  insertImage(href: string, title: string) {
    this.editor!.insertImage(href, title);
  }

  insertLink(href: string, title: string) {
    this.editor!.insertLink(href, title);
  }

  makeBold() {
    this.editor!.putBold();
  }

  makeItalics() {
    this.editor!.putItalics();
  }

  makeUnderline() {
    this.editor!.putUnderline();
  }

  makeStrikeThrough() {
    this.editor!.putStrikeThrough();
  }

  insertBlockQuote(placeholder: string) {
    this.editor!.putBlockQuote(placeholder);
  }

  makeLink(placeholder: string) {
    this.editor!.putLink(placeholder);
  }

  makeImage(placeholder: string) {
    this.editor!.putImage(placeholder);
  }

  makeKeyNotation(key: string) {
    this.editor!.putKey(key);
  }

  appendBlockQuote(content: string) {
    this.editor!.appendBlockQuote(content);
  }

  appendUserMention(username: string) {
    this.editor!.appendUserMention(username);
  }

  insertListOrdered(placeholder: string) {
    this.editor!.putListOrdered(placeholder);
  }

  insertListUnordered(placeholder: string) {
    this.editor!.putListUnordered(placeholder);
  }

  insertCodeBlock() {
    this.editor!.putCodeBlock();
  }

  indentMore() {
    this.editor!.indentMore();
  }

  indentLess() {
    this.editor!.indentLess();
  }

  addTable(header: string, placeholder: string) {
    this.editor!.putTable(index => header + ' ' + (index + 1), placeholder);
  }

  insertEmoji(emojiName: string) {
    this.editor!.insertEmoji(emojiName);
  }

  focus() {
    this.editor!.focus();
  }

  codeBlockLanguages(): CodeBlockLanguages {
    return Object.fromEntries(
      languages()
        .flatMap(({title, codes}) => codes.map(code => [code, title])));

    function languages() {
      function language(title: string, codes: string[]) {
        return {title, codes};
      }

      return [
        language('Ada', ['ada']),
        language('Asembler', ['asm']),
        language('Basic', ['basic']),
        language('Plik wsadowy', ['batch', 'bat']),
        language('BrainFuck', ['brainfuck', 'bf']),
        language('Język C', ['c']),
        language('C++', ['c++', 'cpp']),
        language('C#', ['c#', 'cs']),
        language('Clojure', ['clojure', 'clj']),
        language('Format CSV', ['csv']),
        language('Delphi/Pascal', ['pascal', 'delphi']),
        language('Format Dockerfile', ['dockerfile']),
        language('Elixir', ['elixir']),
        language('Erlang', ['erlang']),
        language('F#', ['f#', 'fsharp']),
        language('Fortran', ['fortran']),
        language('Go', ['go']),
        language('Groovy', ['groovy']),
        language('GraphQL', ['graphql']),
        language('HTML', ['html']),
        language('Haskell', ['hs', 'haskell']),
        language('Format INI', ['ini']),
        language('Java', ['java']),
        language('JavaScript', ['js']),
        language('Format JSON', ['json']),
        language('Julia', ['julia']),
        language('JSX', ['jsx']),
        language('Kotlin', ['kt', 'kotlin']),
        language('Składnia LaTeX', ['latex', 'tex']),
        language('Lisp', ['lisp']),
        language('Lua', ['lua']),
        language('Markdown', ['markdown', 'md']),
        language('MatLab', ['matlab']),
        language('Perl', ['perl']),
        language('PHP', ['php']),
        language('Prolog', ['prolog']),
        language('PowerShell', ['powershell', 'ps']),
        language('Python', ['py', 'python']),
        language('Język R', ['r']),
        language('Rust', ['rs', 'rust']),
        language('Ruby', ['rb', 'ruby']),
        language('RSS', ['atom', 'rss']),
        language('Scala', ['scala']),
        language('Skrypt powłoki', ['bash', 'sh']),
        language('Arkusz stylów', ['css', 'scss', 'sass', 'less']),
        language('Język SQL', ['sql']),
        language('Szablon Twig', ['twig']),
        language('TypeScript/JSX', ['tsx']),
        language('TypeScript', ['ts']),
        language('Visual Basic', ['vb']),
        language('Format XML', ['xml', 'svg']),
        language('Format YAML', ['yaml', 'yml']),
      ];
    }

  }
}
</script>

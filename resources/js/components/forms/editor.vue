<template>
  <div class="editor-4play" ref="view">
  </div>
</template>

<script lang="ts">
import {CodeBlockLanguages, Editor4Play, EditorState} from "@riddled/4play";
import store from "../../store/index";
import {removeVueProxy} from "../../vue";

export default {
  name: 'VueEditor',
  model: {
    prop: 'modelValue',
    event: 'update:modelValue',
  },
  props: {
    modelValue: String,
    placeholder: String,
    autocompleteSource: Function,
    emojiUrl: Function,
    emojis: Object,
  },
  data() {
    return {
      editor: null,
    };
  },
  mounted() {
    this.editor = new Editor4Play(
      this.$refs.view,
      this.placeholder,
      this.modelValue,
      {onChange: this.input, onSubmit: this.submit, onCancel: this.cancel, onStateChange: this.state},
      username => this.autocompleteSource(username),
      'Brak kolorowania',
      this.codeBlockLanguages(),
      this.emojiUrl,
      this.emojis,
      'Emotikony',
      'Użytkownicy',
      'Kolorowanie składni',
      'Krótkie emotikony',
      {
        ':)': [':smile:', ':twinkle:', ':slight_smile:', ':upside_down_face:', ':innocent:'],
        ';)': [':wink:', ':smile_tear:'],
        ':D': [':laugh:', ':sweat_smile:', ':lol:', ':joy:', ':rotfl:'],
        ':>': [':happy:', ':hugging_face:'],
        ':P': [':tongue:', ':tongue_closed_eyes:'],
        ';P': [':tongue_wink:', ':zany_face:'],
        ':*': [':kissing:', ':kissing_heart:', ':kissing_closed_eyes:', ':kissing_smiling:'],
        ';*': [':kissing_heart:'],
        ':(': [':frown:', ':unhappy:', ':sad:', ':upset:', ':worried:', ':nervous:', ':scared:', ':cold_sweat:'],
        ';(': [':cry:', ':sob:'],
        ':c': [':disappointed:'],
        ':o': [':surprised:', ':open_mouth:', ':peeking_eye:', ':astonished:', ':head_explode:', ':scream:', ':dizzy:'],
        ':x': [':zipper_mouth:', ':no_mouth:', ':shush:'],
        ':<': [':weary:', ':tired:', ':angry:', ':rage:', ':symbols_on_mouth:'],
        ':/': [':confused:', ':diagonal_mouth:', ':grimacing:'],
        ':|': [':neutral:', ':raised_eyebrow:', ':monocle:', ':expressionless:', ':dotted_face:'],
        ':]': [':smirk:', ':sunglasses:', ':yum:'],
        '<3': [
          ':heart:',
          ':heart_smile:', ':heart_eyes:', ':heartpulse:', ':heart_on_fire:', ':heart_hands:',
          ':black_heart:', ':brown_heart:', ':orange_heart:', ':green_heart:', ':blue_heart:', ':purple_heart:', ':white_heart:',
        ],
        '^^': [':relaxed:', ':blush:'],
        ':?': [':thinking:', ':yawn:'],
      },
      this.dark,
    );
  },
  methods: {
    input(content: string) {
      this.$emit('update:modelValue', content);
    },
    submit(content: string) {
      this.$emit('submit', content);
    },
    cancel() {
      this.$emit('cancel');
    },
    state(state: EditorState) {
      this.$emit('state', state);
    },
    destroy() {
      removeVueProxy(this.editor).destroy();
    },
    insertImage(href: string, title: string) {
      removeVueProxy(this.editor).insertImage(href, title);
    },
    insertLink(href: string, title: string) {
      removeVueProxy(this.editor).insertLink(href, title);
    },
    makeBold() {
      removeVueProxy(this.editor).putBold();
    },
    makeItalics() {
      removeVueProxy(this.editor).putItalics();
    },
    makeUnderline() {
      removeVueProxy(this.editor).putUnderline();
    },
    makeStrikeThrough() {
      removeVueProxy(this.editor).putStrikeThrough();
    },
    insertBlockQuote(placeholder: string) {
      removeVueProxy(this.editor).putBlockQuote(placeholder);
    },
    makeLink(placeholder: string) {
      removeVueProxy(this.editor).putLink(placeholder);
    },
    makeImage(placeholder: string) {
      removeVueProxy(this.editor).putImage(placeholder);
    },
    makeKeyNotation(key: string) {
      removeVueProxy(this.editor).putKey(key);
    },
    appendBlockQuote(content: string) {
      removeVueProxy(this.editor).appendBlockQuote(content);
    },
    appendUserMention(username: string) {
      removeVueProxy(this.editor).appendUserMention(username);
    },
    insertListOrdered(placeholder: string) {
      removeVueProxy(this.editor).putListOrdered(placeholder);
    },
    insertListUnordered(placeholder: string) {
      removeVueProxy(this.editor).putListUnordered(placeholder);
    },
    insertCodeBlock() {
      removeVueProxy(this.editor).putCodeBlock();
    },
    indentMore() {
      removeVueProxy(this.editor).indentMore();
    },
    indentLess() {
      removeVueProxy(this.editor).indentLess();
    },
    addTable(header: string, placeholder: string) {
      removeVueProxy(this.editor).putTable(index => header + ' ' + (index + 1), placeholder);
    },
    insertEmoji(emojiName: string) {
      removeVueProxy(this.editor).insertEmoji(emojiName);
    },
    focus() {
      removeVueProxy(this.editor).focus();
    },
    codeBlockLanguages(): CodeBlockLanguages {
      return Object.fromEntries(
        languages()
          .flatMap(({title, codes}) => codes.map(code => [code, title])),
      );

      function languages() {
        function language(title: string, codes: string[]) {
          return {title, codes};
        }

        return [
          language('Ada', ['ada']),
          language('Asembler', ['asm']),
          language('Basic', ['basic']),
          language('Skrypt powłoki', ['bash', 'sh']),
          language('Plik wsadowy', ['batch', 'bat']),
          language('BrainFuck', ['brainfuck', 'bf']),
          language('Język C', ['c']),
          language('C++', ['c++', 'cpp']),
          language('C#', ['c#', 'cs']),
          language('Clojure', ['clojure', 'clj']),
          language('Arkusz stylów', ['css', 'scss', 'sass', 'less']),
          language('Format CSV', ['csv']),
          language('Delphi/Pascal', ['pascal', 'delphi']),
          language('Dockerfile', ['dockerfile']),
          language('Elixir', ['elixir']),
          language('Erlang', ['erlang']),
          language('F#', ['f#', 'fsharp']),
          language('Fortran', ['fortran']),
          language('Go', ['go']),
          language('Groovy', ['groovy']),
          language('GraphQL', ['graphql']),
          language('HTML', ['html']),
          language('Haskell', ['hs', 'haskell']),
          language('INI', ['ini']),
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
          language('RSS', ['rss', 'atom']),
          language('Scala', ['scala']),
          language('Język SQL', ['sql']),
          language('Twig', ['twig']),
          language('TypeScript/JSX', ['tsx']),
          language('TypeScript', ['ts']),
          language('Visual Basic', ['vb']),
          language('XML', ['xml', 'svg']),
          language('Format YAML', ['yaml', 'yml']),
        ];
      }
    },
  },
  watch: {
    modelValue(newValue: string, oldValue: string) {
      if (newValue === '') {
        removeVueProxy(this.editor).clear();
      }
    },
  },
  created() {
    store.subscribe((mutation, state) => {
      if (mutation.type === 'theme/CHANGE_THEME') {
        removeVueProxy(this.editor).changeTheme(state.theme.darkTheme);
      }
    });
  },
  computed: {
    dark() {
      return store.state.theme.darkTheme;
    },
  },
};
</script>

<template>
  <div class="editor-4play" ref="view">
  </div>
</template>

<script lang="ts">
import Vue from 'vue';
import Component from 'vue-class-component';
import {Emit, Prop, Ref, Watch} from "vue-property-decorator";
import {Editor4Play, EditorState} from "@riddled/4play/index.js";

@Component
export default class VueEditor extends Vue {

  @Prop()
  readonly value!: string;
  @Prop()
  readonly placeholder!: string;
  @Prop()
  readonly autocompleteSource!: Function

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
      'Brak oznacznika'
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

  insertImage(href, title) {
    this.editor!.insertImage(href, title);
  }

  insertLink(href, title) {
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

  insertBlockQuote(placeholder) {
    this.editor!.putBlockQuote(placeholder);
  }

  makeLink(placeholder) {
    this.editor!.putLink(placeholder);
  }

  makeImage(placeholder) {
    this.editor!.putImage(placeholder);
  }

  makeKeyNotation(key) {
    this.editor!.putKey(key);
  }

  appendBlockQuote(content) {
    this.editor!.appendBlockQuote(content);
  }

  appendUserMention(username) {
    this.editor!.appendUserMention(username);
  }

  insertListOrdered(placeholder) {
    this.editor!.putListOrdered(placeholder);
  }

  insertListUnordered(placeholder) {
    this.editor!.putListUnordered(placeholder);
  }

  insertCodeBlock() {
    this.editor!.putCodeBlock();
  }

  addTable(header, placeholder) {
    this.editor!.putTable(index => header + ' ' + (index + 1), placeholder);
  }

  focus() {
    this.editor!.focus();
  }
}
</script>

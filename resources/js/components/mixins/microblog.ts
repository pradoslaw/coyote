import {Vue, Component, Prop, Emit, Ref} from "vue-property-decorator";
import store from "../../store";
import {Microblog} from "../../types/models";
import VueModal from "../modal.vue";
import VueForm from "../microblog/form.vue";

@Component
export class MicroblogMixin extends Vue {
  protected isEditing = false;
  protected isWrapped = false;

  @Prop(Object)
  protected microblog!: Microblog;

  @Ref()
  protected readonly confirm!: VueModal;

  @Ref()
  protected readonly form!: VueForm;

  protected edit() {
    this.isEditing = !this.isEditing;

    if (this.isEditing) {
      // @ts-ignore
      this.$nextTick(() => this.form.textarea.focus());
      this.isWrapped = false;
    }
  }

  protected delete(action: string, confirm: boolean, microblog: Microblog) {
    if (confirm) {
      // @ts-ignore
      this.confirm.open();
    } else {
      store.dispatch('microblogs/delete', microblog);

      // @ts-ignore
      this.confirm.close()
    }
  }
}

@Component
export class MicroblogFormMixin extends Vue {
  isProcessing = false;

  @Prop({default() {
    return {
      media: []
    }
  }})
  microblog!: Microblog;

  @Ref()
  readonly textarea!: HTMLTextAreaElement;

  @Emit()
  cancel() {
    //
  }

  protected save(action) {
    this.isProcessing = true;

    store.dispatch(action, this.microblog)
      .then(() => {
        this.$emit('save');

        if (!this.microblog.id) {
          this.microblog.text = '';
          this.microblog.media = [];
        }
      })
      .finally(() => this.isProcessing = false);
  }

}

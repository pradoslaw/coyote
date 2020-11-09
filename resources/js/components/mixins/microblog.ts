import Prism from 'prismjs';
import { Vue, Component, Prop, Emit, Ref } from "vue-property-decorator";
import store from "../../store";
import { Microblog } from "../../types/models";
import VueModal from "../modal.vue";
import VueForm from "../microblog/form.vue";

@Component
export class MicroblogMixin extends Vue {
  protected isWrapped = false;

  @Prop(Object)
  protected microblog!: Microblog;

  @Ref()
  protected readonly confirm!: VueModal;

  @Ref()
  protected readonly form!: VueForm;

  protected edit(microblog: Microblog) {
    store.commit('microblogs/edit', microblog);

    if (microblog.is_editing) {
      this.$nextTick(() => this.form.textarea.focus());
      this.isWrapped = false;
    }
  }

  protected delete(action: string, confirm: boolean, microblog: Microblog) {
    if (confirm) {
      // @ts-ignore
      this.confirm.open();
    } else {
      store.dispatch(action, microblog);

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
      .then(result => {
        this.$emit('save', result.data);

        if (!this.microblog.id) {
          this.microblog.text = '';
          this.microblog.media = [];
        }

        // highlight once again after saving
        this.$nextTick(() => Prism.highlightAll());
      })
      .finally(() => this.isProcessing = false);
  }
}

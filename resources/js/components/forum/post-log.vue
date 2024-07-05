<template>
  <section class="card card-post">
    <div class="card-header">
      <div class="row d-none d-lg-flex">
        <div class="col-2">
          <h5 class="mb-0 post-author">
            <vue-username v-if="log.user" :user="log.user"></vue-username>
            <span v-else>{{ log.user_name }}</span>
          </h5>
        </div>

        <div class="col-10">
          <i class="far fa-file"></i>
          <a :href="`#id${log.id}`">
            {{ log.title }}
          </a>
        </div>
      </div>
    </div>

    <div class="card-body">
      <div class="row">
        <div class="d-none d-lg-block col-lg-2">
          <ul class="post-stats list-unstyled">
            <li>
              <strong>Data:</strong>
              <small>
                <vue-timeago :datetime="log.created_at"/>
              </small>
            </li>
            <li>
              <strong>IP:</strong>
              <small>{{ log.ip }}</small>
            </li>
            <li class="text-truncate">
              <strong>Przeglądarka:</strong>
              <small :title="log.browser">{{ log.browser }}</small>
            </li>
          </ul>
        </div>

        <div class="col-12 col-lg-10 diff">
          <div v-if="isLoaded" class="post-content" v-html="diffStr" style="white-space: pre-wrap"/>
        </div>
      </div>
    </div>

    <div class="card-footer">
      <div class="row">
        <div class="d-none d-lg-block col-lg-2"></div>
        <div class="col-12 d-flex col-lg-10">
          <a v-if="isRollbackEnabled" @click="rollback" title="Cofnij do tej wersji" class="btn btn-sm btn-rollback">
            <i class="fas fa-arrow-rotate-left"/>
            Cofnij do tej wersji
          </a>
          <a v-else class="btn btn-sm" :href="this.topicLink">
            <i class="fas fa-eye"/>
            Pokaż aktualną wersję
          </a>
        </div>
      </div>
    </div>
  </section>
</template>

<script lang="ts">
import Vue from 'vue';
import {Prop} from "vue-property-decorator";
import {PostLog} from "@/types/models";
import Component from "vue-class-component";
import VueUserName from "@/components/user-name.vue";
import VueModal from "@/components/delete-modal.vue";

@Component({
  components: {'vue-username': VueUserName, 'vue-modal': VueModal}
})
export default class VuePostLog extends Vue {
  @Prop()
  readonly log!: PostLog;
  @Prop()
  readonly topicLink!: string;

  @Prop()
  readonly isRollbackEnabled!: boolean;

  @Prop({default: null})
  readonly oldStr!: string | null;

  isLoaded = false;
  private diff: any;

  created() {
    return import('diff').then((diff) => {
      this.diff = diff;
      this.isLoaded = true;
    });
  }

  async rollback() {
    await this.$confirm({
      message: 'Treść posta zostanie zastąpiona. Czy chcesz kontynuować?',
      title: 'Potwierdź operację',
      okLabel: 'Tak, przywróć'
    });

    const {data} = await this.$store.dispatch('posts/rollback', this.log);

    window.location.href = data.url;
  }

  get diffStr() {
    if (!this.oldStr) {
      return this.log.text;
    }
    const diff = this.diff.diffWords(
      encodeHtml(this.oldStr),
      encodeHtml(this.log.text));

    return diff.reduce((acc: string, part): string => {
      if (part.added) {
        return acc + `<ins class="text-primary">${part.value}</ins>`;
      }
      if (part.removed) {
        return acc + `<del class="text-danger">${part.value}</del>`;
      }
      return acc + part.value;
    }, '');
  }
}

const tmp = document.createElement("div");

function encodeHtml(text: string): string {
  tmp.innerText = text;
  return tmp.innerHTML;
}
</script>

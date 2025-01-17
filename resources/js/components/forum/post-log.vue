<template>
  <section class="card card-post">
    <div class="card-header">
      <div class="row d-none d-lg-flex">
        <div class="col-2">
          <h5 class="mb-0 post-author ms-2">
            <vue-username v-if="log.user" :user="log.user"/>
            <span v-else>{{ log.user_name }}</span>
          </h5>
        </div>
        <div class="col-10">
          <vue-icon name="postHistoryVersion"/>
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
        <div class="d-none d-lg-block col-lg-2"/>
        <div class="col-12 d-flex col-lg-10">
          <a v-if="isRollbackEnabled" @click="rollback" title="Cofnij do tej wersji" class="btn btn-sm btn-rollback">
            <vue-icon name="postHistoryVersionRestore"/>
            Cofnij do tej wersji
          </a>
          <a v-else class="btn btn-sm" :href="this.topicLink">
            <vue-icon name="postHistoryVersionShow"/>
            Pokaż aktualną wersję
          </a>
        </div>
      </div>
    </div>
  </section>
</template>

<script lang="ts">
import {confirmModal} from "../../plugins/modals";
import {VueTimeAgo} from "../../plugins/timeago.js";
import store from "../../store/index";
import VueModal from "../delete-modal.vue";
import VueIcon from "../icon";
import VueUserName from "../user-name.vue";

export default {
  name: 'VuePostLog',
  components: {
    VueIcon,
    'vue-username': VueUserName,
    'vue-modal': VueModal,
    'vue-timeago': VueTimeAgo,
  },
  props: {
    log: {
      type: Object,
      required: true,
    },
    topicLink: {
      type: String,
      required: true,
    },
    isRollbackEnabled: {
      type: Boolean,
      required: true,
    },
    oldStr: {
      type: String,
      default: null,
    },
  },
  data() {
    return {
      isLoaded: false,
      diff: null as any,
    };
  },
  created() {
    import('diff').then((diff) => {
      this.diff = diff;
      this.isLoaded = true;
    });
  },
  methods: {
    async rollback() {
      await confirmModal({
        message: 'Treść posta zostanie zastąpiona. Czy chcesz kontynuować?',
        title: 'Potwierdź operację',
        okLabel: 'Tak, przywróć',
      });
      const {data} = await store.dispatch('posts/rollback', this.log);
      window.location.href = data.url;
    },
  },
  computed: {
    diffStr() {
      if (!this.oldStr) {
        return this.log.text;
      }
      const diff = this.diff.diffWords(
        encodeHtml(this.oldStr),
        encodeHtml(this.log.text),
      );

      return diff.reduce((acc: string, part): string => {
        if (part.added) {
          return acc + `<ins class="text-primary">${part.value}</ins>`;
        }
        if (part.removed) {
          return acc + `<del class="text-danger">${part.value}</del>`;
        }
        return acc + part.value;
      }, '');
    },
  },
};

const tmp = document.createElement("div");

function encodeHtml(text: string): string {
  tmp.innerText = text;
  return tmp.innerHTML;
}
</script>

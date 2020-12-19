import Vue from "vue";
import VuePost from "@/js/components/forum/post.vue";
import VueForm from "@/js/components/forum/form.vue";
import VuePoll from "@/js/components/forum/poll.vue";
import VuePagination from "../../components/pagination.vue";
import store from "@/js/store";
import { Subscriber, PostSaved, PostCommentSaved } from "@/js/libs/live";
import useBrackets from "@/js/libs/prompt";
import { mapGetters, mapState } from "vuex";
import { Post } from "@/js/types/models.ts";

export default Vue.extend({
  delimiters: ['${', '}'],
  components: {
    'vue-post': VuePost,
    'vue-form': VueForm,
    'vue-poll': VuePoll,
    'vue-pagination': VuePagination
  },
  store,
  data: () => ({
    showStickyCheckbox: window.showStickyCheckbox,
    undefinedPost: { text: '', html: '', assets: [] },
    reasons: window.reasons
  }),
  created() {
    store.commit('posts/init', window.pagination);
    store.commit('topics/init', [ window.topic ]);
    store.commit('forums/init', [ window.forum ]);
    store.commit('poll/init', window.poll);
    store.commit('flags/init', window.flags);
  },
  mounted() {
    document.getElementById('js-skeleton')?.remove();

    this.liveUpdate();

    const hints = ['hint-subject', 'hint-text', 'hint-tags', 'hint-user_name'];

    [
      document.querySelector('#js-submit-form input[name="subject"]'),
      document.querySelector('#js-submit-form textarea[name="text"]'),
      document.querySelector('#js-submit-form input[name="tags"]')
    ].forEach(el => {
      if (!el) {
        return;
      }

      el.addEventListener('focus', () => {
        const name = el.getAttribute('name');
        const hint = document.getElementById(`hint-${name}`);

        if (!hint) {
          return; // hint tooltips might not be present on the website
        }

        hints.forEach(hint => document.getElementById(hint)!.style.display = 'none');
        hint.style.display = 'block';
      });
    });
  },
  methods: {
    liveUpdate() {
      const subscriber = new Subscriber(`topic:${window.topic.id}`);

      subscriber.subscribe('CommentSaved', new PostCommentSaved())
      subscriber.subscribe('PostSaved', new PostSaved())
    },

    redirectToTopic(post: Post) {
      this.resetPost(post);

      window.location.href = post.url;
    },

    changePage(page: number) {
      window.location.href = `?page=${page}`;
    },

    reply(post: Post, scrollIntoForm = true) {
      if (scrollIntoForm) {
        document.getElementById('js-submit-form')!.scrollIntoView();

        if (!this.undefinedPost.text!.includes(`[${post.user!.name}`)) {
          this.undefinedPost.text += `@${useBrackets(post.user!.name)}: `;
        }

        // @ts-ignore
        this.$refs['js-submit-form'].$refs['textarea']!.focus();
      }
      else {
        let text = `> ##### [${post.user ? post.user.name : post.user_name} napisał(a)](/Forum/${post.id}):`
        text += "\n" + post.text.replace(/\n/g, "\n> ") + "\n\n"

        this.undefinedPost.text += (this.undefinedPost.text.length ? "\n" : '') + text;

        this.$notify({type: 'success', text: 'Cytat został skopiowany do formularza.'});
      }
    },

    resetPost(post: Post) {
      this.undefinedPost = { text: '', html: '', assets: [] };

      window.location.hash = `id${post.id}`;
    }
  },
  computed: {
    ...mapGetters('posts', ['posts', 'totalPages', 'currentPage']),
    ...mapGetters('topics', ['topic']),
    ...mapGetters('user', ['isAuthorized']),
    ...mapState('poll', ['poll'])
  }
});

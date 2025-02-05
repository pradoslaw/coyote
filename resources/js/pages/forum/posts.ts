import axios from "axios";
import Prism from "prismjs";
import {mapGetters, mapState} from "vuex";

import VueForm from "../../components/forum/form.vue";
import VuePoll from "../../components/forum/poll.vue";
import VuePostWrapper from "../../components/forum/post-wrapper.vue";
import VuePagination from "../../components/pagination.vue";
import {PostCommentSaved, PostSaved, PostVoted, Subscriber} from "../../libs/live";
import store from "../../store/index";
import {notify} from "../../toast";
import {TreeOrderBy} from "../../treeTopic/treeOrderBy";
import {Post} from "../../types/models";
import {nextTick} from "../../vue";

export default {
  name: 'Posts',
  delimiters: ['${', '}'],
  components: {
    'vue-post-wrapper': VuePostWrapper,
    'vue-form': VueForm,
    'vue-poll': VuePoll,
    'vue-pagination': VuePagination,
  },
  store,
  data() {
    return {
      showStickyCheckbox: window.showStickyCheckbox,
      showDiscussModeSelect: window.showDiscussModeSelect,
      undefinedPost: {text: '', html: '', assets: []},
      reasons: window.reasons,
      popularTags: window.popularTags,
      postFormHidden: false,
      editorRevealed: false,
      draftPost: null,
    };
  },
  created() {
    store.commit('posts/init', window.pagination);
    store.commit('topics/init', [window.topic]);
    if (store.getters['topics/is_mode_tree']) {
      const isMobile = (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent));
      if (isMobile) {
        store.dispatch('posts/foldChildrenOfLevel', 2);
      } else if (!store.getters['topics/treeTopicSelectedSubtree']) {
        store.dispatch('posts/foldChildrenOfLevel', 2);
      } else {
      }
    }
    store.commit('topics/setReasons', this.reasons);
    store.commit('forums/init', [window.forum]);
    store.commit('poll/init', window.poll);
    store.commit('flags/init', window.flags);
    this.resetForm();
  },
  mounted() {
    document.getElementById('js-skeleton')?.remove();

    this.liveUpdate();

    const hints = ['hint-title', 'hint-text', 'hint-tags', 'hint-user_name'];

    [
      document.querySelector('#js-submit-form input[name="title"]'),
      document.querySelector('#js-submit-form textarea[name="text"]'),
      document.querySelector('#js-submit-form input[name="tags"]'),
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
    if (window['draftPost']) {
      if (window['draftPost'].topicId === store.getters["topics/topic"].id) {
        this.includeDraftPost(window['draftPost'].text);
      }
    }
  },
  methods: {
    revealEditor(): void {
      this.$data.editorRevealed = true;
      nextTick(() => {
        this.markdownRef.focus();
      });
    },
    liveUpdate() {
      const subscriber = new Subscriber(`topic:${window.topic.id}`);

      subscriber.subscribe('CommentSaved', new PostCommentSaved());
      subscriber.subscribe('PostSaved', new PostSaved());
      subscriber.subscribe('PostVoted', new PostVoted());
    },
    changePage(page: number) {
      window.location.href = `?page=${page}`;
    },
    reply(post: Post, scrollIntoForm = true) {
      const username = post.user ? post.user.name : post.user_name!;
      if (scrollIntoForm) {
        this.markdownRef.appendUserMention(username);
        document.getElementById('js-submit-form')!.scrollIntoView();
      } else {
        this.markdownRef.appendBlockQuote(username, post.id, post.text);
        notify({type: 'success', text: 'Cytat został dodany do formularza.'});
      }
    },
    savedForm(post: Post): void {
      this.undefinedPost = {text: '', html: '', assets: []};
      window.location.hash = `id${post.id}`;
      this.resetForm();
    },
    guestFormSaved(text: string): void {
      this.undefinedPost = {text: '', html: '', assets: []};
      this.resetForm();
      this.addDraftPost({text, topicId: store.getters["topics/topic"].id});
    },
    redirectToTopic(post: Post): void {
      this.undefinedPost = {text: '', html: '', assets: []};
      window.location.href = post.url;
    },
    resetForm(): void {
      this.$data.postFormHidden = store.getters['topics/is_mode_tree'];
    },
    changeTreeTopicOrder(event: Event): void {
      const ordering: TreeOrderBy = event.target!.value;
      store.commit('topics/treeTopicOrder', ordering);
    },
    addDraftPost(draft): void {
      axios.post('/Forum/Draft', {markdownText: draft.text, topicId: draft.topicId});
      this.includeDraftPost(draft.text);
    },
    includeDraftPost(markdownText: string): void {
      const now = new Date().toISOString();
      renderMarkdown(markdownText).then(html => {
        this.$data.draftPost = {
          assets: [],
          comments: [],
          permissions: {},
          created_at: now,
          score: 1,
          html,
          text: markdownText,
          user: {
            initials: '4p',
            name: 'Your account',
            created_at: now,
          },
        };
        nextTick(() => Prism.highlightAll());
      });
    },
  },
  computed: {
    markdownRef(): VueMarkdown {
      return this.$refs['js-submit-form'].$refs['markdown']!;
    },
    ...mapGetters('posts', ['posts', 'linearTopicPosts', 'treeTopicPostsFirst', 'treeTopicPostsRemaining', 'totalPages', 'currentPage']),
    ...mapGetters('topics', ['topic', 'is_mode_tree', 'is_mode_linear', 'treeTopicOrder']),
    ...mapGetters('user', ['isAuthorized']),
    ...mapState('poll', ['poll']),
  },
};

async function renderMarkdown(markdown: string): Promise<string> {
  const response = await axios.post<any>('/Forum/Preview', {text: markdown});
  return response.data as string;
}

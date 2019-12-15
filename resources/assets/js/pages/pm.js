import 'jquery-color-animation/jquery.animate-colors';
import axios from 'axios';
import Config from '../libs/config';
import store from '../store';
import Vue from 'vue';
import PerfectScrollbar from '../components/perfect-scrollbar';
import VuePm from '../components/pm/message.vue';
import VueTextareaAutosize from 'vue-textarea-autosize';
import VuePrompt from '../components/prompt.vue';
import VueButton from '../components/forms/button.vue';

Vue.use(VueTextareaAutosize);

new Vue({
  el: '#app',
  delimiters: ['${', '}'],
  components: {
    'perfect-scrollbar': PerfectScrollbar,
    'vue-pm': VuePm,
    'vue-prompt': VuePrompt,
    'vue-button': VueButton
  },
  data() {
    return {
      recipient: window.data.recipient,
      text: '',
      isProcessing: false,
      errorMessage: null
    };
  },
  store,
  created() {
    // fill vuex with data passed from controller to view
    store.commit('messages/init', window.data.messages);
  },
  mounted() {
    const overview = document.getElementById('overview');

    document.getElementById('wrap').scrollTop = overview.clientHeight;
  },
  methods: {
    sendMessage() {
      this.isProcessing = true;

      axios.post('/User/Pm/Submit', {recipient: this.recipient, text: this.text})
        .then(result => {
          store.commit('messages/add', result.data);

          this.error = null;
        })
        .catch(err => {
          let errors = err.response.data.errors;

          this.errorMessage = errors[Object.keys(errors)[0]][0];
        })
        .finally(() => {
          this.isProcessing = false;
        });
    }
  },
  computed: {
    messages() {
      return store.state.messages.messages;
    }
  }
});

// $(function() {
//     $('textarea[name="text"]').each(function() {
//         $(this).wikiEditor().prompt().fastSubmit().autogrow().pasteImage();
//     });
//
//     $('#wrap').each(function() {
//         const container = document.getElementById('wrap');
//         new PerfectScrollbar(container);
//
//         let overview = $('#overview');
//         let pending = false;
//
//         $(this).scrollTop(overview.outerHeight());
//
//         container.addEventListener('ps-y-reach-start', () => {
//             if (pending === true) {
//                 return;
//             }
//
//             pending = true;
//             $.get(Config.get('infinity_url'), {offset: $('.media', overview).length}, html => {
//                 overview.prepend(html);
//
//                 // jezeli nie ma wiecej wiadomosci, to ajax nie bedzie kolejny raz wyslany
//                 if ($.trim(html) === '') {
//                     $(this).off('ps-y-reach-start');
//                 }
//
//                 pending = false;
//             });
//         });
//     })
//     .on('mouseenter', '.unread', (e) => {
//         $(e.currentTarget).off('mouseenter');
//         $(e.currentTarget).animate({backgroundColor: '#fff'});
//     });
//
//     $('#recipient').each(function() {
//         $(this).autocomplete({
//             url: $(this).data('prompt-url')
//         });
//     });
//
//     $('.btn-delete-pm').click(function() {
//         Dialog
//             .confirm({
//                 message: $(this).data('confirm'),
//                 form: {
//                     attr: {
//                         action: $(this).attr('href'),
//                         method: 'post'
//                     },
//                     csrfToken: Config.csrfToken()
//                 }}
//             )
//             .show();
//
//         return false;
//     });
//
//     $('#box-pm a[data-toggle="tab"]').click(function(e) {
//         if ($(e.target).attr('aria-controls') == 'preview') {
//             $('#preview').html('<i class="fa fa-spinner fa-spin fa-2x"></i>');
//
//             $.post(Config.get('preview_url'), {'text': $('textarea[name="text"]').val()}, html => {
//                 $('#preview').html(html);
//
//                 Prism.highlightAll();
//             });
//         }
//     });
// });

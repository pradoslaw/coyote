<template>
  <ul class="metadata list-inline">
    <li class="list-inline-item">
      <i class="fa fa-user-large"></i>
      <vue-user-name :user="guide.user"/>
    </li>

    <li class="list-inline-item">
      <i class="far fa-calendar-days"></i>
      <vue-timeago :datetime="guide.created_at"></vue-timeago>
    </li>

    <li class="list-inline-item">
      <i class="far fa-comments"></i>
      {{ guide.comments_count }}
      {{ declination(guide.comments_count, ['komentarz', 'komentarze', 'komentarzy']) }}
    </li>

    <li class="list-inline-item">
      <i class="far fa-eye"></i>
      {{ guide.views }}
      {{ declination(guide.views, ['wyświetlenie', 'wyświetlenia', 'wyświetleń']) }}
    </li>
  </ul>
</template>

<script lang="ts">
import VueUserName from '@/components/user-name.vue';
import Vue from 'vue';
import {default as mixins} from '../mixins/user.js';

export default Vue.extend({
  name: 'VueMetadata',
  mixins: [mixins],
  components: {
    'vue-user-name': VueUserName,
  },
  props: {
    guide: {
      type: Object,
      required: true,
    },
  },
});
</script>

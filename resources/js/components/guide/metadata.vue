<template>
  <ul class="metadata list-inline">
    <li class="list-inline-item">
      <i class="fa fa-user-large"></i> <vue-user-name :user="guide.user" />
    </li>

    <li class="list-inline-item">
      <i class="far fa-calendar-days"></i> <vue-timeago :datetime="guide.created_at"></vue-timeago>
    </li>

    <li class="list-inline-item">
      <i class="far fa-comments"></i> {{ guide.comments_count }} {{ guide.comments_count | declination(['komentarz', 'komentarze', 'komentarzy']) }}
    </li>

    <li class="list-inline-item">
      <i class="far fa-eye"></i>

      {{ guide.views }} {{ guide.views | declination(['wyświetlenie', 'wyświetlenia', 'wyświetleń']) }}
    </li>
  </ul>
</template>

<script lang="ts">
  import Vue from 'vue';
  import Component from "vue-class-component";
  import VueUserName from '@/components/user-name.vue';
  import { Prop } from "vue-property-decorator";
  import { Guide } from '@/types/models';
  import { default as mixins } from '../mixins/user';

  @Component({
    mixins: [ mixins ],
    components: {
      'vue-user-name': VueUserName
    }
  })
  export default class VueMetadata extends Vue {
    @Prop()
    readonly guide!: Guide;
  }
</script>

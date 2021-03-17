<template>
  <div class="card card-default">
    <div class="card-body">
      <div class="qa-title">
        <h1><a :href="`/Guide/${guide.id}-${guide.slug}`">{{ guide.title }}</a></h1>
      </div>

      <div v-html="guide.excerpt_html" class="mt-2"></div>

      <div class="mt-2 position-relative">
        <div v-html="guide.html" :class="{'blur': !isShowing}" style="font-size: 14px"></div>

        <button v-if="!isShowing" @click="isShowing = true" class="position-absolute btn btn-primary" style="left: 50%; top: 50%; transform: translate(-50%, -50%);">Zobacz odpowiedź</button>
      </div>

      <ul class="tag-clouds tag-clouds-skills mt-3 ">
        <li>
          <a href="https://4programmers.net/Praca/Technologia/java" title="Znajdź oferty zawierające Java"><img style="width: 15px" alt="java" src="https://4programmers.net/uploads/logo/59/59f9f808bc606.png">
            Java</a>
        </li>
        <li>
          <a href="https://4programmers.net/Praca/Technologia/javascript" title="Znajdź oferty zawierające Javascript"><img style="width: 15px" alt="javascript" src="https://4programmers.net/uploads/logo/59/59f9f81f8f897.png">
            Javascript </a>
        </li>
      </ul>

      <div class="mt-3 pt-3 qa-options">

        <ul class="list-inline mb-2">
          <li class="list-inline-item">
            <a href="#">
              <i class="fa text-primary fa-fw fa-thumbs-up"></i>
              {{ guide.votes }} {{ guide.votes | declination(['głos', 'głosy', 'głosów']) }}
            </a>
          </li>

          <li class="list-inline-item">
            <a href="#">
              <i class="far fa-fw fa-star"></i>

              10 obserwatorów
            </a>
          </li>
        </ul>
      </div>

    </div>
  </div>
</template>

<script lang="ts">
  import Vue from 'vue';
  import Component from "vue-class-component";
  import { Prop } from "vue-property-decorator";
  import { Guide } from '@/types/models';
  import { default as mixins } from '../mixins/user';
  import { mapState } from 'vuex';

  @Component({
    mixins: [ mixins ],
    computed: {
      ...mapState('guides', ['guide'])
    }
  })
  export default class VuePost extends Vue {
    private isShowing = false;
  }
</script>

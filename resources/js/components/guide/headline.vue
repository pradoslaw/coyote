<template>
  <div class="card card-default">
    <div class="card-body">
      <h4 class="m-0"><a :href="guide.url">{{ guide.title }}</a></h4>

      <div class="row no-gutters">
        <vue-tags :tags="guide.tags" class="tag-clouds-skills mt-2 mb-2"></vue-tags>

        <div class="ml-auto text-right">
          <p class="text-muted font-weight-bold mb-1"><i class="fas fa-fw fa-chart-line"></i> Mid-level</p>

          <i class="fas fa-circle text-primary" title="zaawansowany" style="font-size: 10px; margin-right: 4px;"></i><i class="fas fa-circle text-primary" title="zaawansowany" style="font-size: 10px; margin-right: 4px;"></i><i class="fas fa-circle text-muted" title="zaawansowany" style="font-size: 10px; margin-right: 4px;"></i>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
  import Vue from "vue";
  import Component from "vue-class-component";
  import VueTags from "@/components/tags.vue";
  import VueUserName from "@/components/user-name.vue";
  import {mapActions, mapState} from "vuex";
  import { default as mixins } from '../mixins/user';
  import { Prop } from "vue-property-decorator";
  import { Guide } from '@/types/models';

  @Component({
    mixins: [ mixins ],
    components: {
      'vue-tags': VueTags,
      'vue-user-name': VueUserName
    },
    methods: {
      ...mapActions('guides', ['vote', 'subscribe'])
    }
  })
  export default class VueHeadline extends Vue {
    @Prop()
    readonly guide!: Guide;
  }
</script>

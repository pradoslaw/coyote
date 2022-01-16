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

          <a :href="`#id${log.id}`">{{ log.title }}</a>
        </div>
      </div>
    </div>

    <div class="card-body">
      <div class="row">
        <div class="d-none d-lg-block col-lg-2">
          <ul class="post-stats list-unstyled">
            <li>
              <strong>Data:</strong>
              <small><vue-timeago :datetime="log.created_at"></vue-timeago></small>
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
          <div class="post-content" v-html="log.text">
          </div>

<!--          {% if log.tags %}-->
<!--          <ul class="tag-clouds">-->
<!--            {% for tag in log.tags %}-->
<!--            <li><a href="{{ route('forum.tag', [tag|url_encode]) }}">{{ tag }}</a></li>-->
<!--            {% endfor %}-->
<!--          </ul>-->
<!--          {% endif %}-->
        </div>
      </div>
    </div>

    <div class="card-footer">
      <div class="row">
        <div class="d-none d-lg-block col-lg-2"></div>
        <div class="col-12 d-flex col-lg-10">

          <a v-if="isRollbackEnabled" @click="rollback" title="Cofnij do tej wersji" class="btn btn-sm btn-rollback">
            <i class="fas fa-undo"></i>

            Cofnij do tej wersji
          </a>
        </div>
      </div>
    </div>
  </section>
</template>

<script lang="ts">
  import Vue from 'vue';
  import { Prop, Ref } from "vue-property-decorator";
  import { PostLog } from "@/types/models";
  import Component from "vue-class-component";
  import VueUserName from "@/components/user-name.vue";
  import VueModal from "@/components/delete-modal.vue";

  @Component({
    components: { 'vue-username': VueUserName, 'vue-modal': VueModal }
  })
  export default class VuePostLog extends Vue {
    @Prop()
    readonly log!: PostLog;

    @Prop()
    readonly isRollbackEnabled!: boolean;

    async rollback() {
      await this.$confirm({
        message: 'Treść postu zostanie zastąpiona. Czy chcesz kontynuować?',
        title: 'Potwierdź operację',
        okLabel: 'Tak, przywróć'
      });

      const { data } = await this.$store.dispatch('posts/rollback', this.log);

      window.location.href = data.message;
    }
  }
</script>

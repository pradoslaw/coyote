<template>
  <div class="post-review card-header d-none d-md-block py-2" :class="reviewClasses">
    <div class="row">
      <div class="col-lg-2 d-none d-lg-block"/>
      <div class="col-12 col-lg-10">
        <div class="d-flex justify-content-between">
          <template v-if="!voted">
            <span class="flex-grow-1">
              <span class="me-2">
                Czy według Ciebie ten post jest obraźliwy?
              </span>
              <span class="mx-2 post-review-option" @click="answer('positive')" v-text="'Tak'"/>
              |
              <span class="mx-2 post-review-option" @click="answer('negative')" v-text="'Nie'"/>
              |
              <span class="mx-2 post-review-option" @click="answer('indeterminate')" v-text="'Nie wiem'"/>
            </span>
            <span class="me-2">
              <vue-icon name="postReviewClose" @click="answerAndClose" style="cursor:pointer;" class="me-1"/>
            </span>
          </template>

          <template v-else>
            <span class="text-center flex-grow-1">
              Dziękujemy! Twój głos pomaga nam utrzymać bezpieczną społeczność.
            </span>
            <span class="me-2">
              <vue-icon name="postReviewClose" @click="close" style="cursor:pointer;" class="me-1"/>
            </span>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import VueIcon from "../icon";

type ReviewStyle = 'body' | 'info' | 'success' | 'warning' | 'error';
export type ReviewAnswer = 'indeterminate' | 'positive' | 'negative' | 'close';

export default {
  name: 'VuePostReview',
  components: {VueIcon},
  props: ['postId', 'open', 'reviewStyle'],
  emits: ['close', 'answer'],
  data() {
    return {voted: false};
  },
  methods: {
    close(): void {
      this.$emit('close');
    },
    answer(answer: ReviewAnswer): void {
      this.$emit('answer', answer);
      this.$data.voted = true;
    },
    answerAndClose(): void {
      this.$emit('answer', 'close');
      this.$emit('close');
    },
  },
  computed: {
    reviewClasses(): string {
      const styles: { [keyof: ReviewStyle]: string } = {
        body: '',
        success: 'alert alert-card-header-flush alert-success',
        warning: 'alert alert-card-header-flush alert-warning',
        error: 'alert alert-card-header-flush alert-danger',
        info: 'alert alert-card-header-flush alert-info',
      };
      return styles[this.$props.reviewStyle];
    },
  },
};
</script>

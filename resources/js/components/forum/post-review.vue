<template>
  <div class="d-flex justify-content-between">
    <template v-if="!voted">
      <span class="text-center flex-grow-1">
        <span class="me-2">
          Czy ten post jest obraźliwy?
        </span>
        <span
          class="mx-2 post-review-option"
          @click="review('negative')"
          v-text="'Raczej tak'"
        />
        |
        <span
          class="mx-2 post-review-option"
          @click="review('positive')"
          v-text="'Raczej nie'"
        />
        |
        <span
          class="mx-2 post-review-option"
          @click="review('neutral')"
          v-text="'Ani tak, ani nie'"
        />
      </span>
      <span class="me-2">
        <vue-icon name="postReviewClose" @click="reviewAndClose" style="cursor:pointer;"/>
      </span>
    </template>

    <template v-else>
      <span class="text-center flex-grow-1">
        Dziękujemy! Twój głos pomaga nam utrzymać bezpieczną społeczność.
      </span>
      <span class="me-2">
        <vue-icon name="postReviewClose" @click="close" style="cursor:pointer;"/>
      </span>
    </template>
  </div>
</template>

<script lang="ts">
import VueIcon from "../icon";

export type ReviewType = 'neutral' | 'positive' | 'negative' | 'close';

export default {
  name: 'VuePostReview',
  components: {VueIcon},
  props: ['postId', 'open'],
  emits: ['close', 'review'],
  data() {
    return {voted: false};
  },
  methods: {
    close(): void {
      this.$emit('close');
    },
    review(type: ReviewType): void {
      this.$emit('review', type);
      this.$data.voted = true;
    },
    reviewAndClose(): void {
      this.$emit('review', 'close');
      this.$emit('close');
    },
  },
};
</script>

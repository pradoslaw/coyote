<template>
  <div class="box-poll">
    <div class="row">
      <div class="col-12">
        <strong>{{ pollSync.title }}</strong>

        <em v-if="pollSync.max_items > 1" class="max-items">(* możesz oddać maksymalnie {{ declination(poll.max_items, ['głos', 'głosy', 'głosów']) }})</em>
      </div>
    </div>

    <div v-for="item in pollSync.items" :key="item.id" :class="{'voted': pollSync.votes.includes(item.id)}" class="row">
      <div class="col-sm-6">
        <div v-if="isVoteable" :class="{'custom-checkbox': pollSync.max_items > 1, 'custom-radio': pollSync.max_items === 1}" class="custom-control">
          <input
            :id="`item-${item.id}`"
            v-model="checkedOptions"
            :value="item.id"
            :type="pollSync.max_items > 1 ? 'checkbox' : 'radio'"
            class="custom-control-input"
          >

          <label :for="`item-${item.id}`" class="custom-control-label">
            {{ item.text }}
          </label>
        </div>

        <template v-else>{{ item.text }}</template>
      </div>

      <div class="col-sm-2">
        <div class="progress">
          <div class="progress-bar" role="progressbar" :style="`width: ${percentage(item)}%`">
            <span class="sr-only">{{ percentage(item) }}%</span>
          </div>
        </div>
      </div>

      <div class="col-sm-4">
        {{ percentage(item) }}%

        [{{ item.total }}]
      </div>
    </div>

    <div v-if="isVoteable" class="row">
      <div class="col-12">
        <vue-button :disabled="isProcessing" @click.native="vote" class="btn btn-sm btn-primary">Głosuj</vue-button>
      </div>
    </div>

    <div v-if="pollSync.expired" class="row">
      <div class="col-12">
        <p><em>Ankieta wygasła {{ pollSync.expired_at }}</em></p>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
  import Vue from 'vue';
  import { PropSync } from "vue-property-decorator";
  import Component from "vue-class-component";
  import { Poll, PollItem } from '../../types/models';
  import VueButton from "../forms/button.vue";
  import { default as mixins } from '../mixins/user';
  import store from "../../store";
  import { mapGetters } from "vuex";

  @Component({
    name: 'forum-poll',
    mixins: [ mixins ],
    store,
    components: { 'vue-button': VueButton },
    computed: mapGetters('user', ['isAuthorized']),
  })
  export default class VuePoll extends Vue {
    @PropSync('poll')
    readonly pollSync!: Poll;

    readonly isAuthorized! : boolean;
    checkedOptions: number[] = [];
    isProcessing = false;

    percentage(item: PollItem) {
      return this.totalVotes ? Math.round(100 * item.total / this.totalVotes) : 0
    }

    vote() {
      this.isProcessing = true;

      store.dispatch('poll/vote', Array.isArray(this.checkedOptions) ? this.checkedOptions : [this.checkedOptions])
        .finally(() => this.isProcessing = false);
    }

    get totalVotes() {
      return this.pollSync.items.reduce((total, curr) => total += curr.total, 0);
    }

    get isVoteable() {
      return this.pollSync.votes?.length === 0 && this.isAuthorized && !this.pollSync.expired;
    }
  }
</script>

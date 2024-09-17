<template>
  <div class="box-poll">
    <div class="row">
      <div class="col-12">
        <strong>{{ pollSync.title }}</strong>

        <em v-if="pollSync.max_items > 1" class="max-items">
          (* możesz oddać maksymalnie {{ poll.max_items }} {{ declination(poll.max_items, ['głos', 'głosy', 'głosów']) }})
        </em>
      </div>
    </div>

    <div v-for="item in pollSync.items" :key="item.id" :class="{'voted': pollSync.votes.includes(item.id)}" class="row">
      <div class="col-sm-6">
        <div v-if="isVoteable" :class="{'custom-checkbox': pollSync.max_items > 1, 'custom-radio': pollSync.max_items === 1}" class="custom-control">
          <!-- bug na IE 11 nie pozwala na użycie :type, musimy użyć type -->
          <input
            v-if="pollSync.max_items > 1"
            :id="`item-${item.id}`"
            v-model="checkedOptions"
            :value="item.id"
            type="checkbox"
            class="custom-control-input"
          >

          <input
            v-else
            :id="`item-${item.id}`"
            v-model="checkedOptions"
            :value="item.id"
            type="radio"
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
        <vue-button :disabled="isProcessing" @click="vote" class="btn btn-sm btn-primary">Głosuj</vue-button>
      </div>
    </div>

    <div v-if="pollSync.expired" class="row">
      <div class="col-12">
        <p><em>Ankieta wygasła
          <vue-timeago :datetime="pollSync.expired_at"></vue-timeago>
        </em></p>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import Vue from 'vue';
import {mapGetters} from "vuex";
import store from "../../store";
import {Poll, PollItem} from '../../types/models';
import VueButton from "../forms/button.vue";
import mixins from '../mixins/user.js';

export default Vue.extend({
  name: 'forum-poll',
  mixins: [mixins],
  store,
  components: {'vue-button': VueButton},
  props: {
    poll: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      checkedOptions: [] as number[],
      isProcessing: false,
    };
  },
  computed: {
    ...mapGetters('user', ['isAuthorized']),
    totalVotes() {
      return this.poll.items.reduce((total, curr) => total += curr.total, 0);
    },
    isVoteable() {
      return this.poll.votes?.length === 0 && this.isAuthorized && !this.poll.expired;
    },
    pollSync: {
      get(): Poll {
        return this.poll;
      },
      set(value: Poll) {
        this.$emit('update:poll', value);
      },
    },
  },
  methods: {
    percentage(item: PollItem) {
      return this.totalVotes ? Math.round(100 * item.total / this.totalVotes) : 0;
    },
    vote() {
      this.isProcessing = true;
      store.dispatch('poll/vote', Array.isArray(this.checkedOptions) ? this.checkedOptions : [this.checkedOptions])
        .finally(() => this.isProcessing = false);
    },
  },
});
</script>

<template>
  <vue-modal ref="modal">
    Tej operacji nie będzie można cofnąć.

    <template slot="title">Usunąć wpis?</template>

    <p v-if="reasons" class="mt-2">
      <vue-select name="reason_id" :options="reasons" v-model="reasonId" class="form-control-sm" placeholder="-- wybierz --"></vue-select>
    </p>

    <template slot="buttons">
      <button @click="modal.close()" type="button" class="btn btn-secondary" data-dismiss="modal">Anuluj</button>
      <button @click="deletePost" type="submit" class="btn btn-danger danger">Tak, usuń</button>
    </template>
  </vue-modal>
</template>

<script lang="ts">
import Vue from 'vue';
import VueSelect from './forms/select.vue';
import VueModal from './modal.vue';

export default Vue.extend({
  name: 'VueDeleteModal',
  components: {
    'vue-modal': VueModal,
    'vue-select': VueSelect,
  },
  props: {
    reasons: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      reasonId: null,
    };
  },
  methods: {
    open() {
      (this.$refs.modal as VueModal).open();
    },
    close() {
      (this.$refs.modal as VueModal).close();
    },
    deletePost() {
      this.$emit('delete', this.reasonId);
      this.close();
    },
  },
});
</script>

<template>
  <vue-modal ref="modal">
    <template v-slot:title>
      Usunąć wpis?
    </template>

    Tej operacji nie będzie można cofnąć.

    <p v-if="reasons" class="mt-2">
      <vue-select name="reason_id" :options="reasons" v-model="reasonId" class="form-control-sm" placeholder="-- wybierz --"/>
    </p>

    <template v-slot:buttons>
      <button @click="close" type="button" class="btn btn-secondary" data-dismiss="modal">
        Anuluj
      </button>
      <button @click="deletePost" type="submit" class="btn btn-danger danger">
        Tak, usuń
      </button>
    </template>
  </vue-modal>
</template>

<script lang="ts">
import VueSelect from './forms/select.vue';
import VueModal from './modal.vue';

export default {
  name: 'VueDeleteModal',
  components: {
    'vue-modal': VueModal,
    'vue-select': VueSelect,
  },
  props: {
    reasons: {type: Object},
  },
  data() {
    return {
      reasonId: null,
    };
  },
  methods: {
    open() {
      this.$refs.modal.open();
    },
    close() {
      this.$refs.modal.close();
    },
    deletePost() {
      this.$emit('delete', this.reasonId);
      this.close();
    },
  },
};
</script>

<template>
  <form class="alert alert-danger alert-report">
    <button @click="$refs.modal.open()" type="button" class="close" data-dismiss="alert" aria-label="Close" title="Usuń"><span aria-hidden="true">&times;</span></button>

    <vue-username :user="{id: flag.user_id, name: flag.user_name}" class="alert-link"></vue-username>
    z powodu {{ flag.name }}
    dnia <vue-timeago :datetime="flag.created_at"></vue-timeago>

    <p v-if="flag.text">{{ flag.text }}</p>

    <vue-modal ref="modal">
      <template v-slot:title>Zamknięcie raportu</template>

      <template v-slot:buttons>
        <button @click="$refs.modal.close()" type="button" class="btn btn-secondary" data-dismiss="modal">Anuluj</button>
        <button @click="close" class="btn btn-danger danger">Tak, zamknij</button>
      </template>

      <p>Czy na pewno chcesz zamknąć ten raport?</p>
    </vue-modal>
  </form>
</template>

<script lang="ts">
  import Vue from 'vue';
  import { Prop } from "vue-property-decorator";
  import Component from "vue-class-component";
  import { Flag } from "../../types/models";
  import VueUserName from "../user-name.vue";
  import VueModal from '../modal.vue';
  import axios from 'axios';

  @Component({
    name: 'flag',
    components: { 'vue-username': VueUserName, 'vue-modal': VueModal }
  })
  export default class VueFlag extends Vue {
    @Prop()
    readonly flag!: Flag;

    close() {
      axios.post(`/Flag/Delete/${this.flag.id}`);

      this.$emit('close', this.flag.id);
    }
  }
</script>

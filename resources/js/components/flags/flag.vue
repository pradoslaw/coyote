<template>
  <div class="alert alert-danger alert-report">
    <button @click="$refs.modal.open()" type="button" class="close" data-dismiss="alert" aria-label="Close" title="Usuń">
      <span aria-hidden="true">&times;</span>
    </button>

    <vue-username :user="flag.user" class="alert-link"></vue-username>
    zgłosił
    <a :href="flag.url">{{ elementNameAccusative }}</a>
    z powodu <strong>{{ flag.name }}</strong>
    dnia
    <vue-timeago :datetime="flag.created_at"></vue-timeago>

    <p v-if="flag.text" class="mb-0">{{ flag.text }}</p>

    <vue-modal ref="modal">
      <template v-slot:title>Zamknięcie raportu</template>
      <template v-slot:buttons>
        <button @click="$refs.modal.close()" type="button" class="btn btn-secondary" data-dismiss="modal">Anuluj</button>
        <button @click.prevent="closeFlag" class="btn btn-danger danger">Tak, zamknij</button>
      </template>
      <p>Czy na pewno chcesz zamknąć ten raport?</p>
    </vue-modal>
  </div>
</template>

<script lang="ts">
import Vue from 'vue';
import Component from "vue-class-component";
import {Prop, Ref} from "vue-property-decorator";
import {Flag} from "../../types/models";
import VueModal from '../modal.vue';
import VueUserName from "../user-name.vue";

@Component({
  name: 'flag',
  components: {'vue-username': VueUserName, 'vue-modal': VueModal},
})
export default class VueFlag extends Vue {
  @Prop()
  readonly flag!: Flag;

  @Ref()
  readonly modal!: VueModal;

  closeFlag() {
    this.modal.close()
    this.$store.dispatch('flags/delete', this.flag);
  }

  get elementNameAccusative(): string {
    for (const resource of this.flag.resources) {
      if (resource.resource_type === 'Coyote\\Post\\Comment') {
        return 'komentarz';
      }
      if (resource.resource_type === 'Coyote\\Comment') {
        return 'komentarz';
      }
    }
    for (const resource of this.flag.resources) {
      if (resource.resource_type === 'Coyote\\Post') {
        return 'post';
      }
      if (resource.resource_type === 'Coyote\\Microblog') {
        return 'mikroblog';
      }
      if (resource.resource_type === 'Coyote\\Job') {
        return 'ofertę pracy';
      }
    }
    return '';
  }
}
</script>

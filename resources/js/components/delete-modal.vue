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
  import { Prop, Ref } from "vue-property-decorator";
  import Component from "vue-class-component";
  import VueModal from "./modal.vue";
  import VueSelect from  './forms/select.vue';

  @Component({
    components: { 'vue-modal': VueModal, 'vue-select': VueSelect }
  })
  export default class VueDeleteModal extends Vue {
    @Ref('modal')
    modal!: VueModal;

    @Prop(Object)
    readonly reasons!: { [key: number]: string };

    reasonId: number | null = null;

    open() {
      this.modal.open();
    }

    close() {
      this.modal.close();
    }

    deletePost() {
      this.$emit('delete', this.reasonId);
      this.close();
    }
  }
</script>

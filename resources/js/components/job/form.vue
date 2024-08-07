<template>
  <div>
    <div class="card card-default">
      <div class="card-header">
        Podstawowe informacje
      </div>
      <div class="card-body">

        <div class="form-row border-bottom">
          <vue-form-group :errors="errors['title']" class="col-sm-9">
            <template v-slot:label>
              <label class="col-form-label">Tytuł oferty <em>*</em></label>
            </template>

            <vue-text name="title" v-model="job.title" placeholder="Np. Senior Java Developer" :maxlength="titleMaxLength" :is-invalid="'title' in errors"></vue-text>

            <span class="form-text text-muted">
              Pozostało <strong>{{ jobTitleCharactersRemaining }}</strong> znaków
            </span>
          </vue-form-group>

          <vue-form-group class="col-sm-3" label="Staż pracy">
            <vue-select name="seniority" v-model="job.seniority" :options="seniorities" placeholder="--"></vue-select>
          </vue-form-group>
        </div>

        <div class="form-group border-bottom">
          <label class="col-form-label">Lokalizacja</label>

          <div v-for="(location, index) in job.locations" class="row mb-2">
            <div class="col-sm-12">
              <div class="input-group">
                <a title="Dodaj więcej lokalizacji" class="input-group-text text-decoration-none" href="javascript:" @click="ADD_LOCATION">
                  <i class="fas fa-fw fa-circle-plus"></i>
                </a>
                <a title="Usuń lokalizację" class="input-group-text text-decoration-none" href="javascript:" @click="REMOVE_LOCATION(location)" v-if="job.locations.length > 1">
                  <i class="fas fa-fw fa-circle-minus text-danger"></i>
                </a>
                <vue-google-place @change="setLocation(index, ...arguments)" :label="location.label"></vue-google-place>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <div class="custom-control custom-checkbox mt-2 mb-3">
                <vue-checkbox id="is_remote" class="custom-control-input" name="is_remote" v-model="job.is_remote"></vue-checkbox>
                <label for="is_remote" class="custom-control-label">Możliwa praca zdalna w zakresie</label>

                <vue-select name="remote_range" :options="remoteRange" v-model="job.remote_range" class="form-control-sm d-inline-block" style="width: 100px; margin-top: -5px"></vue-select>
              </div>
            </div>
          </div>
        </div>

        <div class="form-group border-bottom">
          <label class="col-form-label">Ofertowane wynagrodzenie netto i forma zatrudnienia</label>

          <div>
            od
            <vue-text type="number" name="salary_from" class="d-inline-block" v-model="job.salary_from" :is-invalid="'salary_from' in errors"></vue-text>

            do
            <vue-text type="number" name="salary_to" class="d-inline-block" v-model="job.salary_to" :is-invalid="'salary_to' in errors"></vue-text>

            <vue-select name="currency_id" class="d-inline-block" :options="currenciesValues" v-model="job.currency_id"></vue-select>

            <vue-select name="is_gross" class="d-inline-block" :options="['Netto', 'Brutto']" v-model="isGross"></vue-select>
            <vue-select name="rate" class="d-inline-block" :options="rates" v-model="job.rate"></vue-select>
            <vue-select name="employment" class="d-inline-block" :options="employments" v-model="job.employment"></vue-select>

            <span class="form-text text-muted">Podanie tych informacji nie jest obowiązkowe, ale dzięki temu Twoja oferta zainteresuje więcej osób. Obiecujemy!</span>

            <vue-error :message="errors.salary_from"></vue-error>
            <vue-error :message="errors.salary_to"></vue-error>
          </div>
        </div>

        <div class="form-group">
          <label class="col-form-label">Kluczowe technologie (wymagane lub mile widziane)</label>

          <vue-tags-inline @change="addTag" placeholder="Np. java, c#, ms-sql" class="form-control"></vue-tags-inline>

          <span class="form-text text-muted" v-if="errors.tags != null">{{ errors.tags[0] }}</span>
          <span class="form-text text-muted" v-else-if="suggestions.length === 0">Wybierz z listy lub wpisz nazwę języka/technologii i naciśnij Enter, aby dodać wymaganie.</span>
          <span class="form-text text-muted" v-else-if="suggestions.length > 0">
            Podpowiedź:

            <template v-for="(suggestion, index) in suggestions">
              <a href="javascript:" class="tag-suggestion" @click="addTag({ name: suggestion })">{{ suggestion }}</a>{{ index < suggestions.length - 1 ? ', ' : '' }}
            </template>
          </span>

          <vue-tags :tags.sync="job.tags" :editable="true" @delete="REMOVE_TAG" :tooltips="['mile widziane', 'średnio zaawansowany', 'zaawansowany']" class="tag-clouds-md mt-3"></vue-tags>
        </div>
      </div>
    </div>

    <div class="card card-default">
      <div class="card-header">
        Szczegółowe informacje
      </div>

      <div class="card-body">
        <div class="border-bottom form-group">
          <label class="col-form-label">Opis oferty (opcjonalnie)</label>

          <vue-tinymce v-model="job.description" :init="tinyMceOptions"></vue-tinymce>
          <span class="form-text text-muted">Miejsce na szczegółowy opis oferty. Pole to jednak nie jest wymagane.</span>

          <input type="hidden" name="description" v-model="job.description">
        </div>

        <div class="form-group border-bottom">
          <label class="col-form-label">Narzędzia oraz metodologia pracy</label>

          <ol class="features list-group list-group-horizontal d-flex flex-row flex-wrap">
            <li class="list-group-item w-50" v-for="(feature, index) in job.features" :class="{checked: feature.checked}">
              <div class="row form-group">
                <div class="col-6" @click="TOGGLE_FEATURE(feature)">
                  <i class="fas fa-fw " :class="{'fa-check': feature.checked, 'fa-xmark': !feature.checked}"></i>

                  {{ feature.name }}
                </div>

                <div class="col-6" v-show="feature.checked && feature.default">
                  <input type="text" class="form-control form-control-sm" :placeholder="feature.default" v-model="feature.value">
                </div>
              </div>
            </li>
          </ol>
        </div>

        <div class="form-group">
          <div class="form-group">
            <div class="custom-control custom-radio">
              <input type="radio" id="enable_apply_1" name="enable_apply" v-model="job.enable_apply" :value="true" class="custom-control-input">

              <label for="enable_apply_1" class="custom-control-label">
                Zezwól na wysyłanie CV poprzez serwis 4programmers.net
              </label>
            </div>
          </div>

          <div class="row">
            <div class="col-sm-6">
              <vue-text name="email" v-model="job.email" :disabled="job.enable_apply === false" :is-invalid="'email' in errors"></vue-text>
              <span class="form-text text-muted">Adres e-mail nie będzie widoczny dla osób postronnych.</span>

              <vue-error :message="errors.email"></vue-error>
            </div>
          </div>
        </div>

        <div class="form-group">
          <div class="form-group">
            <div class="custom-control custom-radio">
              <input type="radio" id="enable_apply_0" v-model="job.enable_apply" :value="false" class="custom-control-input">

              <label for="enable_apply_0" class="custom-control-label">
                ...lub podaj informacje w jaki sposób kandydaci mogą aplikować na to stanowisko
              </label>
            </div>
          </div>

          <div v-show="job.enable_apply === false">
            <vue-tinymce v-model="job.recruitment" :init="tinyMceOptions"></vue-tinymce>

            <input type="hidden" name="recruitment" v-model="job.recruitment">

            <vue-error :message="errors.recruitment"></vue-error>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import VueButton from '@/components/forms/button.vue';
import VueCheckbox from '@/components/forms/checkbox.vue';
import VueError from '@/components/forms/error.vue';
import VueFormGroup from '@/components/forms/form-group.vue';
import VueRadio from '@/components/forms/radio.vue';
import VueSelect from '@/components/forms/select.vue';
import VueTagsInline from '@/components/forms/tags-inline.vue';
import VueText from '@/components/forms/text.vue';
import VueGooglePlace from '@/components/google-maps/place.vue';
import VueTags from '@/components/tags.vue';
import TinyMceOptions from '@/libs/tinymce';
import store from '@/store';
import {Employment, Rate, Seniority, Tag} from '@/types/models';
import VueTinyMce from '@tinymce/tinymce-vue';
import axios, {AxiosResponse} from 'axios';
import Vue from 'vue';
import {mapMutations} from 'vuex';

export default Vue.extend({
  name: 'VueForm',
  components: {
    'vue-form-group': VueFormGroup,
    'vue-text': VueText,
    'vue-select': VueSelect,
    'vue-checkbox': VueCheckbox,
    'vue-radio': VueRadio,
    'vue-button': VueButton,
    'vue-error': VueError,
    'vue-tags-inline': VueTagsInline,
    'vue-tags': VueTags,
    'vue-google-place': VueGooglePlace,
    'vue-tinymce': VueTinyMce,
  },
  props: {
    job: {
      type: Object,
      required: true,
    },
    currencies: {
      type: Array,
      required: true,
    },
    errors: {
      type: Object,
      required: false,
    },
  },
  data() {
    return {
      suggestions: {},
      titleMaxLength: 60,
    };
  },
  methods: {
    ...mapMutations('jobs', ['ADD_LOCATION', 'REMOVE_LOCATION', 'SET_LABEL', 'ADD_TAG', 'REMOVE_TAG', 'TOGGLE_FEATURE']),
    setLocation(index, location) {
      this.$store.commit('jobs/SET_LOCATION', {index, location});
    },
    addTag(tag: Tag) {
      this.$store.commit('jobs/ADD_TAG', tag.name);

      // fetch only tag name
      let pluck = this.job.tags.map(item => item.name);

      // request suggestions
      axios.get<any>('/Praca/Tag/Suggestions', {params: {t: pluck}})
        .then((response: AxiosResponse<any>) => this.suggestions = response.data);
    },
  },
  computed: {
    jobTitleCharactersRemaining(): number {
      return this.titleMaxLength - String(this.job.title ?? '').length;
    },
    tinyMceOptions() {
      return TinyMceOptions;
    },
    remoteRange() {
      let result = {};

      for (let i = 100; i > 0; i -= 10) {
        result[i] = `${i}%`;
      }

      return result;
    },
    rates() {
      return Rate;
    },
    seniorities() {
      return Seniority;
    },
    employments() {
      return Employment;
    },
    currenciesValues() {
      return this.currencies.reduce((acc, value) => {
        acc[value.id as unknown as string] = `${value.name} (${value.symbol})`;
        return acc;
      }, {});
    },
    isGross: {
      get() {
        return +this.job.is_gross;
      },
      set(flag) {
        this.job.is_gross = !!flag;
      },
    },
  },
  watch: {
    job: {
      handler(job) {
        store.commit('jobs/INIT_FORM', job);
      },
      deep: true,
    },
  },
});

</script>

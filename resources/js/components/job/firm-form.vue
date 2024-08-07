<template>
  <div class="card card-default">
    <div class="card-header">
      Dane firmy
    </div>

    <div class="card-body">
      <div v-if="Object.keys(firmsSelect).length" class="border-bottom form-group">
        <label class="col-form-label">Wybierz firmę z listy</label>

        <vue-select :options="firmsSelect" placeholder="-- zapisane firmy --" v-model="defaultFirm"></vue-select>
        <span class="form-text text-muted">Możesz wybrać jedną z pośród kilku firm przypiasnych do Twojego konta.</span>
      </div>

      <vue-form-group :errors="errors['firm.name']" label="Nazwa firmy" class="border-bottom">
        <div class="input-group">
          <a @click="addFirm" class="input-group-text text-decoration-none" href="javascript:" title="Dodaj nową firmę"><i class="fas fa-fw fa-circle-plus"></i></a>
          <vue-text v-model="firm.name" :is-invalid="'firm.name' in errors" name="firm[name]"></vue-text>
        </div>

        <span class="form-text text-muted">Podając nazwę firmy, oferta staje się bardziej wiarygodna i wartościowa.</span>
      </vue-form-group>

      <div class="border-bottom form-group">
        <div class="form-group">
          <div class="custom-control custom-radio">
            <input type="radio" id="is_agency_0" class="is_agency custom-control-input" name="is_agency" v-model="firm.is_agency" :value="false"></input>

            <label for="is_agency_0" class="custom-control-label">Bezpośredni pracodawca</label>
          </div>
        </div>

        <div class="form-group">
          <div class="custom-control custom-radio">
            <input type="radio" id="is_agency_1" class="is_agency custom-control-input" name="is_agency" v-model="firm.is_agency" :value="true"></input>

            <label for="is_agency_1" class="custom-control-label">Agencja pośrednictwa / IT outsourcing</label>
          </div>
        </div>
      </div>

      <div class="form-group">
        <label class="col-form-label">Logo</label>

        <div class="row">
          <div class="col-sm-2">
            <vue-thumbnail
              :url="firm.logo"
              :only-image="true"
              name="logo"
              upload-url="/Firma/Logo"
              @upload="addLogo"
              @delete="removeLogo">
            </vue-thumbnail>
          </div>
        </div>
      </div>

      <vue-form-group :errors="errors['firm.website']" label="Strona WWW" class="border-bottom">
        <vue-text v-model="firm.website" :is-invalid="'firm.website' in errors" name="firm[website]"></vue-text>

        <span class="form-text text-muted">Firmowa strona WWW. Będzie ona wyświetlana przy ofercie.</span>
      </vue-form-group>

      <vue-form-group label="Opis firmy" class="border-bottom">
        <vue-tinymce v-model="firm.description" :init="tinyMceOptions"></vue-tinymce>

        <span class="form-text text-muted">Czym zajmuje się firma, w jakich branżach działa oraz jakie technologie wykorzystuje?</span>
      </vue-form-group>

      <div class="form-group border-bottom" v-show="firm.is_agency === false">
        <label class="col-form-label">Dodaj zdjęcia</label>

        <div class="row mb-2">
          <div class="col-sm-2" v-for="photo in gallery">
            <vue-thumbnail
              :url="photo.url"
              :only-image="true"
              @upload="addPhoto"
              @delete="removePhoto">
            </vue-thumbnail>
          </div>

          <div class="col-sm-2">
            <vue-thumbnail @upload="addPhoto" name="asset"></vue-thumbnail>
          </div>
        </div>
      </div>

      <vue-form-group :errors="errors['firm.youtube_url']" label="Nagranie wideo w Youtube" class="form-group">
        <vue-text v-model="firm.youtube_url" :is-invalid="'firm.youtube_url' in errors" name="firm[youtube_url]"></vue-text>

        <span class="form-text text-muted">Film promujący firmę będzie wyświetlany pod ogłoszeniem o pracę.</span>
      </vue-form-group>

      <vue-form-group :errors="errors['firm.employees']" label="Liczba pracowników w firmie">
        <vue-select :options="employees" v-model="firm.employees" placeholder="--" name="firm[employees]"></vue-select>

        <span class="form-text text-muted">Pozwala ocenić jak duża jest firma. Czy jest to korporacja, czy mała rodzinna firma?</span>
      </vue-form-group>

      <vue-form-group :errors="errors['firm.founded']" label="Rok powstania" class="border-bottom">
        <vue-select :options="founded" v-model="firm.founded" placeholder="--" name="firm[founded]"></vue-select>

        <span class="form-text text-muted">Pozwala ocenić jak duża jest firma. Czy jest to korporacja, czy mała rodzinna firma?</span>
      </vue-form-group>

      <vue-form-group label="Adres" class="border-bottom" v-show="firm.is_agency === false">
        <vue-text autocomplete="off" v-model="address" @keydown.native.enter.prevent="changeAddress" name="address"></vue-text>

        <span class="form-text text-muted">Wpisz adres i naciśnij Enter lub kliknij na mapę. Adres firmy będzie wyświetlany przy ofercie.</span>

        <div id="map">
          <vue-map @click="geocode" class="h-100" :latitude="firm.latitude || 51.919438" :longitude="firm.longitude || 19.145135999">
            <vue-marker :latitude="firm.latitude" :longitude="firm.longitude"></vue-marker>
          </vue-map>
        </div>
      </vue-form-group>

      <div class="form-group border-bottom" v-show="firm.is_agency === false">
        <label class="col-form-label">Benefity</label>
        <span class="form-text text-muted">Kliknij na wybraną pozycję, aby zaznaczyć benefity jakie oferuje Twoja firma. Jeżeli nie ma go na liście, możesz dodać nową pozycję wpisując ją w polu poniżej.</span>

        <ol class="benefits list-group list-group-horizontal d-flex flex-row flex-wrap">

          <li
            class="list-group-item w-50 clickable"
            v-for="benefit in defaultBenefits"
            :class="{checked: firm.benefits.includes(benefit)}"
            @click="TOGGLE_BENEFIT(benefit)"
          >
            <i class="fas fa-fw " :class="{'fa-check': firm.benefits.includes(benefit), 'fa-xmark': !firm.benefits.includes(benefit)}"></i> {{ benefit }}
          </li>

          <li class="list-group-item w-50 checked" v-for="benefit in firm.benefits" v-if="!defaultBenefits.includes(benefit)">
            <i class="fas fa-fw fa-check"></i>

            <input type="text" maxlength="100" :value="benefit" class="form-control form-control-sm" @keydown.enter.prevent="">
            <button class="btn btn-sm btn-delete" title="Usuń tę pozycję" @click.prevent="REMOVE_BENEFIT(benefit)"><i class="fas fa-circle-minus text-danger"></i></button>
          </li>

          <li class="list-group-item w-50 checked">
            <i class="fas fa-fw fa-check"></i>

            <input v-model="benefit" type="text" maxlength="100" class="form-control form-control-sm" @keydown.enter.prevent="addBenefit" placeholder="Wpisz tekst i naciśnij Enter, aby dodać">
          </li>
        </ol>

        <div class="clearfix"></div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import Vue from 'vue';
import VueFormGroup from '@/components/forms/form-group.vue';
import VueText from '@/components/forms/text.vue';
import VueSelect from '@/components/forms/select.vue';
import VueButton from '@/components/forms/button.vue';
import VueError from '@/components/forms/error.vue';
import VueTinyMce from '@tinymce/tinymce-vue';
import VueMap from '@/components/google-maps/map.vue';
import VueMarker from '@/components/google-maps/marker.vue';
import VueThumbnail from '@/components/thumbnail.vue';
import { Firm, Asset } from '@/types/models';
import { mapMutations } from 'vuex';
import Geocoder from '../../libs/geocoder.js';
import TinyMceOptions from '../../libs/tinymce.js';
import store from '../../store';

export default Vue.extend({
  name: 'VueFirmForm',
  components: {
    'vue-form-group': VueFormGroup,
    'vue-text': VueText,
    'vue-select': VueSelect,
    'vue-button': VueButton,
    'vue-error': VueError,
    'vue-tinymce': VueTinyMce,
    'vue-map': VueMap,
    'vue-marker': VueMarker,
    'vue-thumbnail': VueThumbnail,
  },
  props: {
    firm: {
      type: Object,
      required: true,
    },
    errors: {
      type: Object,
      required: false,
    },
    defaultBenefits: {
      type: Array,
      required: true,
    },
    employees: {
      type: Array,
      required: true,
    },
    firms: {
      type: Array,
      required: true,
    },
  },
  data() {
    return {
      benefit: '',
    };
  },
  methods: {
    ...mapMutations('jobs', ['REMOVE_BENEFIT', 'TOGGLE_BENEFIT']),
    changeAddress(e) {
      const val = e.target.value.trim();
      const geocoder = new Geocoder();

      if (val.length) {
        geocoder.geocode(val, result => {
          this.firm = Object.assign(this.firm, result);
        });
      } else {
        ['longitude', 'latitude', 'country', 'city', 'street', 'street_number', 'postcode'].forEach(field => {
          this.firm[field] = null;
        });
      }
    },
    geocode(latlng) {
      const geocoder = new Geocoder();
      geocoder.reverseGeocode(latlng, result => this.firm = Object.assign(this.firm, result));
    },
    addBenefit() {
      if (this.benefit.trim()) {
        store.commit('jobs/ADD_BENEFIT', this.benefit);
      }
      this.benefit = '';
    },
    addLogo(input) {
      this.firm.logo = input.url;
    },
    addPhoto(asset: Asset) {
      this.firm.assets.push(asset);
    },
    removeLogo() {
      this.firm.logo = null;
    },
    removePhoto(url: string) {
      const gallery = this.firm.assets;
      gallery.splice(gallery.findIndex(asset => asset.url === url), 1);
    },
    addFirm() {
      this.$store.commit('jobs/SET_FIRM', {
        id: null,
        name: '',
        logo: null,
        thumbnail: null,
        description: '',
        website: null,
        is_agency: false,
        employees: null,
        founded: null,
        vat_id: null,
        youtube_url: null,
        gallery: [''],
        benefits: [],
        latitude: null,
        longitude: null,
        country: null,
        street: null,
        postcode: null,
        city: null,
        street_number: null,
        country_id: null,
      });
    },
  },
  computed: {
    address() {
      return String((this.firm.street || '') + ' ' + (this.firm.street_number || '') + ' ' + (this.firm.postcode || '') + ' ' + (this.firm.city || '')).trim();
    },
    gallery() {
      return this.firm.assets?.length ? this.firm.assets : [];
    },
    founded() {
      const year = new Date().getFullYear();
      let result = {};

      for (let i = 1900; i <= year; i++) {
        result[i] = i;
      }

      return result;
    },
    tinyMceOptions() {
      return TinyMceOptions;
    },
    firmsSelect() {
      return this.firms.reduce((acc, curr) => {
        acc[curr.id as unknown as string] = curr.name;
        return acc;
      }, {});
    },
    defaultFirm: {
      get() {
        return this.firm.id;
      },
      set(id) {
        if (!id) {
          this.addFirm();
          return;
        }
        this.$store.commit('jobs/SET_FIRM', this.firms.find(firm => firm.id == id));
      },
    },
  },
});
</script>

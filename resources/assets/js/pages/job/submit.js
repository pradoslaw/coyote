import '../../plugins/uploader';
import tinymce from '../../libs/tinymce';
import Dialog from '../../libs/dialog';
import Map from '../../libs/map';
import Vue from 'vue';
import VueThumbnail from '../../components/thumbnail.vue';
import VuePricing from '../../components/pricing.vue';
import VueTagsDropdown from '../../components/tags-dropdown.vue';
import VueTagsSkill from '../../components/tag-skill.vue';
import VueGooglePlace from '../../components/google-place.vue';
import VueText from '../../components/forms/text.vue';
import VueSelect from '../../components/forms/select.vue';
import VueCheckbox from '../../components/forms/checkbox.vue';
import VueRadio from '../../components/forms/radio.vue';
import VueError from '../../components/forms/error.vue';
import Editor from '@tinymce/tinymce-vue';
import 'chosen-js';
import axios from "axios";
import Config from "../../libs/config";

new Vue({
    el: '.submit-form',
    delimiters: ['${', '}'],
    data: window.data,
    components: {
        'vue-tinymce': Editor,
        'vue-thumbnail': VueThumbnail,
        'vue-pricing': VuePricing,
        'vue-tags-dropdown': VueTagsDropdown,
        'vue-tag-skill': VueTagsSkill,
        'vue-google-place': VueGooglePlace,
        'vue-text': VueText,
        'vue-select': VueSelect,
        'vue-checkbox': VueCheckbox,
        'vue-radio': VueRadio,
        'vue-error': VueError
    },
    mounted () {
        this.marker = null;
        this.job.enable_apply = +this.job.enable_apply;

        if (typeof google !== 'undefined' && this.firm) {
            this.map = new Map();

            if (this.firm.latitude && this.firm.longitude) {
                this._setupMarker();
            }

            this.map.setupGeocodeOnMapClick(result => {
                this.firm = Object.assign(this.firm, result);
                this._setupMarker();
            });
        }

        // ugly hack to initialize jquery fn after dom is loaded
        $(() => {
            $.uploader({
                input: 'logo',
                onChanged: data => {
                    this.firm.thumbnail = $('.img-container > img').attr('src');
                    this.firm.logo = data.name;
                },
                onDeleted: () => {
                    this.firm.logo = null;
                    this.firm.thumbnail = null;
                }
            });
        });

        $('[v-loader]').remove();
        this._initTooltip();

        $('#industries').chosen({
            placeholder_text_multiple: 'Wybierz z listy'
        });

        axios.defaults.headers.common['X-CSRF-TOKEN'] = Config.csrfToken();
    },
    methods: {
        submitForm () {
            axios.post(this.$refs.submitForm.action, new FormData(this.$refs.submitForm))
                .then(response => {
                    window.location.href = response.data;
                })
                .catch(error => {
                    this.errors = error.response.data.errors;
                });
        },

        /**
         * Add tag after clicking on suggestion tag.
         *
         * @param {String} name
         */
        addTag (name) {
            this.job.tags.push({name: name, pivot: {priority: 1}});
            // fetch only tag name
            let pluck = this.job.tags.map(item => item.name);

            // request suggestions
            axios.get(this.suggestionUrl, {params: {t: pluck}})
                .then(response => {
                    this.suggestions = response.data;
                });

            this._initTooltip();
        },
        onTagChange (name) {
            this.addTag(name);
        },
        onTagDelete (name) {
            let index = this.job.tags.findIndex(el => {
                return el.name === name;
            });

            this.job.tags.splice(index, 1);
        },
        isInvalid (fields) {
            return Object.keys(this.errors).findIndex(element => fields.indexOf(element) > -1) > -1;
        },
        charCounter (item, limit) {
            let model = item.split('.').reduce((o, i) => o[i], this);

            return limit - String(model !== null ? model : '').length;
        },
        toggleBenefit (item) {
            let index = this.firm.benefits.indexOf(item);

            if (index === -1) {
                this.firm.benefits.push(item);
            } else {
                this.firm.benefits.splice(index, 1);
            }
        },
        addBenefit: function (e) {
            if (e.target.value.trim()) {
                this.firm.benefits.push(e.target.value);
            }

            e.target.value = '';
        },
        removeBenefit: function (benefit) {
            this.firm.benefits.splice(this.firm.benefits.indexOf(benefit), 1);
        },
        updateBenefit: function () {},
        /**
         * Enable/disable feature for this offer.
         *
         * @param feature
         */
        toggleFeature: function (feature) {
            feature.pivot.checked = +!feature.pivot.checked;
        },
        addFirm: function () {
            let dialog = new Dialog({
                title: 'Dodanie nowej firmy',
                message: 'Czy na pewno chcesz dodać nową firme i przypisać ją do tego ogłoszenia?',
                buttons: [{
                    label: 'Anuluj',
                    attr: {
                        'class': 'btn btn-default',
                        'type': 'button',
                        'data-dismiss': 'modal'
                    }
                }, {
                    label: 'Tak',
                    attr: {
                        'class': 'btn btn-primary'
                    },
                    onClick: () => {
                        this._newFirm();
                        dialog.close();
                    }
                }]
            });

            dialog.show();
        },
        selectFirm: function (firmId) {
            let index = this.firms.findIndex(element => element.id === firmId);

            this.firm = this.firms[index];
            this.firm.is_private = false;

            this.benefits = this.firm.benefits;

            // text can not be NULL
            // tinymce.get('description').setContent(this.firm.description === null ? '' : this.firm.description);
            this.firm.description = this.firm.description === null ? '' : this.firm.description;
            $('#industries').trigger('chosen:updated');
        },
        changeFirm: function () {
            if (!this.firm.name) {
                return;
            }

            let dialog = new Dialog({
                title: 'Zmiana nazwy firmy?',
                message: 'Zamierzasz edytować nazwę tej firmy. Weź pod uwagę, że zmieniona nazwa będzie wyświetlana przy wszystkich Twoich ofertach. Czy może chcesz dodać nową firmę?',
                buttons: [{
                    label: 'Jest OK, chce tylko zmienić nazwę',
                    attr: {
                        'class': 'btn btn-default',
                        'type': 'button',
                        'data-dismiss': 'modal'
                    }
                }, {
                    label: 'Tak, chcę dodać nową firmę',
                    attr: {
                        'class': 'btn btn-primary'
                    },
                    onClick: () => {
                        this._newFirm();
                        dialog.close();
                    }
                }]
            });

            dialog.show();
        },
        _newFirm: function () {
            this.firm = {
                'id': null,
                'name': null,
                'headline': '',
                'logo': null,
                'thumbnail': null,
                'description': null,
                'website': null,
                'is_private': +false,
                'is_agency': +false,
                'employees': null,
                'founded': null,
                'vat_id': null,
                'youtube_url': null,
                'gallery': [{file: ''}]
            };

            this.benefits = [];
            this.firm.description = '';
            // tinymce.get('description').setContent(''); // new firm - empty description

            $('#industries').trigger('chosen:updated');
        },
        changeAddress: function (e) {
            let val = e.target.value.trim();

            if (val.length) {
                this.map.geocode(val, result => {
                    this.firm = Object.assign(this.firm, result);

                    this._setupMarker(); // must be inside closure
                });
            }
            else {
                ['longitude', 'latitude', 'country', 'city', 'street', 'postcode'].forEach(field => {
                    this.firm[field] = null;
                });

                this._setupMarker();
            }
        },
        _setupMarker: function () {
            this.map.removeMarker(this.marker);
            this.marker = this.map.addMarker(this.firm.latitude, this.firm.longitude);
        },
        onThumbnailUploaded: function (file) {
            this.firm.gallery.splice(this.firm.gallery.length - 1, 0, file);
        },
        onThumbnailDeleted: function (file) {
            let index = this.firm.gallery.findIndex(photo => photo.file === file);

            if (index > -1) {
                this.firm.gallery.splice(index, 1);
            }
        },
        _initTooltip: function () {
            this.$nextTick(function () {
                $('i[data-toggle="tooltip"]').tooltip();
            });
        },

        addLocation () {
            this.job.locations.push({});
        },

        removeLocation (location) {
            this.job.locations.splice(this.job.locations.indexOf(location), 1);
        },

        setAddress (index, data) {
            this.$set(this.job.locations, index, data);
        }
    },
    computed: {
        address: {
            get: function () {
                return String((this.firm.street || '') + ' ' + (this.firm.house || '') + ' ' + (this.firm.postcode || '') + ' ' + (this.firm.city || '')).trim();
            },
            set: function () {
                //
            }
        },

        gallery () {
            return this.firm.gallery && this.firm.gallery.length ? this.firm.gallery : {'file': ''};
        },

        tinymce: {
            get: function () {
                return tinymce;
            }
        }
    },
    watch: {
        'firm.is_private': function (flag) {
            if (!Boolean(parseInt(flag))) {
                google.maps.event.trigger(map, 'resize');
            }
        }
    }
});

$(() => {
    'use strict';

    let navigation = $('#form-navigation');
    let fixed = $('#form-navbar-fixed');

    $('#form-navigation-container')
        .html(navigation.html())
        .on('click', ':submit', () => $('.submit-form').submit())
        .on('click', 'button[data-submit-state]', e => $(e.currentTarget).attr('disabled', 'disabled').text($(e.currentTarget).data('submit-state')));

    if (navigation.length) {
        $(window).scroll(() => {
            let bottom = $(window).scrollTop() + $(window).height();

            if (bottom > navigation.offset().top) {
                fixed.fadeOut();
            }
            else {
                fixed.show();
            }
        }).trigger('scroll');
    }

    /**
     * Save and exit button
     */
    $('.btn-save').on('click', () => {
        $('input[name="done"]').val(1);
    });
});

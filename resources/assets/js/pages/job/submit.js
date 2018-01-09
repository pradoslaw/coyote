import '../../plugins/uploader';
import initTinymce from '../../libs/tinymce';
import Dialog from '../../libs/dialog';
import Map from '../../libs/map';
import VueThumbnail from '../../components/thumbnail.vue';
import VuePricing from '../../components/pricing.vue';
import VueTagsDropdown from '../../components/tags-dropdown.vue';
import VueTagsSkill from '../../components/tag-skill.vue';
import 'chosen-js';
import 'intl-tel-input';

/**
 * Cast data from bool to int to properly display radio buttons (0 and 1 instade of true and false).
 *
 * @param data
 * @return {*}
 */
function toInt(data) {
    for (let item in data) {
        if (data.hasOwnProperty(item)) {
            if (typeof(data[item]) === 'boolean') {
                data[item] = +data[item];
            }

            if (typeof(data[item]) === 'object') {
                data[item] = toInt(data[item]);
            }
        }
    }

    return data;
}

Vue.component('vue-thumbnail', VueThumbnail);
Vue.component('vue-pricing', VuePricing);
Vue.component('vue-tags-dropdown', VueTagsDropdown);
Vue.component('vue-tag-skill', VueTagsSkill);

new Vue({
    el: '.submit-form',
    delimiters: ['${', '}'],
    data: toInt(window.data),
    mounted: function () {
        initTinymce();

        this.marker = null;

        if (typeof google !== 'undefined') {
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
    },
    methods: {
        /**
         * Add tag after clicking on suggestion tag.
         *
         * @param {String} name
         */
        addTag: function (name) {
            this.tags.push({name: name, pivot: {priority: 1}});
            // fetch only tag name
            let pluck = this.tags.map(item => item.name);

            // request suggestions
            $.get(this.suggestionUrl, {t: pluck}, result => {
                this.suggestions = result;
            });

            this._initTooltip();
        },
        onTagChange: function (name) {
            this.addTag(name);
        },
        onTagDelete: function (name) {
            let index = this.tags.findIndex(el => {
                return el.name === name;
            });

            this.tags.splice(index, 1);
        },
        isInvalid: function (fields) {
            return Object.keys(this.errors).findIndex(element => fields.indexOf(element) > -1) > -1;
        },
        charCounter: function (item, limit) {
            let model = item.split('.').reduce((o, i) => o[i], this);

            return limit - String(model !== null ? model : '').length;
        },
        toggleBenefit: function (item) {
            let index = this.benefits.indexOf(item);

            if (index === -1) {
                this.benefits.push(item);
            } else {
                this.benefits.splice(index, 1);
            }
        },
        addBenefit: function (e) {
            if (e.target.value.trim()) {
                this.benefits.push(e.target.value);
            }

            e.target.value = '';
        },
        removeBenefit: function (benefit) {
            this.benefits.splice(this.benefits.indexOf(benefit), 1);
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
            let index = this.firms.findIndex(element => element.id == firmId);

            this.firm = this.firms[index];
            this.firm.is_private = +false; // must be the number - not bool

            this.benefits = this.firm.benefits;

            // text can not be NULL
            tinymce.get('description').setContent(this.firm.description === null ? '' : this.firm.description);
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
            tinymce.get('description').setContent(''); // new firm - empty description

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
        }
    },
    computed: {
        address: function () {
            return String((this.firm.street || '') + ' ' + (this.firm.house || '') + ' ' + (this.firm.postcode || '') +  ' ' + (this.firm.city || '')).trim();
        }
    },
    watch: {
        'job.enable_apply': function (flag) {
            if (Boolean(parseInt(flag))) {
                tinymce.get('recruitment').hide();

                $('#recruitment').attr('disabled', 'disabled').hide();
            } else {
                tinymce.get('recruitment').show();

                $('#recruitment').removeAttr('disabled');
            }
        },
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

    require.ensure([], require => {
        require('intl-tel-input/build/js/utils');

        $('input[type="tel"]').intlTelInput({
            preferredCountries: ['pl'],
            separateDialCode: true,
            nationalMode: true,
            initialCountry: 'auto',
            geoIpLookup: function (callback) {
                $.getJSON('//geo-ip.io/1.0/ip/?callback=?', {}, function (json) {
                    callback(json.country_code.toLowerCase());
                });
            },
        });

        $('.submit-form').submit(function () {
            let input = $('input[type="tel"]');
            let number = input.intlTelInput('getNumber');

            input.val(number.length > 3 ? number : null);
        });
    });
});

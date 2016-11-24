class Tags {
    constructor(options) {
        let defaults = {
            input: '#tag',
            dropdown: '.tag-suggestions',
            container: '#tags-container',
            remove: '.btn-remove'
        };

        this.setup = $.extend(defaults, options);

        this.input = $(this.setup.input);
        this.dropdown = $(this.setup.dropdown);
        this.container = $(this.setup.container);
        this.selectedIndex = -1;

        this.dropdown.css({
            'width':			this.input.outerWidth() - 4,
            'left':				this.input.position().left,
            'top':				this.input.position().top + this.input.outerHeight()
        });

        this.onFocus();
        this.onKeyUp();
        this.onKeyDown();
        this.onHover();
        this.onItemClick();
        this.onRemove();

        // let self = this;

        $(document).bind('click', e => {
            let $target = $(e.target);

            if (!$target.is(this.input)) {
                this.hideDropdown();
            }
        });
    }

    onKeyUp() {
        // let self = this;

        this.input.on('keyup', e => {
            let keyCode = e.keyCode || window.event.keyCode;

            if (keyCode === 13) {
                if ($('li.hover', this.dropdown).find('span').text() !== '') {
                    this.addTag($('li.hover', this.dropdown).find('span').text());
                }
                else if (this.input.val() !== '') {
                    this.addTag(this.input.val());
                }

                this.hideDropdown();
                this.input.val('');

                e.preventDefault();
            }
            else if (keyCode === 40) { // down
                this.select(this.selectedIndex + 1);
            }
            else if (keyCode === 38) { // up
                this.select(this.selectedIndex - 1);
            }
            else {
                let searchText = this.input.val().toLowerCase();
                let hits = 0;

                $('li', this.dropdown).each(index => {
                    let item = $('li:eq(' + index + ')', this.dropdown);
                    let text = item.find('span').text();

                    if (text.toLowerCase().indexOf(searchText) === -1) {
                        item.hide();
                    }
                    else  {
                        item.show();
                        hits++;
                    }
                });

                this.dropdown.toggle(hits > 0);
            }
        });
    }

    onKeyDown() {
        this.input.on('keydown', e => {
            let keyCode = e.keyCode || window.event.keyCode;

            if (keyCode === 27) {
                this.input.val('');
                this.dropdown.hide();
            }
            else if (keyCode === 13) {
                e.preventDefault();
            }
        });
    }

    onHover() {
        $('li', this.dropdown).hover(e => $(e.currentTarget).addClass('hover'), e => $(e.currentTarget).removeClass('hover'));
    }

    onItemClick() {
        // let self = this;

        this.dropdown.on('click', 'li', e => {
            this.addTag($(e.currentTarget).find('span').text());
            this.hideDropdown();

            this.input.val('').focus();
        });
    }

    onRemove() {
        this.container.on('click', this.setup.remove, e => {
            $(e.currentTarget).parents('.tag-item').remove();
        });
    }

    onFocus() {
        // let self = this;

        this.input.on('focus click', () => {
            this.dropdown.show();
        });
    }

    select(position) {
        let length = $('li:visible', this.dropdown).length;

        if (length > 0) {
            if (position >= length) {
                position = 0;
            }
            else if (position < 0) {
                position = length -1;
            }
            this.selectedIndex = position;

            $('li:visible', this.dropdown).removeClass('hover');
            $('li:visible:eq(' + this.selectedIndex + ')', this.dropdown).addClass('hover');

            this.dropdown.scrollTop(position * $('li:first', this.dropdown).outerHeight());
        }
    }

    hideDropdown() {
        this.dropdown.hide();
    }

    showDropdown() {
        this.dropdown.show();
    }

    addTag(value) {
        value = $.trim(value)
            .toString()
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/'/g, "&#39;")
            .replace(/"/g, "&#34;")
            .toLowerCase()
            .replace(/ /g, '-');

        // let self = this;

        $.post(this.input.data('post-url'), {name: value}, html => {
            this.container.append(html);
        }).fail(() => {
            $('#alert').modal('show');
        });

        this.selectedIndex = - 1;
        $('li', this.dropdown).removeClass('hover').show();
    }
}

$(() => {
    'use strict';

    if ($('#tag').length) {
        new Tags();
    }

    if (typeof google !== 'undefined') {
        google.maps.event.addDomListener(window, 'load', initialize);
    }

    let navigation = $('#form-navigation');
    let fixed = $('#form-navbar-fixed');

    $('#form-navigation-container')
        .html(navigation.html())
        .on('click', ':submit', () => $('#job-posting').submit())
        .on('click', 'button[data-submit-state]', e => $(e.currentTarget).attr('disabled', 'disabled').text($(e.currentTarget).data('submit-state')));

    $(window).scroll(() => {
        let bottom = $(window).scrollTop() + $(window).height();

        if (bottom > navigation.offset().top) {
            fixed.fadeOut();
        }
        else {
            fixed.show();
        }
    }).trigger('scroll');

    /**
     * Save and exit button
     */
    $('.btn-save').on('click', () => {
        $('input[name="done"]').val(1);
    });

    $('.jumbotron .btn-close').click(() => {
        $('.jumbotron .close').click();
    });

    $('#job-posting').on('change', 'input[name="enable_apply"]', e => {
        if (Boolean(parseInt($(e.currentTarget).val()))) {
            tinymce.get('recruitment').hide();
            $('#recruitment').attr('disabled', 'disabled').hide();

            $('input[name="email"]').removeAttr('disabled');
        }
        else {
            tinymce.get('recruitment').show();
            $('input[name="email"]').attr('disabled', 'disabled');
            $('#recruitment').removeAttr('disabled');
        }
    })
    .on('keyup', 'input[name="deadline"]', e => {
        let $this = $(e.currentTarget);
        let value = parseInt($this.val());

        if (value > 0) {
            let date = new Date();
            date.setDate(date.getDate() + value);

            $this.next('span').children('strong').text(date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate());
        }
        else {
            $this.next('span').children('strong').text('--');
        }
    })
    .on('change keyup', 'input[maxlength]', e => {
        let $this = $(e.currentTarget);
        let maxLength = $this.attr('maxlength');
        let container = $this.next('span');
        let length = maxLength - $this.val().length;

        container.children('strong').text(length);
    })
    .on('change', 'input[name="private"]', e => {
        $('#box-edit-firm, #choose-firm').toggle($(e.currentTarget).val() == 0);
        $('#box-buttons').toggle($(e.currentTarget).val() != 0);
    })
    .on('change', 'input[name="is_agency"]', e => {
        $('.agency').toggle($(e.currentTarget).val() != 1);
    })
    .on('focus', ':input', e => {
        let $this = $(e.currentTarget);
        let offset = $this.offset().top;
        let name = $this.attr('name');

        $('.sidebar-hint').hide();

        if (typeof name !== 'undefined') {
            name = name.replace('[', '').replace(']', '');

            $('#hint-' + name).fadeIn();
            offset -= $('aside').offset().top;

            $('#hint-container').css('top', offset);
        }
    });

    if ($('input[name="private"]').val()) {
        $('input[name="private"]:checked').trigger('change');
    }

    if ($('input[name="is_agency"]').val()) {
        $('input[name="is_agency"]:checked').trigger('change');
    }

    $('.benefits').on('keyup focus blur', 'input[type="text"]', e => {
        let $this = $(e.currentTarget);
        let nextItem = $this.parent().next('li');

        if ($this.val().length > 0) {
            if (!nextItem.length) {
                let clone = $this.parent().clone();
                $('input', clone).val('');

                clone.insertAfter($this.parent());
            }
        }
        else if (nextItem.length) {
            if ($('input', nextItem).val().length === 0) {
                nextItem.remove();
            }
        }
    }).on('click', 'li.clickable', e => {
        let checkbox = $(e.currentTarget).children(':checkbox');

        checkbox.prop('checked', !checkbox.is(':checked'));
        $(e.currentTarget).toggleClass('checked');
    });

    $('#upload').click(() => $('#input-file').click());

    $('#input-file').change(e => {
        let file = e.currentTarget.files[0];

        if (file.type !== 'image/png' && file.type !== 'image/jpg' && file.type !== 'image/gif' && file.type !== 'image/jpeg') {
            $('#alert').modal('show');
            $('.modal-body').text('Format pliku jest nieprawidłowy. Załącznik musi być zdjęciem JPG, PNG lub GIF');
        }
        else {
            let $form = $('#upload-form');
            let formData = new FormData($form[0]);
            let uploadBtn = $('#upload i');

            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: () => {
                    uploadBtn.removeClass('fa-upload').addClass('fa-spinner fa-spin');
                },
                success: (data) => {
                    $('#logo img').attr('src', data.url);
                    $('#job-posting').find('input[name="logo"]').val(data.name);

                    if (!$('.btn-flush').length) {
                        $('<div class="btn-flush"><i class="fa fa-remove fa-2x"></i></div>').appendTo('#logo');
                    }
                },
                complete: () => {
                    uploadBtn.removeClass('fa-spinner fa-spin').addClass('fa-upload');
                },
                error: (err) => {
                    $('#alert').modal('show');

                    if (typeof err.responseJSON !== 'undefined') {
                        $('.modal-body').text(err.responseJSON.logo[0]);
                    }
                }
            }, 'json');
        }
    });

    $('.btn-flush').on('click', () => {
        $('#job-posting').find('input[name="logo"]').val('');
        $('.btn-flush').remove();
        $('#logo img').attr('src', '/img/logo-gray.png');

        return false;
    });

    $('#btn-add-firm').click(() => {
        $.get(_config.firm_partial, {}, (html) => {
            $('#box-edit-firm').replaceWith(html);

            $('#modal-firm').modal('hide');
            initialize();
        });
    });

    /**
     * Ability to create new firm and assign it to the offer
     */
    $('#box-edit-firm').find('input[name="name"]').one('keyup', () => {
        if ($('#firm-id').val() === '') {
            return true;
        }

        $('#modal-firm').modal('show').find('.btn-primary').one('click', () => {
            $('#btn-add-firm').click();

            return false;
        });
    });

    /**
     * Ability to assign different firm to this job offer
     */
    $('.btn-firm').click(e => {
        let self = $(e.currentTarget);

        $.get(self.attr('href'), (html) => {
            $('#box-edit-firm').replaceWith(html);
            initialize();

            $('.btn-firm').not(self).removeClass('btn-primary').addClass('btn-default');
            self.addClass('btn-primary').removeClass('btn-default');

            tinymce.EditorManager.editors = [];
            initTinymce();
        });

        return false;
    });
});

function initialize() {
    'use strict';

    var mapOptions =
    {
        zoom: 6,
        center: new google.maps.LatLng(51.919438, 19.14513599999998),
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };

    var geocoder = new google.maps.Geocoder();
    var map = new google.maps.Map(document.getElementById("map"), mapOptions);
    var marker;

    var geocodeResult = function (results, status) {
        if (status === google.maps.GeocoderStatus.OK) {
            map.setCenter(results[0].geometry.location);

            if (marker) {
                marker.setMap(null);
            }

            marker = new google.maps.Marker({
                map: map,
                position: results[0].geometry.location
            });

            map.setZoom(16);
            $('#address').val(results[0].formatted_address);

            var country = '', city = '', street = '', postcode = '';
            var components = results[0].address_components;

            for (let item in components) {
                if (components.hasOwnProperty(item)) {
                    let component = components[item];

                    if (!country && component.types.indexOf('country') > -1) {
                        country = component.long_name;
                    }
                    if (!postcode && component.types.indexOf('postal_code') > -1) {
                        postcode = component.long_name;
                    }
                    if (!city && component.types.indexOf('locality') > -1) {
                        city = component.long_name;
                    }
                    if (!postcode && component.types.indexOf('route') > -1) {
                        street = component.long_name;
                    }
                }
            }

            $(':hidden[name=country]').val(country);
            $(':hidden[name=city]').val(city);
            $(':hidden[name=street]').val(street);
            $(':hidden[name=postcode]').val(postcode);
            $('#longitude').val(results[0].geometry.location.lng());
            $('#latitude').val(results[0].geometry.location.lat());
        }
    };

    var geocode = function (address) {
        geocoder.geocode({'address': address}, geocodeResult);
    };

    var reverseGeocode = function (coordinates) {
        geocoder.geocode({'latLng': coordinates}, geocodeResult);
    };

    if (!isNaN(parseFloat($('#latitude').val())) && !isNaN(parseFloat($('#longitude').val()))) {
        var coordinates = new google.maps.LatLng(parseFloat($('#latitude').val()), parseFloat($('#longitude').val()));

        marker = new google.maps.Marker({
            map: map,
            position: coordinates
        });

        map.setCenter(coordinates);
    }

    var onAddressChange = function () {
        var val = $.trim($('#address').val());

        if (val.length) {
            geocode(val);
        }
        else {
            $('#longitude, #latitude').val(0);
            $(':hidden[name=country], :hidden[name=city], :hidden[name=street], :hidden[name=postcode]').val('');

            marker.setMap(null);
        }
    };

    $('#address').keypress(function (e) {
        var code = e.keyCode || e.which;

        if (code === 13) {
            onAddressChange();
            return false;
        }
    });

    google.maps.event.addListener(map, 'click', (e) => {
        reverseGeocode(e.latLng);
    });
}

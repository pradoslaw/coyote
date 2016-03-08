$(() => {
    'use strict';

    if (typeof google !== 'undefined') {
        google.maps.event.addDomListener(window, 'load', initialize);
    }

    let navigation = $('#form-navigation');
    let fixed = $('#form-navigation-fixed');

    $('#form-navigation-container').html(navigation.html()).on('click', ':submit', () => {
        $('#job-posting').submit();
    }).on('click', 'button[data-submit-state]', (e) => {
        $(e.currentTarget).attr('disabled', 'disabled').text($(e.currentTarget).data('submit-state'));
    });

    $(window).scroll(e => {
        let bottom = $(window).scrollTop() + $(window).height();

        if (bottom > navigation.offset().top) {
            fixed.fadeOut();
        }
        else {
            fixed.show();
        }
    });

    $('input[name="private"]').change(e => {
        $('#box-edit-firm, #box-choose-firm').toggle($(e.currentTarget).val() == 0);
        $('#box-buttons').toggle($(e.currentTarget).val() != 0);
    });

    if ($('input[name="private"]').val()) {
        $('input[name="private"]:checked').trigger('change');
    }

    $('input[name="is_agency"]').change(e => {
        $('.agency').toggle($(e.currentTarget).val() != 1);
    });

    if ($('input[name="is_agency"]').val()) {
        $('input[name="is_agency"]:checked').trigger('change');
    }

    $(':input').focus(e => {
        let $this = $(e.currentTarget);
        let offset = $this.position().top;
        let name = $this.attr('name');

        name = name.replace('[', '').replace(']', '');

        $('.sidebar-hint').hide();
        $('#hint-' + name).fadeIn();

        if (!offset) {
            offset = $this.parent().position().top;
        }

        $('#hint-container').css('top', offset);
    });

    $('.jumbotron .btn-close').click(() => {
        $('.jumbotron .close').click();
    });

    $('body').on('change keyup', 'input[maxlength]', e => {
        let $this = $(e.currentTarget);
        let maxLength = $this.attr('maxlength');
        let container = $this.next('span');
        let length = maxLength - $this.val().length;

        container.children('strong').text(length);
    });

    $('input[name="deadline"]').on('keyup', e => {
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
    });

    $('input[name="enable_apply"]').change(e => {
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
    });

    $('.benefits').on('keyup focus blur', 'input[type="text"]', (e) => {
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
    }).on('click', 'li.clickable', (e) => {
        let checkbox = $(e.currentTarget).children(':checkbox');

        checkbox.prop('checked', !checkbox.is(':checked'));
        $(e.currentTarget).toggleClass('checked');
    });

    $('#upload').click(() => {
        $('#input-file').click();
    });

    $('#input-file').change((e) => {
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

tinymce.init({
    selector: "textarea",
    //height: 150,
    plugins: [
        "advlist lists spellchecker",
        "code",
        "paste"
    ],

    toolbar1: "bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | cut copy paste | bullist numlist | undo redo | outdent indent",

    menubar: false,
    toolbar_items_size: 'small',
    elementpath: false,
    statusbar: false,

    content_style: "* {font-size: 13px; font-family: Arial, sans-serif;}",

    setup: function (ed) {
        ed.on('init', function(args) {
            if ('recruitment' === args.target.id) {
                $('input[name="enable_apply"]:checked').trigger('change');
            }
        });
    }
});

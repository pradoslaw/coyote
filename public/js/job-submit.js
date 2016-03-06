'use strict';

$(function () {
    'use strict';

    if (typeof google !== 'undefined') {
        google.maps.event.addDomListener(window, 'load', initialize);
    }

    var navigation = $('#form-navigation');
    var fixed = $('#form-navigation-fixed');

    $('#form-navigation-container').html(navigation.html()).on('click', ':submit', function () {
        $('#job-posting').submit();
    }).on('click', 'button[data-submit-state]', function (e) {
        $(e.currentTarget).attr('disabled', 'disabled').text($(e.currentTarget).data('submit-state'));
    });

    $(window).scroll(function (e) {
        var bottom = $(window).scrollTop() + $(window).height();

        if (bottom > navigation.offset().top) {
            fixed.fadeOut();
        } else {
            fixed.show();
        }
    });

    $('input[name="private"]').change(function (e) {
        $('#box-edit-firm, #box-choose-firm').toggle($(e.currentTarget).val() == 0);
        $('#box-buttons').toggle($(e.currentTarget).val() != 0);
    });

    if ($('input[name="private"]').val()) {
        $('input[name="private"]:checked').trigger('change');
    }

    $('input[name="is_agency"]').change(function (e) {
        $('.agency').toggle($(e.currentTarget).val() != 1);
    });

    $(':input').focus(function (e) {
        var $this = $(e.currentTarget);
        var offset = $this.position().top;

        $('.sidebar-hint').hide();
        $('#hint-' + $this.attr('name')).fadeIn();

        if (!offset) {
            offset = $this.parent().position().top;
        }

        $('#hint-container').css('top', offset);
    });

    $('.jumbotron .btn-close').click(function () {
        $('.jumbotron .close').click();
    });

    $('body').on('change keyup', 'input[maxlength]', function (e) {
        var $this = $(e.currentTarget);
        var maxLength = $this.attr('maxlength');
        var container = $this.next('span');
        var length = maxLength - $this.val().length;

        container.children('strong').text(length);
    });

    $('input[name="deadline"]').on('keyup', function (e) {
        var $this = $(e.currentTarget);
        var value = parseInt($this.val());

        if (value > 0) {
            var date = new Date();
            date.setDate(date.getDate() + value);

            $this.next('span').children('strong').text(date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate());
        } else {
            $this.next('span').children('strong').text('--');
        }
    });

    $('input[name="enable_apply"]').change(function (e) {
        if (Boolean(parseInt($(e.currentTarget).val()))) {
            tinymce.get('recruitment').hide();
            $('#recruitment').hide();

            $('input[name="email"]').removeAttr('disabled');
        } else {
            tinymce.get('recruitment').show();
            $('input[name="email"]').attr('disabled', 'disabled');
        }
    });
});

function initialize() {
    'use strict';

    var mapOptions = {
        zoom: 6,
        center: new google.maps.LatLng(51.919438, 19.14513599999998),
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };

    var geocoder = new google.maps.Geocoder();
    var map = new google.maps.Map(document.getElementById("map"), mapOptions);
    var marker;

    var geocodeResult = function geocodeResult(results, status) {
        if (status === google.maps.GeocoderStatus.OK) {
            map.setCenter(results[0].geometry.location);

            if (marker !== null) {
                marker.setMap(null);
            }

            marker = new google.maps.Marker({
                map: map,
                position: results[0].geometry.location
            });

            map.setZoom(16);
            $('#address').val(results[0].formatted_address);

            var country = '',
                city = '',
                street = '',
                postcode = '';
            var components = results[0].address_components;

            for (var item in components) {
                if (components.hasOwnProperty(item)) {
                    var component = components[item];

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

    var geocode = function geocode(address) {
        geocoder.geocode({ 'address': address }, geocodeResult);
    };

    var reverseGeocode = function reverseGeocode(coordinates) {
        geocoder.geocode({ 'latLng': coordinates }, geocodeResult);
    };

    if (!isNaN(parseFloat($('#latitude').val())) && !isNaN(parseFloat($('#longitude').val()))) {
        var coordinates = new google.maps.LatLng(parseFloat($('#latitude').val()), parseFloat($('#longitude').val()));

        marker = new google.maps.Marker({
            map: map,
            position: coordinates
        });

        map.setCenter(coordinates);
    }

    var onAddressChange = function onAddressChange() {
        var val = $.trim($('#address').val());

        if (val.length) {
            geocode(val);
        } else {
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

    google.maps.event.addListener(map, 'click', function (e) {
        reverseGeocode(e.latLng);
    });
}

tinymce.init({
    selector: "textarea",
    //height: 150,
    plugins: ["advlist lists spellchecker", "code", "paste"],

    toolbar1: "bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | cut copy paste | bullist numlist | undo redo | outdent indent",

    menubar: false,
    toolbar_items_size: 'small',
    elementpath: false,
    statusbar: false,

    content_style: "* {font-size: 13px; font-family: Arial, sans-serif;}",

    setup: function setup(ed) {
        ed.on('init', function (args) {
            if ('recruitment' === args.target.id) {
                $('input[name="enable_apply"]:checked').trigger('change');
            }
        });
    }
});
//# sourceMappingURL=job-submit.js.map
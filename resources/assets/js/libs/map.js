
class Map {
    constructor(container = 'map') {
        let mapOptions = {
            zoom: 6,
            center: new google.maps.LatLng(51.919438, 19.14513599999998),
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };

        this._geocoder = new google.maps.Geocoder();
        this._map = new google.maps.Map(document.getElementById(container), mapOptions);
    }

    geocode(address, cb) {
        this._geocoder.geocode({'address': address}, (result, status) => {
            cb(this._geocodeResult(result, status));
        });
    }

    reverseGeocode(coordinates, cb) {
        this._geocoder.geocode({'latLng': coordinates}, (result, status) => {
            cb(this._geocodeResult(result, status));
        });
    }

    addMarker(latitude, longitude) {
        if (!latitude || !longitude) {
            return null;
        }

        let coordinates = new google.maps.LatLng(latitude, longitude);

        let marker = new google.maps.Marker({
            map: this._map,
            position: coordinates
        });

        this._map.setCenter(coordinates);

        return marker;
    }

    removeMarker(marker) {
        if (marker !== null) {
            marker.setMap(null);
        }
    }

    setupGeocodeOnMapClick(cb) {
        google.maps.event.addListener(this._map, 'click', e => {
            this.reverseGeocode(e.latLng, cb);
        });
    }

    _geocodeResult(result, status) {
        let data = {
            location: null,
            latitude: null,
            longitude: null,
            country: null,
            city: null,
            street: null,
            postcode: null,
            address: null,
            house: null
        };

        if (status === google.maps.GeocoderStatus.OK) {
            data.location = result[0].geometry.location;
            data.address = result[0].formatted_address;

            data.longitude = data.location.lng();
            data.latitude = data.location.lat();

            let components = result[0].address_components;

            for (let item in components) {
                if (components.hasOwnProperty(item)) {
                    let component = components[item];

                    if (!data.country && component.types.indexOf('country') > -1) {
                        data.country = component.long_name;
                    }
                    if (!data.postcode && component.types.indexOf('postal_code') > -1) {
                        data.postcode = component.long_name;
                    }
                    if (!data.city && component.types.indexOf('locality') > -1) {
                        data.city = component.long_name;
                    }
                    if (!data.postcode && component.types.indexOf('route') > -1) {
                        data.street = component.long_name;
                    }
                    if (!data.street_number && component.types.indexOf('street_number') > -1) {
                        data.house = component.long_name;
                    }
                }
            }
        }

        return data;
    }
}

export default Map;

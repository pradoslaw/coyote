
class Geocoder {
    constructor() {
        this._geocoder = new google.maps.Geocoder();
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
            street_number: null
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
                        data.street_number = component.long_name;
                    }
                }
            }
        }

        return data;
    }
}

export default Geocoder;

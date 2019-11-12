<template>
    <div :id="id" ref="map">
        <slot></slot>
    </div>
</template>

<script>
    export default {
        props: {
            lat: String,
            lng: String
        },
        provide: function () {
            return {
                getMap: this.getMap
            }
        },
        data() {
            return {
                map: null
            }
        },
        beforeMount() {
            this.id = 'map-'  + Math.random();
        },
        mounted() {
            let mapOptions = {
                zoom: 16,
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                center: this.lat && this.lng ? new google.maps.LatLng(this.lat, this.lng) : null
            };

            this.map = new google.maps.Map(document.getElementById(this.id), mapOptions);
        },
        methods: {
            getMap(cb) {
                let waitForMap  = () => {
                    if (this.map) {
                        cb(this.map)
                    } else {
                        setTimeout(waitForMap, 50)
                    }
                };

                waitForMap();
            }
        }
    }
</script>

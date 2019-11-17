<template>
    <div :id="id" ref="map">
        <slot></slot>
    </div>
</template>

<script>
    export default {
        props: {
            latitude: [Number, String],
            longitude: [Number, String]
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
                center: this.latitude && this.longitude ? new google.maps.LatLng(this.latitude, this.longitude) : null
            };

            this.map = new google.maps.Map(document.getElementById(this.id), mapOptions);

            google.maps.event.addListener(this.map, 'click', e => {
                this.$emit('click', e.latLng);
            });
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

import Vue from 'vue';
import VueJob from '../../components/job.vue';

new Vue({
    el: '#page-job',
    delimiters: ['${', '}'],
    components: {
        'vue-job': VueJob
    },
    data: window.data,
    created: function () {

    },
    mounted: function () {

    },
    methods: {

    },
    computed: {

    }
});

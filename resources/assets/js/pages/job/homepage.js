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
        toggleTag: function (tag) {
            const index = this.input.tags.indexOf(tag.name);

            if (index > -1) {
                this.input.tags.splice(index, 1);
            }
            else {
                this.input.tags.push(tag.name);
            }
        }
    },
    computed: {

    }
});

import Vue from 'vue';
import VueJob from '../../components/job.vue';
import VuePagination from '../../components/pagination.vue';

new Vue({
    el: '#page-job',
    delimiters: ['${', '}'],
    components: {
        'vue-job': VueJob,
        'vue-pagination': VuePagination
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
        },

        changePage: function () {

        }
    },
    computed: {

    }
});

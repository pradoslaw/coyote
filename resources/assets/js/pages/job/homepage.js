import Vue from 'vue';
import Config from '../../libs/config';
import VueJob from '../../components/job.vue';
import VuePagination from '../../components/pagination.vue';
import axios from 'axios';
import store from "../../store";

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
        axios.defaults.headers.common['X-CSRF-TOKEN'] = Config.csrfToken();
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

            this.search();
        },

        changePage: function () {

        },

        search: function () {

            const input = {
                q: this.input.q,
                city: this.input.city,
                tags: this.input.tags,
                sort: this.input.sort,
                page: this.input.page,
                salary: this.input.salary,
                currency: this.input.currency
            };

            axios.get(this.$refs.searchForm.action, {params: input})
                .then(response => {
                    window.history.pushState(input, '', response.request.responseURL);
                    this.jobs = response.data.jobs;

                    console.log(response);
                })
                .catch(error => {
                    console.log(error);
                });
        }
    },
    computed: {
        defaultSort: {
            get: function () {
                return this.input.sort ? this.input.sort : this.default.sort;
            },
            set: function (value) {
                this.input.sort = value;
            }
        },

        defaultCurrency: {
            get: function () {
                return this.input.currency ? this.input.currency : this.default.currency;
            },
            set: function (value) {
                this.input.currency = value;
            }
        }
    }
});

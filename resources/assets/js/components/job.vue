<template>
    <div :class="{'highlight': job.is_highlight}" class="panel panel-default panel-job margin-md-bottom">
        <span v-if="order === 0 && job.is_on_top" class="label top-spot-badge hidden-xs">Promowane</span>

        <div class="panel-body">
            <div class="media">
                <div class="media-left hidden-xs">
                    <a :href="job.url">
                        <object :data="job.firm.logo || '//'" type="image/png" class="media-object margin-sm-right" >
                            <img src="/img/logo-gray.png" :alt="job.firm.logo ? job.firm.name : ''">
                        </object>
                    </a>
                </div>

                <div class="media-body">
                    <h4 class="media-heading pull-left"><a :href="job.url">{{ job.title }}</a></h4>

                    <a v-if="job.is_medal" :href="job.url" class="medal hidden-xs" title="Oferta na medal. To odznaczenie przyznawane jest ofertom, które zawierają szczegółowe informacje o pracy"></a>

                    <vue-salary
                        :salary_from="job.salary_from"
                        :salary_to="job.salary_to"
                        :currency_symbol="job.currency_symbol"
                        :rate="job.rate"
                        :is_gross="job.is_gross"
                        :options="{'class': 'pull-right'}"
                    >
                    </vue-salary>

                    <p class="padding-xs-bottom">
                        <a class="employer" :title="'Zobacz oferty pracy z ' + job.firm.name" :href="job.firm.url">{{ job.firm.name }}</a>

                        <vue-location :locations="job.locations" :remote="job.remote"></vue-location>
                    </p>

                    <span v-if="job.is_new" class="label label-new pull-right margin-sm-top">Nowe</span>
                    <small v-else class="text-muted pull-right">{{ job.boost_at }}</small>

                    <ul class="tag-clouds tag-clouds-sm tag-clouds-skills margin-md-top">
                        <li v-for="tag in limitedTags">
                            <a :href="tag.url" :title="'Znajdź oferty zawierające ' + tag.name">
                                <img v-if="tag.logo" :alt="tag.name" :src="tag.logo">

                                {{ tag.real_name || tag.name }}
                            </a>
                        </li>
                    </ul>

                    <ul class="list-inline job-options margin-sm-top">
                        <li><a @click="subscribe()" href="javascript:"><i :class="{'fas fa-heart on': isSubscribed, 'far fa-heart': !isSubscribed}" class="fa-fw"></i> Ulubiona</a></li>
                        <li><a :href="job.url + '#comments'"><i class="far fa-fw fa-comment"></i> {{ job.comments_count }} {{ commentsDeclination }}</a></li>
                        <!--<li><a href="#"><i class="fa fa-fw fa-share"></i> Udostępnij</a></li>-->
                    </ul>

                </div>
            </div>
        </div>

        <vue-modal ref="error">
            Musisz się zalogować, aby dodać tę ofertę do ulubionych.
        </vue-modal>
    </div>
</template>

<script>
    import VueSalary from './salary.vue';
    import VueLocation from './location.vue';
    import declination from '../components/declination';

    import VueModal from './modal.vue';

    export default {
        props: {
            job: {
                type: Object,
                required: true
            },
            order: {
                type: Number
            }
        },
        components: {
            'vue-salary': VueSalary,
            'vue-location': VueLocation,
            'vue-modal': VueModal
        },
        data() {
            return {
                error: ''
            }
        },
        methods: {
            subscribe: function () {
                this.$store.dispatch('subscriptions/toggle', this.job).catch(() => {
                    this.$refs.error.open();

                    // change button status in case of any error
                    this.$store.commit('subscriptions/pop', this.$store.getters['subscriptions/exists'](this.job));
                });
            }
        },
        computed: {
            commentsDeclination: function () {
                return declination(this.job.comments_count, ['komentarz', 'komentarze', 'komentarzy']);
            },

            limitedTags: function () {
                return this.job.tags.slice(0, 5);
            },

            isSubscribed () {
                return this.$store.getters['subscriptions/exists'](this.job) !== -1;
            }
        }
    }
</script>

<template>
    <div class="panel panel-default panel-job margin-md-bottom">
        <div class="panel-body">
            <div class="media">
                <div class="media-left">
                    <a :href="job.url"><img :alt="job.firm.name" class="media-object margin-sm-right" :src="job.firm.logo"></a>
                </div>

                <div class="media-body">
                    <h4 class="media-heading"><a :href="job.url">{{ job.title }}</a></h4>

                    <a v-if="job.is_medal" :href="job.url" class="medal hidden-xs" title="Oferta na medal. To odznaczenie przyznawane jest ofertom, które zawierają szczegółowe informacje o pracy"></a>

                    <vue-salary
                        :salary_from="job.salary_from"
                        :salary_to="job.salary_to"
                        :currency_symbol="job.currency_symbol"
                        :rate="job.rate"
                        :isGross="job.is_gross"
                        :options="{'class': 'pull-right'}"
                    >
                    </vue-salary>

                    <p class="padding-sm-bottom">
                        <a class="employer" :title="'Zobacz oferty pracy z ' + job.firm.name" :href="job.firm.url">{{ job.firm.name }}</a>

                        <vue-location :locations="job.locations" :remote="job.remote"></vue-location>
                    </p>

                    <span v-if="job.is_new" class="label label-new pull-right margin-sm-top">Nowe</span>
                    <small v-else class="text-muted pull-right">{{ job.boost_at }}</small>

                    <ul class="tag-clouds tag-clouds-sm tag-clouds-skills margin-md-top">
                        <li v-for="tag in job.tags">
                            <a :href="tag.url" :title="'Znajdź oferty zawierające ' + tag.name">
                                <img v-if="tag.logo" :alt="tag.name" :src="tag.logo">

                                {{ tag.real_name ? tag.real_name : tag.name }}
                            </a>
                        </li>
                    </ul>

                    <ul class="list-inline job-options margin-sm-top">
                        <li><a href="#"><i :class="{'fa-heart on': job.subscribe_on, 'fa-heart-o': !job.subscribe_on}" class="fa fa-fw"></i> Ulubiona</a></li>
                        <li><a href="#"><i class="fa fa-fw fa-comments-o"></i> 0 komentarzy</a></li>
                        <li><a href="#"><i class="fa fa-fw fa-share"></i> Udostępnij</a></li>
                    </ul>

                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import VueSalary from './salary.vue';
    import VueLocation from './location.vue';

    export default {
        props: ['job'],
        components: {
            'vue-salary': VueSalary,
            'vue-location': VueLocation
        }
    }
</script>

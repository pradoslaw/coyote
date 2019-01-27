<template>
    <div>
        <div id="plan-table" class="clearfix margin-lg-top margin-lg-bottom">
            <ul class="plan-benefits">
                <li><div>Publikacja ogłoszenia na okres <strong>40 dni</strong></div></li>
                <li>
                    <div>
                        <strong>Reklama</strong> oferty na forum i stronie głównej

                        <a href="javascript:" class="plan-tooltip-wrapper">
                            <i class="fa fa-question-circle"></i>

                            <div class="plan-tooltip"><img src="/img/offer-example.jpg"></div>
                        </a>
                    </div>
                </li>
                <li><div><strong>Wyróżnienie</strong> kolorem <i class="fa fa-question-circle" data-toggle="tooltip" title="Twoje ogłoszenie otrzyma dodatkowe tło, które odróżni je od standardowych ogłoszeń na liście."></i></div></li>
                <li><div>Wyróżnienie ogłoszenia <strong>na górze listy</strong> wyszukiwania</div></li>
                <li><div><strong>3x podbicie</strong> ogłoszenia <i class="fa fa-question-circle" data-toggle="tooltip" title="W okresie promowania oferty, 3 razy podbijemy Twoje ogłoszenie na górę listy. Dzięki temu więcej ludzi będzie mogło je zobaczyć."></i></div></li>
            </ul>

            <div class="plan" v-for="plan in plans" :class="{'selected': vModel == plan.id}">
                <div class="plan-header" @click="changePlan(plan.id)">
                    <h4 class="plan-name">Ogłoszenie<br><strong>{{ plan.name }}</strong></h4>

                    <div class="plan-price"><strong>{{ plan.price - (plan.discount > 0 ? (plan.price * plan.discount) : 0) }} zł</strong></div>
                    <div class="plan-price-old" v-if="plan.discount > 0"><strong>{{ plan.price }} zł</strong></div>
                </div>

                <div class="plan-body">
                    <ul class="plan-features">
                        <li v-for="n in 5"><i class="fa fa-fw" :class="{'fa-check-circle': plan.benefits.length >= n, 'text-primary': plan.benefits.length >= n, 'fa-remove': plan.benefits.length < n, 'text-muted': plan.benefits.length < n}"></i></li>

                        <li class="feature-button">
                            <button class="btn btn-default" v-if="vModel != plan.id" @click.prevent="changePlan(plan.id)">Wybierz</button>
                            <span class="text-primary" v-if="vModel == plan.id"><i class="fa fa-check-circle-o fa-fw text-primary"></i> Wybrano</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="custom-plan clearfix text-center margin-lg-bottom">

            <h3>Potrzebujesz kilku ogłoszeń?</h3>

            <div class="custom-plan-container">
                <i class="fa fa-info-circle fa-2x pull-left"></i>

                <div class="pull-left margin-xs-left custom-plan-info">
                    Napisz do nas na adres:<br>
                    <a :href="'mailto:' + email + '?subject=Interesuje mnie pakiet ogłoszeń. Proszę o przesłanie szczegółów'">{{ email }}</a>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            plans: {
                type: Array
            },
            vModel: {
                type: Number
            },
            email: {
                type: String
            }
        },
        methods: {
            changePlan: function (planId) {
                this.vModel = planId;
            }
        },
        watch: {
            vModel: function() {
                this.$emit('update:vModel', this.vModel);
            }
        }
    }

</script>

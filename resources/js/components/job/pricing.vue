<template>
  <div>
    <div id="plan-table" class="clearfix mt-4 mb-4">
      <ul class="plan-benefits">
        <li>
          <div>Publikacja ogłoszenia na okres <strong>40 dni</strong></div>
        </li>
        <li>
          <div>
            Promocja ogłoszenia w kanałach social media

            <span data-balloon-pos="up" aria-label="Jedynie ogłoszenia z podanymi widełkami płacowymi"><i class="fa fa-circle-question"></i></span>
          </div>
        </li>
        <li>
          <div>
            <strong>Podbicie</strong> ogłoszenia

            <span data-balloon-pos="right" aria-label="W okresie promowania oferty, podbijemy Twoje ogłoszenie na górę listy ogłoszeń. Dzięki temu więcej ludzi będzie mogło je zobaczyć."><i class="fa fa-circle-question"></i></span>
          </div>
        </li>

        <li>
          <div>
            <strong>Reklama</strong> oferty na forum i stronie głównej

            <a href="javascript:" class="plan-tooltip-wrapper">
              <i class="fa fa-circle-question"></i>

              <div class="plan-tooltip"><img src="/img/offer-example.jpg"></div>
            </a>
          </div>
        </li>
        <li>
          <div>
            <strong>Wyróżnienie</strong> kolorem

            <a href="javascript:" class="plan-tooltip-wrapper">
              <i class="fa fa-circle-question"></i>

              <div class="plan-tooltip"><img src="/img/offer-color-example.png"></div>
            </a>
          </div>
        </li>
        <li>
          <div>Wyróżnienie ogłoszenia <strong>na górze listy</strong> wyszukiwania</div>
        </li>
      </ul>

      <div class="plan" v-for="plan in plans" :class="{'selected': valueLocal === plan.id}" @click="changePlan(plan.id)">
        <div class="plan-header">
          <h4 class="plan-name">Ogłoszenie<br><strong>{{ plan.name }}</strong></h4>

          <div class="plan-price"><strong>{{ plan.price - (plan.discount > 0 ? (plan.price * plan.discount) : 0) }} zł</strong></div>
          <div class="plan-price-old" v-if="plan.discount > 0"><strong>{{ plan.price }} zł</strong></div>
        </div>

        <div class="plan-body">
          <ul class="plan-features">
            <li v-for="n in 6">
              <strong v-if="plan.benefits[n - 1] === 'is_boost'" class="text-muted">{{ plan.boost }}x</strong>
              <i v-else class="fa fa-fw" :class="{'fa-circle-check': plan.benefits.length >= n, 'text-primary': plan.benefits.length >= n, 'fa-remove': plan.benefits.length < n, 'text-muted': plan.benefits.length < n}"></i>
            </li>

            <li class="feature-button">
              <button class="btn btn-secondary" v-if="valueLocal != plan.id" @click.prevent="changePlan(plan.id)">Wybierz</button>
              <span class="text-primary" v-if="valueLocal == plan.id"><i class="fa fa-circle-check fa-fw text-primary"></i> Wybrano</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {default as mixins} from '../mixins/form';

export default {
  props: {
    plans: {
      type: Array
    },
    value: {
      type: Number
    },
    email: {
      type: String
    }
  },
  methods: {
    changePlan(planId) {
      this.valueLocal = planId;
    }
  },
  mixins: [mixins]
}

</script>

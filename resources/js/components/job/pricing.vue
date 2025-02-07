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
            <span data-balloon-pos="up" aria-label="Jedynie ogłoszenia z podanymi widełkami płacowymi">
              <vue-icon name="pricingHelpExample"/>
            </span>
          </div>
        </li>
        <li>
          <div>
            <strong>Podbicie</strong> ogłoszenia
            <span data-balloon-pos="right" aria-label="W okresie promowania oferty, podbijemy Twoje ogłoszenie na górę listy ogłoszeń. Dzięki temu więcej ludzi będzie mogło je zobaczyć.">
              <vue-icon name="pricingHelpExample"/>
            </span>
          </div>
        </li>
        <li>
          <div>
            <strong>Reklama</strong> oferty na forum i stronie głównej
            <a href="javascript:" class="plan-tooltip-wrapper">
              <vue-icon name="pricingHelpExample"/>
              <div class="plan-tooltip">
                <img src="/img/offer-example.jpg">
              </div>
            </a>
          </div>
        </li>
        <li>
          <div>
            <strong>Wyróżnienie</strong> kolorem
            <a href="javascript:" class="plan-tooltip-wrapper">
              <vue-icon name="pricingHelpExample"/>
              <div class="plan-tooltip">
                <img src="/img/offer-color-example.png">
              </div>
            </a>
          </div>
        </li>
        <li>
          <div>Wyróżnienie ogłoszenia <strong>na górze listy</strong> wyszukiwania</div>
        </li>
      </ul>
      <div class="plan" v-for="(plan, index) in plans" :class="{'selected': index === 1}">
        <div class="plan-header">
          <h4 class="plan-name">
            Ogłoszenie
            <br>
            <strong>{{ plan.name }}</strong>
          </h4>
          <div class="plan-price">
            <strong>{{ plan.price - (plan.discount > 0 ? (plan.price * plan.discount) : 0) }} zł</strong>
          </div>
          <div class="plan-price-old" v-if="plan.discount > 0">
            <strong>{{ plan.price }} zł</strong>
          </div>
        </div>

        <div class="plan-body">
          <ul class="plan-features">
            <li v-for="n in 6">
              <strong v-if="plan.benefits[n - 1] === 'is_boost'" class="text-muted">
                {{ plan.boost }}x
              </strong>
              <template v-else>
                <span v-if="plan.benefits.length >= n" class="text-primary">
                  <vue-icon name="pricingBenefitPresent"/>
                </span>
                <span v-else class="text-muted">
                  <vue-icon name="pricingBenefitMissing"/>
                </span>
              </template>
            </li>
            <li class="feature-button">
              <button type="button" class="btn btn-primary" @click="selectPlan(plan.id)">
                Wybierz plan
              </button>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {postJobBoardMilestone} from "../../../feature/jobBoard/jobBoard";
import VueIcon from '../icon';

export default {
  components: {VueIcon},
  props: {
    plans: {type: Array},
    modelValue: {type: Number},
  },
  model: {
    prop: 'modelValue',
    event: 'update:modelValue',
  },
  methods: {
    selectPlan(planId) {
      this.$emit('select', planId);
      postJobBoardMilestone('select-plan-' + planId);
    },
  },
};
</script>

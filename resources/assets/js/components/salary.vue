<template>
    <p class="salary" :class="options.class">
        <strong class="text-primary">
            <template v-if="salary_from && salary_to">
                {{ salary_from }} - {{ salary_to }}
            </template>

            <template v-else-if="salary_from">
                od {{ salary_from }}
            </template>

            <template v-else-if="salary_to">
                do {{ salary_to }}
            </template>

            <template v-if="salary_from || salary_to">
                {{ currency_symbol }}
            </template>
        </strong>

        <small v-if="salary_from && salary_to" class="text-muted">{{ is_gross ? 'brutto' : 'netto' }}</small>
        <small v-if="rate" class="text-muted">{{ rate }}</small>
    </p>
</template>

<script>
    export default {
        props: {
            salary_from: {
                type: String
            },
            salary_to: {
                type: String
            },
            currency_symbol: {
                type: String
            },
            rate: {
                type: String
            },
            is_gross: {
                type: Boolean
            },
            options: {
                type: Object,
                default: () => {
                    return {};
                }
            }
        }
    }
</script>

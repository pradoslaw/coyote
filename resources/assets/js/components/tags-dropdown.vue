<template>
    <div>
        <input
                type="text"
                class="form-control"
                :id="id"
                placeholder="Np. Java, MS-SQL"
                autocomplete="off"
                v-model="vModel"
                @click="onFocus"
                @focus="onFocus"
                @keyup.up="onUp"
                @keyup.down="onDown"
                @keyup.esc="onEsc"
                @keyup.enter.prevent="onEnter"
        >
        <span class="fa fa-tag form-control-feedback" aria-hidden="true"></span>

        <ol class="tag-dropdown" v-show="isDropdownShown">
            <li v-for="(count, name, index) in tags" v-show="vModel === '' || name.startsWith(vModel)" :class="{'hover': index === hoverIndex}" @click="onClick(name)" @mouseover="onMouseOver(index)">
                <span>{{ name }}</span>
                <small>×{{ count }}</small>
            </li>

            <li v-show="vModel !== ''" @click="onClick(vModel)">
                <small>Dodaj... </small> <span>{{ vModel }}</span>
            </li>
        </ol>
    </div>
</template>

<script>
    export default {
        props: {
            tags: {
                type: Object
            },
            id: {
                type: String
            }
        },
        data: function() {
            return {
                isDropdownShown: false,
                hoverIndex: -1,
                vModel: ''
            }
        },
        methods: {
            onFocus: function () {
                this.isDropdownShown = true;
            },
            onDown: function () {
                this.hoverIndex += 1;
            },
            onUp: function () {
                this.hoverIndex -= 1;
            },
            onEsc: function () {
                this.isDropdownShown = false;
                this.vModel = '';
            },
            onEnter: function () {
                this.onClick(this.vModel);
            },
            onClick: function (name) {
                this.onEsc();

                name = name
                    .trim()
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/'/g, "&#39;")
                    .replace(/"/g, "&#34;")
                    .toLowerCase()
                    .replace(/\//g, '')
                    .replace(/ /g, '-')
                    .replace(/[^a-ząęśżźćółń0-9\-\.#\+\s]/gi, '');

                if (name.startsWith('#')) {
                    name = name.substr(1);
                }

                this.$emit('change', name);
            },
            onMouseOver: function (index) {
                this.hoverIndex = index;
            }
        },
        computed: {
        }
    }

</script>

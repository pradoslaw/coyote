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
                @keyup="onKeyUp"
                @keydown.enter.prevent="onEnter"
        >
        <span class="fa fa-tag form-control-feedback" aria-hidden="true"></span>

        <ol class="tag-dropdown" v-show="isDropdownShown">
            <li v-for="(name, index) in tags" v-show="vModel === '' || name.startsWith(vModel)" :class="{'hover': index === hoverIndex}" @click="onClick(name)" @mouseover="onMouseOver(index)">
                <span>{{ name }}</span>
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
        mounted: function () {
            document.body.addEventListener('click', event => {
                if (!(this.$el === event.target || this.$el.contains(event.target))) {
                    this.isDropdownShown = false;
                }
            });
        },
        methods: {
            onFocus: function () {
                this.isDropdownShown = true;
            },
            onDown: function () {
                this.isDropdownShown = true;
                this.hoverIndex += 1;
            },
            onUp: function () {
                this.hoverIndex -= 1;
            },
            onEsc: function () {
                this._hide();
            },
            onEnter: function () {
                if (this.hoverIndex === -1) {
                    this._addTag(this.vModel);
                }
                else {
                    this._addTag(this.tags[this.hoverIndex]);
                }
            },
            onKeyUp: function (e) {
                if (e.key !== 'Enter') {
                    this.isDropdownShown = true; // show dropdown while typing ...
                }
            },
            onClick: function (name) {
                this._addTag(name);
            },
            onMouseOver: function (index) {
                this.hoverIndex = index;
            },
            _addTag: function (name) {
                this._hide();

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
            _hide: function () {
                this.isDropdownShown = false;
                this.vModel = '';
                this.hoverIndex = -1;
            }
        },
        computed: {
        }
    }

</script>

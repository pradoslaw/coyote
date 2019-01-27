<template>
    <li>
        <a href="javascript:" @click="onDelete(tag.name)" class="remove">{{ tag.name }}</a>

        <div style="display: inline" @mouseenter="editable = true" @mouseleave="finishEditing">
            <i v-for="i in [0, 1, 2]" class="fa fa-circle" :title="tooltips[i]" :class="{'text-primary': getHighlight(tag.pivot.priority) >= i, 'text-muted': getHighlight(tag.pivot.priority) < i}" @mouseover="highlight = i" @click="setPriority(i)" data-toggle="tooltip"></i>
        </div>
    </li>
</template>

<script>
    export default {
        props: {
            tag: {
                type: Object
            },
            tooltips: {
                type: Array
            }
        },
        data: function() {
            return {
                editable: false,
                highlight: null
            }
        },
        methods: {
            getHighlight: function ( _default) {
                return this.highlight !== null ? this.highlight : _default;
            },
            setPriority: function (priority) {
                this.tag.pivot.priority = priority;
                this.finishEditing();
            },
            finishEditing: function () {
                this.editable = false;
                this.highlight = null;
            },
            onDelete: function (name) {
                this.$emit('delete', name);
            }
        }
    }

</script>

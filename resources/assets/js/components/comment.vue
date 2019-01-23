<template>
    <div :id="'comment-' + comment.id" class="comment">
        <div class="media">
            <div class="media-left">
                <img :src="comment.user.photo" class="img-thumbnail media-object">
            </div>

            <div class="media-body">
                <div class="dropdown pull-right">
                    <button class="btn btn-default btn-xs dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="caret"></span>
                    </button>

                    <ul class="dropdown-menu dropdown-menu-right" v-if="comment.editable">
                        <li><a @click="edit" href="javascript:" class="btn-edit" :data-id="comment.id"><i class="fa fa-edit fa-fw"></i> Edytuj</a></li>
                        <li><a v-on:click="remove" href="javascript:" :data-target="'#modal-confirm' + comment.id" data-toggle="modal"><i class="fa fa-remove fa-fw"></i> Usuń</a></li>
                    </ul>
                </div>

                <div class="media-heading">
                    <h5><a :href="comment.user.profile" :data-user-id="comment.user.id">{{ comment.user.name }}</a></h5>

                    <h6><a :href="'#comment-' + comment.id" class="text-muted timestamp" :data-timestamp="comment.timestamp">{{ comment.created_at }}</a></h6>
                </div>

                <div class="margin-sm-top margin-sm-bottom" v-if="!isEditing">
                    {{ comment.html }}
                </div>

                <div class="margin-sm-top" v-if="isEditing">
                    <form method="post" :action="comment.route.edit" v-on:submit.prevent="submitForm">
                        <div class="form-group">
                            <textarea name="text" class="form-control">{{ comment.text}}</textarea>
                        </div>

                        <div class="form-group">
                            <input type="submit" class="btn btn-primary btn-sm pull-right">
                        </div>
                    </form>
                </div>

                <ul class="list-inline">
                    <li><a @click="reply" href="javascript:" class="text-muted">Odpowiedz</a></li>
                    <li><a href="#" class="text-muted">Zgłoś</a></li>
                </ul>
            </div>
        </div>

        <div class="media" v-if="isReplying">
            <form method="post" :action="comment.route.reply" v-on:submit.prevent="replyForm">
                <input type="hidden" name="parent_id" :value="parentId">

                <div class="form-group">
                    <textarea name="text" class="form-control" ref="text"></textarea>
                </div>

                <div class="form-group">
                    <input type="submit" class="btn btn-primary btn-sm pull-right">
                </div>
            </form>
        </div>

        <vue-comment
            v-for="child in comment.children"
            v-if="!child.deleted_at"
            :comment="child"
            :key="child.id"
            :nested="true"
            @reply="onReply"
        ></vue-comment>
    </div>
</template>

<script>
    import axios from 'axios';

    export default {
        name: 'vue-comment', // required with recursive component
        props: ['comment', 'nested'],
        data: () => {
            return {
                isEditing: false,
                isReplying: false
            }
        },
        methods: {
            edit: function () {
                this.isEditing = !this.isEditing;
            },

            reply: function () {
                this.isReplying = !this.isReplying;

                if (this.isReplying) {
                    this.$nextTick(function() {
                        this.$refs.text.focus();
                    });
                }
            },

            remove: function () {
                axios.delete(this.comment.route.delete).then(() => {
                    this.comment.deleted_at = new Date();
                });
            },

            submitForm: function (e) {
                axios.post(e.target.action, new FormData(e.target))
                    .then(response => {
                        this.comment = response.data;
                        this.isEditing = false;
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
            },

            replyForm: function (e) {
                axios.post(e.target.action, new FormData(e.target))
                    .then(response => {
                        if (!this.nested) {
                            this.onReply(response.data);
                        }

                        this.$emit('reply', response.data);
                        this.isReplying = false;
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
            },

            onReply: function (data) {
                this.comment.children.push(data);

                this.$nextTick(function() {
                    let el = document.getElementById(`comment-${data.id}`);
                    el.scrollIntoView();
                });

            }

        },
        computed: {
            parentId () {
                return this.comment.parent_id ? this.comment.parent_id : this.comment.id;
            }
        }
    }

</script>

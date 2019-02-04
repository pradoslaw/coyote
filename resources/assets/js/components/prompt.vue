<template>
    <div>
        <slot></slot>

        <ul ref="list" class="auto-complete" v-show="isListShown">
            <li v-for="(user, index) in result" :class="{hover: index === selectedIndex}" @click="onClick" @mouseover="onMouseOver(index)">
                <img :src="user.photo" class="avatar-placeholder">
                <span>{{ user.name }}</span>

                <small v-if="user.group" class="label label-default">{{ user.group }}</small>
            </li>
        </ul>
    </div>
</template>

<script>
    import axios from 'axios';

    const ESC = 27;
    const ENTER = 13;
    const UP = 40;
    const DOWN = 38;

    export default {
        props: ['source'],
        data() {
            return {
                input: null,
                result: [],
                timerId: null,
                selectedIndex: 0
            }
        },
        mounted() {
            this.input = this.$slots.default[0].elm;

            this.input.addEventListener('keyup', this.onKeyUp);
            this.input.addEventListener('click', this.onKeyUp); // bind one listener to two events
            this.input.addEventListener('keydown', this.onKeyDown);

            document.addEventListener("click", e => {
                let target = e.target;

                do {
                    if (target === this.$refs.list) {
                        return;
                    }

                    target = target.parentNode;
                } while (target);

                this.result = [];
            });
        },
        computed: {
            isListShown: function () {
                return this.result.length;
            }
        },
        methods: {
            onClick: function () {
                const caretPosition = this.getCaretPosition();
                const startIndex = this.getUserNamePosition(caretPosition);

                this.applySelected(startIndex, caretPosition);
            },

            onMouseOver: function (index) {
                this.selectedIndex = index;
            },

            onKeyUp: function (e) {
                let userName = '';

                const keyCode = e.keyCode;
                const caretPosition = this.getCaretPosition();
                const startIndex = this.getUserNamePosition(caretPosition);

                if (keyCode === ESC) {
                    this.result = [];

                    return;
                } else if (keyCode === DOWN) {
                    this.down();

                    return;
                } else if (keyCode === UP) {
                    this.up();

                    return;
                } else if (keyCode === ENTER) {
                    if (this.isListShown) {
                        this.applySelected(startIndex, caretPosition);
                    }

                    return;
                }

                if (startIndex > -1) {
                    userName = this.input.value.substr(startIndex, caretPosition - startIndex);
                }
                this.lookup(userName);
            },

            onKeyDown: function (e) {
                if (this.isListShown && [ESC, ENTER, UP, DOWN].indexOf(e.keyCode) !== -1) {
                    e.preventDefault();
                }
            },

            getCaretPosition: function () {
                if (this.input.selectionStart || this.input.selectionStart === 0) {
                    return this.input.selectionStart;
                }
                else if (document.selection) {
                    this.input.focus();
                    let sel = document.selection.createRange();

                    sel.moveStart('character', -this.input.value.length);
                    return (sel.text.length);
                }
            },

            getUserNamePosition: function (caretPosition) {
                let i = caretPosition;
                let result = -1;

                while (i > caretPosition - 50 && i >= 0) {
                    let $val = this.input.value[i];

                    if ($val === ' ') {
                        break;
                    }
                    else if ($val === '@') {
                        if (i === 0 || this.input.value[i - 1] === ' ' || this.input.value[i - 1] === "\n") {
                            result = i + 1;
                            break;
                        }
                    }
                    i--;
                }

                return result;
            },

            lookup: function (text) {
                if (text.length >= 2) {
                    clearTimeout(this.timerId);

                    this.timerId = setTimeout(() => {
                        axios.get(this.source, {params: {q: text, json: 1}})
                            .then(response => {
                                this.result = response.data;
                            })
                            .catch(error => {
                                console.log(error);
                            });
                    }, 200);

                    this.selectedIndex = 0;

                    this.$refs.list.style.width = this.input.clientWidth + 'px';
                    this.$refs.list.style.top = (this.input.offsetTop + this.input.clientHeight + 1) + 'px';
                    this.$refs.list.style.left = this.input.offsetLeft + 'px';
                } else {
                    this.result = [];
                }
            },

            down: function () {
                this.changeIndex(--this.selectedIndex);
            },

            up: function () {
                this.changeIndex(++this.selectedIndex);
            },

            changeIndex: function (index) {
                const length = this.result.length;

                if (length > 0) {
                    if (index >= length) {
                        index = 0;
                    }
                    else if (index < 0) {
                        index = length - 1;
                    }

                    this.selectedIndex = index;
                }
            },

            applySelected: function (startIndex, caretPosition) {
                let text = this.result[this.selectedIndex].name;

                if (text.length) {
                    if (text.indexOf(' ') > -1 || text.indexOf('.') > -1) {
                        text = '{' + text + '}';
                    }

                    if (startIndex === 1) {
                        text += ': ';
                    }

                    this.input.value = this.input.value.substr(0, startIndex) + text + this.input.value.substring(caretPosition);
                    this.input.focus();
                    this.input.dispatchEvent(new Event('change', {'bubbles': true }));

                    let caret = startIndex + text.length;

                    if (this.input.setSelectionRange) {
                        this.input.setSelectionRange(caret, caret);
                    }
                    else if (this.input.createTextRange) {
                        let range = this.input.createTextRange();

                        range.collapse(true);
                        range.moveEnd('character', caret);
                        range.moveStart('character', caret);
                        range.select();
                    }
                }

                this.result = [];
            }
        }
    }
</script>

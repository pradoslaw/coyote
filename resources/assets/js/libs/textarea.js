class Textarea {
    constructor(textarea) {
        this.textarea = textarea;
    }

    /**
     * Get textarea selection.
     *
     * @return {string}
     */
    getSelection() {
        let startPos = this.textarea.selectionStart;
        let endPos = this.textarea.selectionEnd;

        return this.textarea.value.substring(startPos, endPos);
    }

    /**
     * Insert text at selection.
     *
     * @param {string} startsWith
     * @param {string} endsWith
     * @param {string} value
     */
    insertAtCaret(startsWith, endsWith, value) {
        let startPos = this.textarea.selectionStart;
        let endPos = this.textarea.selectionEnd;
        let scrollTop = this.textarea.scrollTop;

        value = startsWith + value + endsWith;

        this.textarea.value = this.textarea.value.substring(0, startPos) + value + this.textarea.value.substring(endPos, this.textarea.value.length);

        this.textarea.focus();
        this.textarea.selectionStart = startPos + value.length;
        this.textarea.selectionEnd = startPos + value.length;
        this.textarea.scrollTop = scrollTop;
    }

    /**
     * Return TRUE if text is selected within textarea.
     *
     * @return {boolean}
     */
    isSelected() {
        return (this.textarea.selectionEnd - this.textarea.selectionStart) > 0;
    }
}

export default Textarea;

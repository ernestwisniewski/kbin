import TextareaAutoGrow from 'stimulus-textarea-autogrow';

/* stimulusFetch: 'lazy' */
export default class extends TextareaAutoGrow {
    connect() {
        super.connect();

        this.element.addEventListener('keydown',
            this.handleInput.bind(this));
    }

    handleInput (event) {
        // ctrl + enter to submit form
        if (event.ctrlKey && event.which === 13) {
            this.element.form.submit();
        }

        // ctrl + b to toggle bold
        else if (event.ctrlKey && event.which === 66) {
            this.toggleFormattingEnclosure('**');
        }

        // ctrl + i to toggle italic
        else if (event.ctrlKey && event.which === 73) {
            this.toggleFormattingEnclosure('_');
        }

        // grave to toggle inline code
        else if (event.which === 192) {
            this.toggleFormattingEnclosure('`');
        }

        else {
            return;
        }

        event.preventDefault();
    }

    toggleFormattingEnclosure(encl) {
        const start = this.element.selectionStart, end = this.element.selectionEnd;
        const ranged = start != end;
        const before = this.element.value.substring(0, start),
            inner = this.element.value.substring(start, end),
            after = this.element.value.substring(end);

        // TODO: find a way to do undo-aware text manipulations that isn't deprecated like execCommand?
        // it seems like specs never actually replaced it with anything unless i'm missing it

        // remove an existing enclosure
        if (before.endsWith(encl) && after.startsWith(encl)) {
            this.element.selectionStart = start - encl.length;
            this.element.selectionEnd = end + encl.length;

            // TODO: find a way to do this that isn't deprecated?
            // it seems like this was never actually replaced by anything
            document.execCommand('delete', false, null);
            document.execCommand('insertText', false, inner);

            this.element.selectionStart = start - encl.length;
            this.element.selectionEnd = end - encl.length;
        }

        // add a new enclosure
        else {
            if (ranged) {
                document.execCommand('delete', false, null);
            }
            document.execCommand('insertText', false, encl + inner + encl);

            this.element.selectionStart = start + encl.length;
            this.element.selectionEnd = end + encl.length;
        }
    }
}
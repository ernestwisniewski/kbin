import SimpleMDE from 'simplemde';

export default class Keditor {
    constructor(form) {
        if (form) {
            this.build(form.querySelector('.kbin-editor'));
            return;
        }

        document.querySelectorAll('.kbin-editor').forEach(el => {
            this.build(el)
        });
    }

    build(el) {
        let simplemde = new SimpleMDE({
            element: el,
            hideIcons: ['guide', 'fullscreen', 'side-by-side', 'preview', 'heading'],
            showIcons: ['code', 'table'],
            spellChecker: false,
            status: false,
            toolbarTips: false,
            styleSelectedText: false
        });

        const textarea = simplemde.element.parentNode.getElementsByClassName('CodeMirror')[0].getElementsByTagName('textarea')[0]
        const toolbar = simplemde.element.parentElement.getElementsByClassName('editor-toolbar')[0];

        toolbar.classList.add('visually-hidden');

        textarea.addEventListener('focus', (evt => {
            toolbar.classList.remove('visually-hidden');
        }));
    }
}

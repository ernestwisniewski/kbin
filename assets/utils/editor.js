import SimpleMDE from 'simplemde';

export default class Keditor {
    constructor(form, focus = false) {
        if (form) {
            return this.build(form.querySelector('.kbin-editor'), focus);
        }

        document.querySelectorAll('.kbin-editor').forEach(el => {
            this.build(el)
        });

        document.addEventListener('turbo:load', (event) => {
            event.target.querySelectorAll('.kbin-editor').forEach(el => {
                this.build(el)
            });
        });
    }

    build(el, focus = false) {
        let simplemde = new SimpleMDE({
            element: el,
            hideIcons: ['guide', 'fullscreen', 'side-by-side', 'preview', 'heading'],
            showIcons: ['code', 'table'],
            spellChecker: false,
            status: false,
            toolbarTips: false,
            styleSelectedText: false,
            autofocus: focus,
        });

        simplemde.codemirror.setOption("extraKeys", {
            'Ctrl-Enter': (e) => {
                el.closest('form').querySelector('[type="submit"]').click();
            }
        });

        if (!focus) {
            const textarea = simplemde.element.parentNode.getElementsByClassName('CodeMirror')[0].getElementsByTagName('textarea')[0]
            const toolbar = simplemde.element.parentElement.getElementsByClassName('editor-toolbar')[0];

            toolbar.classList.add('visually-hidden');

            textarea.addEventListener('focus', (evt => {
                toolbar.classList.remove('visually-hidden');
            }));
        }

        return simplemde;
    }
}

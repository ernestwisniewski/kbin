import {Controller} from '@hotwired/stimulus';
import SimpleMDE from 'simplemde/dist/simplemde.min';

export default class extends Controller {
    static values = {
        focus: Boolean,
    };

    connect() {
        super.connect();

        this.element.querySelectorAll('.kbin-editor').forEach(el => {
            this.build(el, this.focusValue)
        });
    }

    build(el, focus = true) {
        let simplemde = new SimpleMDE({
            element: el,
            hideIcons: ['guide', 'fullscreen', 'side-by-side', 'preview', 'heading'],
            showIcons: ['code', 'table'],
            spellChecker: false,
            status: true,
            toolbarTips: false,
            promptURLs: true,
            styleSelectedText: false,
            autofocus: focus,
            forceSync: true,
        });

        simplemde.codemirror.setOption("extraKeys", {
            'Ctrl-Enter': (e) => {
                el.closest('form').querySelector('[type="submit"]').click();
            },
        });

        // try {
        //     if (this.element.closest('article')) {
        //         this.addMention(
        //             simplemde,
        //             this.element.closest('article').getElementsByClassName('kbin-user')[0].innerHTML.trim()
        //         )
        //     } else if (this.element.closest('blockquote')) {
        //         this.addMention(
        //             simplemde,
        //             this.element.closest('blockquote').getElementsByClassName('kbin-user')[0].innerHTML.trim()
        //         )
        //     }
        // } catch (e) {
        //     throw e;
        // }

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

    addMention(simplemde, replyTo) {
        if (Array.from(replyTo)[0] !== '@') {
            replyTo = '@' + replyTo;
        }

        simplemde.codemirror.getDoc();
        let doc = simplemde.codemirror.getDoc();
        let cursor = doc.getCursor();
        let line = doc.getLine(cursor.line);
        let pos = {
            line: cursor.line,
            ch: line.length - 1
        }

        doc.replaceRange(replyTo + ' ', pos);
    }
}

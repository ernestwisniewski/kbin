import {Controller} from '@hotwired/stimulus';
import {fetch, ok} from "../utils/http";
import router from "../utils/routing";
import bootstrap from "bootstrap/dist/js/bootstrap.min";
import {trim} from "core-js/internals/string-trim";
import EasyMDE from "easymde";

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
        this.editor = new EasyMDE({
            element: el,
            hideIcons: ['guide', 'fullscreen', 'side-by-side', 'preview', 'heading', 'table'],
            showIcons: ['code', 'table'],
            spellChecker: false,
            nativeSpellcheck: true,
            status: true,
            toolbarTips: false,
            promptURLs: true,
            styleSelectedText: false,
            autofocus: focus,
            forceSync: true,
        });

        this.addExtraKeys(el);
        this.addMentions();
        this.handleFocus();
        this.handleUserAutocomplete();
        this.handleTagAutocomplete();
    }

    addMention(replyTo) {
        if (this.editor.value()) {
            return;
        }

        if (Array.from(replyTo)[0] !== '@') {
            replyTo = '@' + replyTo;
        }

        let doc = this.editor.codemirror.getDoc();
        let cursor = doc.getCursor();
        let line = doc.getLine(cursor.line);
        let pos = {
            line: cursor.line, ch: line.length - 1
        }

        doc.replaceRange(replyTo + ' ', pos);
    }

    addExtraKeys(el) {
        this.editor.codemirror.setOption("extraKeys", {
            'Ctrl-Enter': (e) => {
                el.closest('form').querySelector('[type="submit"]').click();
            },
        });

    }

    addMentions() {
        try {
            if (this.element.closest('article')) {
                this.addMention(this.element.closest('article').getElementsByClassName('kbin-user')[0].innerHTML.trim())
            } else if (this.element.closest('blockquote')) {
                const isEntryComment = this.element.closest('blockquote').id.startsWith('entry-comment');

                if (!isEntryComment) {
                    this.addMention(this.element.closest('blockquote').getElementsByClassName('kbin-user')[0].innerHTML.trim())
                }
            }
        } catch (e) {
            throw e;
        }
    }

    handleFocus() {
        if (!focus) {
            const textarea = this.editor.element.parentNode.getElementsByClassName('CodeMirror')[0].getElementsByTagName('textarea')[0]
            const toolbar = this.editor.element.parentElement.getElementsByClassName('editor-toolbar')[0];

            toolbar.classList.add('visually-hidden');

            textarea.addEventListener('focus', (evt => {
                toolbar.classList.remove('visually-hidden');
            }));
        }

    }

    handleUserAutocomplete() {
        let self = this;
        this.editor.codemirror.on("change", function (e) {
            self.lastWord = trim(self.editor.value().split(' ').pop());

            let list = self.element.getElementsByClassName('kbin-suggests');
            for (let item of list) {
                item.remove();
            }

            if (self.lastWord.startsWith('@')) {
                if (self.lastWord.length > 2) {
                    self.fetchUsers(self.lastWord, self.element)
                }
            }
        });
    }

    async fetchUsers(username, elem) {
        try {
            let response = await fetch(router().generate('ajax_fetch_users_suggestions', {username: username}));

            response = await ok(response);
            response = await response.json();

            let div = document.createElement('div');
            div.setAttribute("id", "kbin-suggest");
            div.innerHTML = response.html;

            elem.getElementsByClassName('editor-statusbar')[0].after(div);

            const dropdownElem = new bootstrap.Dropdown(div.querySelectorAll('.dropdown-toggle')[0]);

            dropdownElem.show();
        } catch (e) {
        }
    }

    acceptSuggest(e) {
        e.preventDefault();

        this.editor.value(this.editor.value().replace(new RegExp(this.lastWord + "\\b", 'ig'), '@' + e.target.innerHTML + ' '));
        this.editor.element.focus(); // @todo
    }

    handleTagAutocomplete() {

    }
}

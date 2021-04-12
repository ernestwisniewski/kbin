import {Controller} from 'stimulus';
import {fetch, ok} from "../utils/http";
import KEditor from "../utils/editor";

export default class extends Controller {
    static targets = ['form'];
    static values = {
        loading: Boolean,
        url: String,
        form: String,
        level: Number
    };

    async reply(event) {
        await this.handle(event);
    }

    async edit(event) {
        await this.handle(event, true);
    }

    async handle(event, edit = false) {
        event.preventDefault();

        this.loadingValue = true;

        try {
            let response = await fetch(event.target.href, {method: 'GET'});

            response = await ok(response);
            response = await response.json();

            this.formTarget.innerHTML = response.form;

            new KEditor(this.formTarget);

            let self = this;
            this.formTarget.getElementsByTagName('form')[0].addEventListener('submit', function (e) {
                self.send(e, edit);
            });
        } catch (e) {
            alert('Nie możesz dodać komentarza.');
        } finally {
            this.loadingValue = false;
        }
    }

    async send(event, edit = false) {
        event.preventDefault();

        this.loadingValue = true;

        try {
            let response = await fetch(event.target.action, {method: 'POST', body: new FormData(event.target)});

            response = await ok(response);
            response = await response.json();

            let level = event.target.closest('blockquote').dataset.commentLevelValue;

            let div = document.createElement('div');
            div.innerHTML = response.html;

            if (edit) {
                div.firstElementChild.classList.add('kbin-comment-level--' + level);
                event.target.closest('blockquote').replaceWith(div);
                return;
            }

            level = (level >= 7 ? 7 : parseInt(level) + 1);
            div.firstElementChild.classList.add('kbin-comment-level--' + level)
            div.firstElementChild.dataset.commentLevelValue = level;

            event.target
                .closest('blockquote')
                .parentNode
                .insertBefore(div.firstElementChild, event.target.closest('blockquote').nextSibling);

            event.target.parentNode.innerHTML = ''
        } catch (e) {
            alert('Nie możesz dodać komentarza.');
        } finally {
            this.loadingValue = false;
        }
    }
}

import {Controller} from '@hotwired/stimulus';
import {fetch, ok} from "../utils/http";
import KEditor from "../utils/editor";
import CommentFactory from "../utils/comment-factory";

export default class extends Controller {
    static targets = ['form'];
    static values = {
        loading: Boolean,
        url: String,
        form: String,
        level: Number,
        nested: Boolean
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

            new KEditor(this.formTarget, true);

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

            if (edit) {
                CommentFactory.edit(response.html, event.target.closest('.kbin-comment'));
            } else {
                CommentFactory.create(response.html, event.target.closest('.kbin-comment'), this.nestedValue);
            }

            event.target.parentNode.innerHTML = ''
        } catch (e) {
            alert('Nie możesz dodać komentarza.');
        } finally {
            this.loadingValue = false;
        }
    }
}

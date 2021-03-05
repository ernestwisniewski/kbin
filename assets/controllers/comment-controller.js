import {Controller} from 'stimulus';
import {fetch, ok} from "./utils/http";
import router from "./utils/routing";
import KEditor from "./utils/editor";

export default class extends Controller {
    static targets = ['reply'];
    static values = {
        loading: Boolean,
        url: String,
        form: String,
        level: Number
    };

    async reply(event) {
        event.preventDefault();

        this.loadingValue = true;

        try {
            let response = await fetch(this.urlValue, {method: 'GET'});

            response = await ok(response);
            response = await response.json();

            this.formValue = response.form;
            event.target.remove();
        } catch (e) {
            alert('Nie możesz dodać komentarza.');
        } finally {
            this.loadingValue = false;
        }
    }

    async send(event) {
        event.preventDefault();

        this.loadingValue = true;

        try {
            let response = await fetch(this.urlValue, {method: 'POST', body: new FormData(event.target)});

            response = await ok(response);
            response = await response.json();

            let level = event.target.closest('blockquote').dataset.commentLevelValue;

            let div = document.createElement('div');
            div.innerHTML = response.html;

            level = (level >= 7 ? 7 : parseInt(level) + 1);

            div.firstElementChild.classList.add('kbin-comment-level--' + level)
            div.firstElementChild.dataset.commentLevelValue = level;

            event.target
                .closest('blockquote')
                .parentNode
                .insertBefore(div.firstElementChild, event.target.closest('blockquote').nextSibling);

            event.target.parentNode.remove()
        } catch (e) {
            alert('Nie możesz dodać komentarza.');
        } finally {
            this.loadingValue = false;
        }
    }

    formValueChanged(val) {
        if (!val) {
            return;
        }

        this.replyTarget.innerHTML = val;
        new KEditor(this.replyTarget);


        let self = this;
        this.replyTarget.getElementsByTagName('form')[0].addEventListener('submit', function (e) {
            self.send(e);
        });
    }
}

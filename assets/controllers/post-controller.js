import {Controller} from 'stimulus';
import {fetch, ok} from "./utils/http";
import router from "./utils/routing";
import KEditor from "./utils/editor";

export default class extends Controller {
    static targets = ['expand', 'reply'];
    static values = {
        loading: Boolean,
        id: Number,
        magazineName: String,
        commentList: String,
        form: String,
    };

    async reply(event) {
        event.preventDefault();

        this.loadingValue = true;

        try {
            let url = router().generate('post_comment_create', {'magazine_name': this.magazineNameValue, 'post_id': this.idValue});

            let response = await fetch(url, {method: 'GET'});

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
            let url = router().generate('post_comment_create', {'magazine_name': this.magazineNameValue, 'post_id': this.idValue});

            let response = await fetch(url, {method: 'POST', body: new FormData(event.target)});

            response = await ok(response);
            response = await response.json();

            this.element.insertAdjacentHTML('afterend', response.html);
            this.replyTarget.remove();
        } catch (e) {
            alert('Nie możesz dodać komentarza.');
        } finally {
            this.loadingValue = false;
        }
    }

    async expandComments(event) {
        event.preventDefault();

        this.loadingValue = true;

        let loader = document.createElement("span");
        loader.classList.add('spinner-border', 'me-2');

        event.target.parentNode.replaceChild(loader, event.target);

        try {
            let url = router().generate('ajax_fetch_post_comments', {'id': this.idValue});

            let response = await fetch(url, {method: 'GET'});

            response = await ok(response);
            response = await response.json();

            this.element.parentElement
                .querySelectorAll(`[data-comment-post-id="${this.idValue}"]`)
                .forEach(e => {
                    e.remove()
                })
            this.element.insertAdjacentHTML('afterend', response.html);
        } catch (e) {
            alert('Coś poszło nie tak...')
        } finally {
            loader.remove();
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

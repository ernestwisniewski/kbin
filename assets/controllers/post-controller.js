import {Controller} from '@hotwired/stimulus';
import {fetch, ok} from "../utils/http";
import router from "../utils/routing";
import KEditor from "../utils/editor";

export default class extends Controller {
    static targets = ['expand', 'form', 'commentCounter'];
    static values = {
        loading: Boolean,
        id: Number,
        commentList: String,
        form: String,
    };

    async reply(event) {
        event.preventDefault();

        this.loadingValue = true;

        try {
            const url = event.target.href;

            let response = await fetch(url, {method: 'GET'});

            response = await ok(response);
            response = await response.json();

            this.formTarget.innerHTML = response.form;
            new KEditor(this.formTarget);

            let self = this;
            this.formTarget.getElementsByTagName('form')[0].addEventListener('submit', function (e) {
                self.send(e);
            });
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
            const url = event.target.action;

            let response = await fetch(url, {method: 'POST', body: new FormData(event.target)});

            response = await ok(response);
            response = await response.json();

            this.element.nextElementSibling.insertAdjacentHTML('beforeend', response.html);

            event.target.parentNode.innerHTML = ''
        } catch (e) {
            alert('Nie możesz dodać komentarza.');
        } finally {
            this.loadingValue = false;
        }
    }

    async expand(event) {
        event.preventDefault();

        this.loadingValue = true;

        let loader = document.createElement("span");
        loader.classList.add('spinner-border', 'me-2');

        this.expandTarget.parentNode.replaceChild(loader, this.expandTarget)

        try {
            const url = router().generate('ajax_fetch_post_comments', {'id': this.idValue});

            let response = await fetch(url, {method: 'GET'});

            response = await ok(response);
            response = await response.json();

            this.element.parentElement
                .querySelectorAll(`[data-comment-list-subject-id-value="${this.idValue}"]`)
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

    remove(notification) {
        if (this.idValue !== notification.detail.id) {
            return;
        }

        this.element.remove();
        document.querySelector(`[data-comment-list-subject-id-value='${notification.detail.id}']`).remove()
    }

    async edit(notification) {
        if (this.idValue !== notification.detail.id) {
            return;
        }

        try {
            const url = router().generate('ajax_fetch_post', {'id': notification.detail.id});

            let response = await fetch(url, {method: 'GET'});

            response = await ok(response);
            response = await response.json();

            this.element.outerHTML = response.html;
        } catch (e) {
        }
    }

    increaseComments(notification) {
        if (this.idValue === notification.detail.subject.id && this.hasCommentCounterTarget) {
            this.commentCounterTarget.textContent = Number(this.commentCounterTarget.textContent) + 1;
        }
    }

    decreaseComments(notification) {
        if (this.idValue === notification.detail.subject.id && this.hasCommentCounterTarget) {
            this.commentCounterTarget.textContent = Number(this.commentCounterTarget.textContent) - 1;
        }
    }
}

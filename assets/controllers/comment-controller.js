import {Controller} from '@hotwired/stimulus';
import {fetch, ok} from "../utils/http";
import KEditor from "../utils/editor";
import CommentFactory from "../utils/comment-factory";
import Modal from 'bootstrap/js/dist/modal';

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

            let replyTo = event.target.closest('blockquote').getElementsByClassName('kbin-user')[0].innerHTML.trim();
            if (Array.from(replyTo)[0] !== '@') {
                replyTo = '@' + replyTo;
            }

            const editor = new KEditor(this.formTarget, false);
            editor.value(replyTo + ' ');

            let self = this;
            this.formTarget.getElementsByTagName('form')[0].addEventListener('submit', function (e) {
                self.send(e, edit);
            });

            let modal = Modal.getOrCreateInstance(this.formTarget.getElementsByClassName('modal')[0]);
            modal._element.addEventListener('shown.bs.modal', function (event) {
                self.formTarget.classList.add('position-relative')
                event.target.classList.add('postition-absolute')

                document.getElementsByClassName('modal-backdrop')[0].remove();

                document.body.style.removeProperty('overflow')
                document.body.style.removeProperty('padding-right')
            })
        } catch (e) {
            if (!window.KBIN_LOGGED_IN) {
                window.location = window.KBIN_LOGIN;
                return;
            }

            throw e
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
            throw e;
        } finally {
            this.loadingValue = false;
        }
    }
}

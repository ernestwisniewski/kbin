import {Controller} from '@hotwired/stimulus';
import {fetch, ok} from "../utils/http";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['loader', 'more', 'container']
    static values = {
        loading: Boolean,
    };

    connect() {
        const self = this;
        this.moreTarget.addEventListener('focusin', () => {
            self.element.parentNode
                .querySelectorAll('.z-5')
                .forEach((el) => {
                    el.classList.remove('z-5');
                });
            this.element.classList.add('z-5');
        });
    }

    async getForm(event) {
        event.preventDefault();

        if ('' !== this.containerTarget.innerHTML.trim()) {
            if (false === confirm('Do you really want to leave?')) {
                return;
            }
        }

        try {
            this.loadingValue = true;

            let response = await fetch(event.target.href, {method: 'GET'});

            response = await ok(response);
            response = await response.json();

            this.containerTarget.innerHTML = response.form;
        } catch (e) {
            window.location.href = event.target.href;
        } finally {
            this.loadingValue = false;
        }
    }

    async sendForm(event) {
        event.preventDefault();

        const form = event.target.closest('form');
        const url = form.action;

        try {
            this.loadingValue = true;

            let response = await fetch(url, {
                method: 'POST',
                body: new FormData(form)
            });

            response = await ok(response);
            response = await response.json();

            if (response.form) {
                this.containerTarget.innerHTML = response.form;
            } else if (form.classList.contains('replace')) {
                const div = document.createElement('div');
                div.innerHTML = response.html;
                div.firstElementChild.className = this.element.className;

                this.element.innerHTML = div.firstElementChild.innerHTML;
            } else {
                const div = document.createElement('div');
                div.innerHTML = response.html;

                let level = this.getLevel();

                div.firstElementChild.classList.add('comment-level--' + (level >= 10 ? 10 : level + 1));

                if (this.element.nextElementSibling && this.element.nextElementSibling.classList.contains('comments')) {
                    this.element.nextElementSibling.appendChild(div.firstElementChild);
                } else {
                    this.element.parentNode.insertBefore(div.firstElementChild, this.element.nextSibling);
                }

                this.containerTarget.innerHTML = '';
            }
        } catch (e) {
            // this.containerTarget.innerHTML = '';
        } finally {
            this.application.getControllerForElementAndIdentifier(document.getElementById('main'), 'lightbox').connect();
            this.loadingValue = false;
        }

    }

    async favourite(event) {
        event.preventDefault();

        const form = event.target.closest('form');

        try {
            this.loadingValue = true;

            let response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form)
            });

            response = await ok(response);
            response = await response.json();

            form.innerHTML = response.html;
        } catch (e) {
            form.submit();
        } finally {
            this.loadingValue = false;
        }
    }

    async vote(event) {
        event.preventDefault();

        const form = event.target.closest('form');

        try {
            this.loadingValue = true;

            let response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form)
            });

            response = await ok(response);
            response = await response.json();

            event.target.closest('.vote').insertAdjacentHTML('afterend', response.html);
        } catch (e) {
            form.submit();
        } finally {
            this.loadingValue = false;
        }
    }

    loadingValueChanged(val) {
        const submitButton = this.containerTarget.querySelector('form button[type="submit"]');

        if (true === val) {
            if (submitButton) {
                submitButton.disabled = true;
            }
            this.loaderTarget.style.display = 'block';
        } else {
            if (submitButton) {
                submitButton.disabled = false;
            }
            this.loaderTarget.style.display = 'none';
        }
    }

    getLevel() {
        let level = parseInt(this.element.className.replace('comment-level--1', '').split('--')[1]);
        return isNaN(level) ? 1 : level;
    }
}
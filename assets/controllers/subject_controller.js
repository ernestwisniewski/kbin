import {Controller} from '@hotwired/stimulus';
import {fetch, ok} from "../utils/http";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['loader', 'more', 'container']
    static values = {
        loading: Boolean
    };

    connect() {
        const self = this;
        this.moreTarget.addEventListener('focusin', () => {
            self.element.parentNode
                .querySelectorAll('.z-100')
                .forEach((el) => {
                    el.classList.remove('z-100');
                });
            this.element.classList.add('z-100');
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

        let response = await fetch(event.target.closest('form').action, {
            method: 'POST',
            body: new FormData(event.target.closest('form'))
        });

        response = await ok(response);
        response = await response.json();

        if (response.form) {
            console.log(this.containerTarget)
            this.containerTarget.innerHTML = response.form;
        } else {
            const div = document.createElement('div');
            div.innerHTML = response.html;

            let level = parseInt(this.element.className.replace('comment-level--1', '').split('--')[1]);
            if (isNaN(level)) {
                level = 1;
            }

            div.firstElementChild.classList.add('comment-level--' + (level >= 10 ? 10 : level + 1));
            this.element.parentNode.insertBefore(div.firstElementChild, this.element.nextSibling);

            this.element.remove();
        }
    }

    loadingValueChanged(val) {
        this.loaderTarget.style.display = val === true ? 'block' : 'none';
    }
}
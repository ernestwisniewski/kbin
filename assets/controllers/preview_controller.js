import {Controller} from '@hotwired/stimulus';
import {fetch, ok} from "../utils/http";
import router from "../utils/routing";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {
        loading: Boolean,
    }

    async show(event) {
        event.preventDefault();

        let container = this.element.nextElementSibling && this.element.nextElementSibling.classList.contains('js-container')
            ? this.element.nextElementSibling : null;

        if (null === container) {
            container = document.createElement('div');
            container.classList.add('js-container');
            this.element.insertAdjacentHTML('afterend', container.outerHTML);
        } else {
            if (container.querySelector('.preview')) {
                container.querySelector('.preview').remove();
                return;
            }
        }

        try {
            this.loadingValue = true;

            let response = await fetch(router().generate('ajax_fetch_embed', {url: event.params.url}), {method: 'GET'});

            response = await ok(response);
            response = await response.json();

            this.element.nextElementSibling.insertAdjacentHTML('afterbegin', response.html);
            if (event.params.ratio) {
                this.element.nextElementSibling.querySelector('.preview').classList.add('ratio');
            }
            this.loadScripts(response.html);
        } catch (e) {
            window.location.href = event.target.href;
        } finally {
            this.loadingValue = false;
        }
    }

    loadScripts(response) {
        let tmp = document.createElement("div");
        tmp.innerHTML = response;
        let el = tmp.getElementsByTagName('script');

        if (el.length) {
            let script = document.createElement("script");
            script.setAttribute("src", el[0].getAttribute('src'));
            script.setAttribute("async", "false");

            // let exists = [...document.head.querySelectorAll('script')]
            //     .filter(value => value.getAttribute('src') >= script.getAttribute('src'));
            //
            // if (exists.length) {
            //     return;
            // }

            let head = document.head;
            head.insertBefore(script, head.firstElementChild);
        }
    }

    loadingValueChanged(val) {
        const subject = this.element.closest('.subject');
        if (null !== subject) {
            const subjectController = this.application.getControllerForElementAndIdentifier(subject, 'subject');
            subjectController.loadingValue = val;
        }
    }
}
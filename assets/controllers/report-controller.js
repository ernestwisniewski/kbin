import {Controller} from '@hotwired/stimulus';
import {fetch, ok} from "../utils/http";

export default class extends Controller {
    static targets = ['form'];
    static values = {
        loading: Boolean
    };

    async report(event) {
        event.preventDefault();

        this.loadingValue = true;

        if (!window.KBIN_LOGGED_IN) {
            document.querySelector(".kbn-login-btn a").click()
            return;
        }

        try {
            let response = await fetch(event.target.href, {method: 'GET'});

            response = await ok(response);
            response = await response.json();

            this.formTarget.innerHTML = response.form;

            let self = this;
            this.formTarget.getElementsByTagName('form')[0].addEventListener('submit', function (e) {
                self.send(e);
            });
        } catch (e) {
            alert('Oops, something went wrong.');
            throw e;
        } finally {
            this.loadingValue = false;
        }
    }

    async send(event) {
        event.preventDefault();

        this.loadingValue = true;

        if (!window.KBIN_LOGGED_IN) {
            document.querySelector(".kbn-login-btn a").click()
            return;
        }

        try {
            let response = await fetch(event.target.action, {method: 'POST', body: new FormData(event.target)});

            response = await ok(response);
            response = await response.json();

            event.target.parentNode.innerHTML = '';

            alert('👍👍👍');
        } catch (e) {
            alert('Oops, something went wrong.');
            throw e;
        } finally {
            this.loadingValue = false;
        }
    }
}

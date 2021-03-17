import {Controller} from 'stimulus';
import {fetch, ok} from "../utils/http";
import router from "../utils/routing";

export default class extends Controller {
    static targets = ['form'];
    static values = {
        loading: Boolean
    };

    async report(event) {
        event.preventDefault();

        this.loadingValue = true;

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
            alert('Nie możesz dodać zgłoszenia.');
        } finally {
            this.loadingValue = false;
        }
    }

    async send(event) {
        event.preventDefault();

        this.loadingValue = true;

        try {
            let response = await fetch(event.target.action, {method: 'POST', body: new FormData(event.target)});

            response = await ok(response);
            response = await response.json();

            event.target.parentNode.innerHTML = '';

            alert('Zgłoszenie zostało wysłane, dzięki!');
        } catch (e) {
            alert('Nie możesz dodać zgłoszenia.');
        } finally {
            this.loadingValue = false;
        }
    }
}

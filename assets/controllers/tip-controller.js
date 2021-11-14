import {Controller} from 'stimulus';
import {fetch, ok} from "../utils/http";

export default class extends Controller {
    static targets = ['container'];

    async fetch(event) {
        event.preventDefault();

        try {
            console.log(event.target.closest('a'));
            let response = await fetch(event.target.closest('a').href, {method: 'GET'});

            response = await ok(response);
            response = await response.json();

            this.containerTarget.innerHTML = response.html;
        } catch (e) {
            alert('Oops, something went wrong.');
            throw e;
        } finally {
            this.loadingValue = false;
        }
    }
}

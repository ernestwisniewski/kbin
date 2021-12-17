import {Controller} from '@hotwired/stimulus';
import {fetch, ok} from "../utils/http";

export default class extends Controller {
    static targets = ['container'];

    async fetch(e) {
        e.preventDefault();

        let response = await fetch(e.target.parentElement.href, {method: 'GET'});

        response = await ok(response);
        response = await response.json();

        this.element.innerHTML = response.html;
    }
}

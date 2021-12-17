import {Controller} from '@hotwired/stimulus';
import {fetch, ok} from "../utils/http";
import Cookies from 'js-cookie';

export default class extends Controller {
    connect() {
    }

    async toggleTheme(e) {
        e.preventDefault();

        try {
            let response = await fetch(e.target.href, {method: 'POST'});

            response = await ok(response);
            response = await response.json();

            document.body.classList.toggle('kbin-dark');
        } catch (e) {
            document.body.classList.toggle('kbin-dark');
        } finally {
        }
    }
}

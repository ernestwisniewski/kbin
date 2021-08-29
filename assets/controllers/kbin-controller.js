import {Controller} from 'stimulus';
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

            this.element.classList.toggle('kbin-dark');
        } catch (e) {
        } finally {
        }
    }
}

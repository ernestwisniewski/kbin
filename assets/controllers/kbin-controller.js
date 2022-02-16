import {Controller} from '@hotwired/stimulus';
import {fetch, ok} from "../utils/http";
import Cookies from 'js-cookie';

export default class extends Controller {
    async toggleTheme(e) {
        e.preventDefault();

        if (window.KBIN_LOGGED_IN) {
            try {
                let response = await fetch(e.target.href, {method: 'POST'});

                response = await ok(response);
                response = await response.json();

                document.body.classList.toggle('kbin-dark');
            } catch (e) {
                document.body.classList.toggle('kbin-dark');
            } finally {
            }
        } else {
            if (!Cookies.get('theme')) {
                document.body.classList.add('kbin-dark');
                Cookies.set('theme', 'kbin-dark');
                return true;
            }

            Cookies.set('theme', document.body.classList.contains('kbin-dark') ? 'kbin-light' : 'kbin-dark');
            document.body.classList.toggle('kbin-dark');
        }
    }
}

import {Controller} from '@hotwired/stimulus';
import {fetch, ok} from "../utils/http";
import Cookies from 'js-cookie';

export default class extends Controller {
    connect() {
        if (window.KBIN_LOGGED_IN) {
            return true;
        }

        if (!document.body.classList.contains('kbin-light') && !document.body.classList.contains('kbin-dark')) {
            return true
        }

        if (Cookies.get('theme') === 'kbin-dark') {
            document.body.classList.add('kbin-dark');
        }
    }

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
            alert('Zaloguj się, aby uniknąć efektu miagania.')

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

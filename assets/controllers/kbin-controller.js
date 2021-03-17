import {Controller} from 'stimulus';
import {fetch, ok} from "../utils/http";
import Cookies from 'js-cookie';

export default class extends Controller {
    connect() {
        if (Cookies.get('dark')) {
            this.element.classList.add('kbin-dark');
        }
    }

    async toggleTheme(e) {
        e.preventDefault();

        try {
            let response = await fetch(e.target.href, {method: 'POST'});

            response = await ok(response);
            response = await response.json();

            Cookies.remove('dark');
            this.element.classList.toggle('kbin-dark');
        } catch (e) {
            this.element.classList.toggle('kbin-dark');
            if (Cookies.get('dark')) {
                Cookies.remove('dark');
            } else {
                alert("Zaloguj się, aby uniknąć efektu migania.")
                Cookies.set('dark', true);
            }
        } finally {
        }
    }
}

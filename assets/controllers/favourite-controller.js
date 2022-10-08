import {Controller} from '@hotwired/stimulus';
import {fetch, ok} from "../utils/http";

export default class extends Controller {
    static targets = ['counter'];

    async toggle(event) {
        event.preventDefault();

        if (!window.KBIN_LOGGED_IN) {
            window.location = window.KBIN_LOGIN;
            return;
        }

        try {
            let response = await fetch(event.target.action, {
                method: 'POST',
                body: new FormData(event.target)
            });

            response = await ok(response);
            response = await response.json();

            this.counterTarget.innerHTML = `(${response.count})`;

            const btn = event.target.getElementsByTagName('button')[0];

            if(response.isFavored) {
                this.counterTarget.classList.remove('visually-hidden');
                btn.classList.add('text-decoration-underline');
            } else {
                this.counterTarget.classList.add('visually-hidden');
                btn.classList.remove('text-decoration-underline');
            }
        } catch (e) {
            alert('Oops, something went wrong.');
            throw e;
        }
    }

}

import {Controller} from '@hotwired/stimulus';
import router from "../utils/routing";
import {fetch, ok} from "../utils/http";

export default class extends Controller {
    static values = {
        id: Number,
    };

    remove(notification) {
        if (this.idValue !== notification.detail.id) {
            return;
        }

        this.element.remove();
    }

    async edit(notification) {
        if (this.idValue !== notification.detail.id) {
            return;
        }

        try {
            const url = router().generate('ajax_fetch_entry_comment', {'id': notification.detail.id});

            let response = await fetch(url, {method: 'GET'});

            response = await ok(response);
            response = await response.json();

            const classList = this.element.classList;

            let div = document.createElement('div');
            div.innerHTML = response.html;
            div.classList = classList;

            this.element.innerHTML = div.firstElementChild.innerHTML;
        } catch (e) {
        }
    }
}

import {Controller} from '@hotwired/stimulus';
import {fetch, ok} from "../utils/http";
import router from "../utils/routing";
import Modal from "bootstrap/js/dist/modal";

export default class extends Controller {
    async show(e) {
        e.preventDefault();

        this.element.classList.add('visually-hidden');

        const url = router().generate('ajax_fetch_user_popup', {username: this.element.innerHTML});

        let response = await fetch(url, {method: 'GET'});

        response = await ok(response);
        response = await response.json();

        if (document.contains(document.getElementById("kbin-user-popup"))) {
            document.getElementById("kbin-user-popup").remove();
        }

        let div = document.createElement('div');
        div.innerHTML = response.html;

        document.getElementById('kbin').prepend(div);

        (new Modal(document.getElementById("kbin-user-popup")).show());

        this.element.classList.remove('visually-hidden');
    }
}

import {Controller} from '@hotwired/stimulus';
import {fetch, ok} from "../utils/http";
import router from "../utils/routing";
import Modal from "bootstrap/js/dist/modal";

export default class extends Controller {
    static values = {
        username: String,
    };

    async mouse(e) {
        e.preventDefault();
        let self = this;
        this.timeout = window.setTimeout(function () {
            self.on();
        }, 1000)
    }

    out() {
        if (this.timeout) window.clearTimeout(this.timeout)
    }

    async on() {
        this.element.classList.add('kbin-link-block');

        const url = router().generate('ajax_fetch_user_popup', {username: this.usernameValue});

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

        this.element.classList.remove('kbin-link-block');
    }
}

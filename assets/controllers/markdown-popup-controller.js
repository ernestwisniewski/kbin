import {Controller} from '@hotwired/stimulus';
import {fetch, ok} from "../utils/http";
import Modal from 'bootstrap/js/dist/modal';

export default class extends Controller {
    async open(e) {
        e.preventDefault();

        try {
            let response = await fetch(e.target.closest('a').href);

            response = await ok(response);
            response = await response.json();

            let div = document.createElement('div');
            div.innerHTML = response.html;

            div.firstElementChild.setAttribute('id', 'markdown-popup')

            document.body.appendChild(div);

            (new Modal(document.getElementById('markdown-popup'))).show();

            document.getElementById('markdown-popup').addEventListener('hidden.bs.modal', (e) => {
                this.close();
            });
        } catch (e) {
            alert('Oops, something went wrong.');
        } finally {
        }
    }

    close() {
        document.getElementById('markdown-popup').remove();
    }
}

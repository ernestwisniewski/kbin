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

            e.target.closest('article').after(div);

            (new Modal(document.getElementById('content-popup'))).show();
        } catch (e) {
            alert('Oops, something went wrong.');
        } finally {
        }
    }

    close(e) {
        e.target.closest('#entry-popup').remove();
    }
}

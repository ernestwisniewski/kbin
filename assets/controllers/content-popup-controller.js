import {Controller} from '@hotwired/stimulus';
import {fetch, ok} from "../utils/http";
import Modal from 'bootstrap/js/dist/modal';
import KEditor from "../utils/editor";

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

            const popup = document.getElementById('content-popup');

            // const commentForm = popup.getElementsByClassName('kbin-editor');
            // if(commentForm.length){
            //     new KEditor(commentForm[0]);
            // } // @todo fix editor

            popup.addEventListener('hidden.bs.modal', (e) => {
                this.close();
            });
        } catch (e) {
            alert('Oops, something went wrong.');
        } finally {
        }
    }

    close() {
        document.getElementById('content-popup').remove();
    }
}

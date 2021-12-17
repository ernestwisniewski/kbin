import {Controller} from '@hotwired/stimulus';
import {fetch, ok} from "../utils/http";

export default class extends Controller {
    static targets = ['button', 'loading', 'address', 'mnemonic', 'content'];

    async create(event) {
        event.preventDefault();

        try {
            this.showLoading();
            let response = await fetch(event.target.closest('a').href, {method: 'GET'});

            response = await ok(response);
            response = await response.json();

            this.addressTarget.value = response.address;
            this.mnemonicTarget.value = response.mnemonic;

            this.removeLoading();
        } catch (e) {
            alert('Oops, something went wrong.');
            throw e;
        }
    }

    showLoading() {
        this.contentTarget.classList.add('visually-hidden');
        this.buttonTarget.classList.add('visually-hidden');
        this.loadingTarget.classList.remove('visually-hidden');
    }

    removeLoading() {
        this.contentTarget.classList.remove('visually-hidden');
        this.buttonTarget.classList.remove('visually-hidden');
        this.loadingTarget.classList.add('visually-hidden');
    }
}

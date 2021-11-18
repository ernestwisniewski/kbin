import {Controller} from 'stimulus';
import {fetch, ok} from "../utils/http";

export default class extends Controller {
    static targets = ['button', 'loading', 'walletId'];

    async create(event) {
        event.preventDefault();

        try {
            this.walletIdTarget.classList.add('visually-hidden');
            this.buttonTarget.classList.add('visually-hidden');

            // let response = await fetch(event.target.closest('a').href, {method: 'GET'});
            //
            // response = await ok(response);
            // response = await response.json();
            //
            // this.walletIdTarget.value = response.mnemonic;
            this.loadingValue = false;
        } catch (e) {
            alert('Oops, something went wrong.');
            throw e;
        } finally {
            this.loadingValue = false;
        }
    }
}

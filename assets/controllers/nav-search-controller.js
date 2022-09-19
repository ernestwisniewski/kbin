import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input'];

    toggleSearch(e) {
        e.preventDefault();

        if(this.inputTarget.classList.contains('visually-hidden')) {
            this.inputTarget.classList.remove('visually-hidden');
            this.inputTarget.focus();
        } else {
            this.inputTarget.closest('form').submit();
        }
    }
}

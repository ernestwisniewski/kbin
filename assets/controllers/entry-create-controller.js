import {Controller} from '@hotwired/stimulus';
import {useDebounce} from 'stimulus-use'
import {fetch, ok} from '../utils/http';
import router from '../utils/routing';

export default class extends Controller {
    static debounces = ['fetchTitle']
    static targets = ['sendButton', 'url', 'title'];
    static values = {
        loading: Boolean,
    };

    connect() {
        useDebounce(this, {wait: 800})
    }

    async fetchTitle() {
        if (this.titleTarget.value) {
            return;
        }

        if (!this.urlTarget.value) {
            return;
        }

        this.loadingValue = true;

        try {
            const url = router().generate('ajax_fetch_title');
            let response = await fetch(url, {method: 'POST', body: JSON.stringify({'url': this.urlTarget.value})});

            response = await ok(response);
            response = await response.json();

            this.titleTarget.value = response.title;
        } catch (e) {
            alert('Oops, something went wrong.');
            throw e;
        } finally {
            this.loadingValue = false;
        }
    }

    loadingValueChanged(loading) {
        if (loading) {
            this.sendButtonTarget.setAttribute('disabled', 'disabled')
            this.titleTarget.setAttribute('disabled', 'disabled')
        } else {
            this.sendButtonTarget.removeAttribute('disabled')
            this.titleTarget.removeAttribute('disabled')
        }
    }
}

import {Controller} from '@hotwired/stimulus';
import {useDebounce} from 'stimulus-use'
import {fetch, ok} from '../utils/http';
import router from '../utils/routing';
import Modal from 'bootstrap/js/dist/modal';

export default class extends Controller {
    static debounces = ['fetchTitle']
    static targets = ['sendButton', 'url', 'title', 'entries'];
    static values = {
        loading: Boolean,
    };

    connect() {
        useDebounce(this, {wait: 800})
    }

    async fetchDuplicates() {
        try {
            const url = router().generate('ajax_fetch_duplicates');
            let response = await fetch(url, {method: 'POST', body: JSON.stringify({'url': this.urlTarget.value})});

            response = await ok(response);
            response = await response.json();

            if (response.total > 0) {
                this.entriesTarget.innerHTML = response.html;
                (new Modal(document.getElementById('duplicatesModal'))).show();
            }
        } catch (e) {
            console.log(e);
        }
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

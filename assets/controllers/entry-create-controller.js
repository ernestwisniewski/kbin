import {Controller} from 'stimulus';
import KChoices from "./utils/choices";
import debounce from "./utils/debounce";
import {fetch, ok} from './utils/http';
import router from './utils/routing';

export default class extends Controller {
    static targets = ['sendButton', 'url', 'title'];
    static values = {
        loading: Boolean,
    };

    connect() {
        const choices = new KChoices();
        this.fetchTitle = debounce(this.fetchTitle, 800).bind(this);
    }

    async fetchTitle() {
        if (this.titleTarget.value) {
            return;
        }

        if(!this.urlTarget.value) {
            return;
        }

        this.loadingValue = true;

        try {
            let url = router().generate('ajax_fetch_title');
            let response = await fetch(url, {method: 'POST', body: JSON.stringify({'url': this.urlTarget.value})});

            response = await ok(response);
            response = await response.json();

            this.titleTarget.value = response.title;
        } catch (e) {
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

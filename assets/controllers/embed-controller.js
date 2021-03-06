import {Controller} from 'stimulus';
import {fetch, ok} from "./utils/http";
import router from "./utils/routing";

export default class extends Controller {
    static targets = ['embed', 'container', 'close'];
    static classes = ['hidden', 'loading', 'embed'];
    static values = {
        isVisible: Boolean,
        loading: Boolean,
        id: Number,
        html: String
    };

    async fetch(event) {
        event.preventDefault();

        if (this.isVisibleValue) {
            this.close();
            return;
        }

        if (this.htmlValue) {
            this.show();
            return;
        }

        this.loadingValue = true;

        try {
            let url = router().generate('ajax_fetch_embed', {id: this.idValue});

            let response = await fetch(url, {method: 'GET'});

            response = await ok(response);
            response = await response.json();

            this.htmlValue = response.html;
            this.show();
        } catch (e) {
            throw e;
        } finally {
            this.loadingValue = false;
        }
    }

    close() {
        this.containerTarget.innerHTML = '';
        this.containerTarget.classList.add(this.hiddenClass);
        this.closeTarget.classList.add(this.hiddenClass);
        this.isVisibleValue = false;
    }

    show() {
        this.containerTarget.innerHTML = this.htmlValue
        this.containerTarget.classList.remove(this.hiddenClass);
        this.closeTarget.classList.remove(this.hiddenClass);

        this.isVisibleValue = true;
    }

    loadingValueChanged() {
        if (this.loadingValue) {
            this.embedTarget.classList.remove(this.embedClass);
            this.embedTarget.classList.add(this.loadingClass);
        } else {
            if (this.hasEmbedTarget) {
                this.embedTarget.classList.remove(this.loadingClass);
                this.embedTarget.classList.add(this.embedClass);
            }
        }
    }
}

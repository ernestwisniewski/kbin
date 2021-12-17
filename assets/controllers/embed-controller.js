import {Controller} from '@hotwired/stimulus';
import {fetch, ok} from "../utils/http";
import router from "../utils/routing";

export default class extends Controller {
    static targets = ['embed', 'container', 'close'];
    static classes = ['hidden', 'loading', 'embed'];
    static values = {
        type: String,
        image: String,
        isVisible: Boolean,
        loading: Boolean,
        url: String,
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

        if(this.typeValue === 'image' && this.hasImageValue) {
            this.htmlValue = `<img src='${window.location.origin}/media/${this.imageValue}'>`;
            this.show();
            return;
        }

        this.loadingValue = true;

        try {
            if(this.typeValue === 'image'){
                return;
            }

            const url = router().generate('ajax_fetch_embed', {url: this.urlValue});

            let response = await fetch(url, {method: 'GET'});

            response = await ok(response);
            response = await response.json();

            this.htmlValue = response.html;
            this.show();
        } catch (e) {
            alert('Oops, something went wrong.');
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

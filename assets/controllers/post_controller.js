import {Controller} from '@hotwired/stimulus';
import {fetch, ok} from "../utils/http";
import router from "../utils/routing";
import getIdFromElement from "../utils/kbin";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['loader'];
    static values = {
        loading: Boolean
    };

    expand(event) {
        event.preventDefault();

        if (this.loadingValue === true) {
            return;
        }

        try {
            this.loadingValue = true;

            const url = router().generate('ajax_fetch_post_comments', {'id': getIdFromElement(this.element)});

            this.handleEntries(url);
        } catch (e) {
        } finally {
            event.target.closest('li').remove();
        }
    }

    async handleEntries(url) {
        let response = await fetch(url, {method: 'GET'});

        response = await ok(response);

        try {
            response = await response.json();
        } catch (e) {
            this.loadingValue = false;
            throw new Error('Invalid JSON response');
        }

        const preview = this.element.nextElementSibling;

        this.collapse(preview);

        preview.innerHTML = response.html;

        this.loadingValue = false;
    }

    collapse(preview) {
        if (false === preview.classList.contains('comments')) {
            return;
        }

        while (preview.firstChild) {
            preview.removeChild(preview.firstChild);
        }

        return preview;
    }

    loadingValueChanged(val) {
        this.loaderTarget.style.display = val === true ? 'block' : 'none';
    }
}
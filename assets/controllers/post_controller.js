import {Controller} from '@hotwired/stimulus';
import {fetch, ok} from "../utils/http";
import router from "../utils/routing";
import getIdFromElement from "../utils/kbin";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['loader', 'expand', 'collapse'];
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

        this.collapse(new Event('click'));

        const preview = this.element.nextElementSibling;
        preview.innerHTML = response.html;


        this.loadingValue = false;
        this.expandTarget.style.display = 'none';
        this.collapseTarget.style.display = 'block';
    }

    collapse(event) {
        event.preventDefault();

        const preview = this.element.nextElementSibling;

        if (false === preview.classList.contains('comments')) {
            return;
        }

        while (preview.firstChild) {
            preview.removeChild(preview.firstChild);
        }

        this.expandTarget.style.display = 'block';
        this.collapseTarget.style.display = 'none';
    }

    loadingValueChanged(val) {
        this.loaderTarget.style.display = val === true ? 'block' : 'none';
    }
}
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

    async expandComments(event) {
        event.preventDefault();

        if (this.loadingValue === true) {
            return;
        }

        try {
            this.loadingValue = true;

            const url = router().generate('ajax_fetch_post_comments', {'id': getIdFromElement(this.element)});

            let response = await fetch(url, {method: 'GET'});

            response = await ok(response);
            response = await response.json();

            this.collapseComments(new Event('click'));

            const preview = this.element.nextElementSibling;

            if (true === preview.classList.contains('comments')) {
                preview.innerHTML = response.html;
            } else {
                while (this.element.nextElementSibling && this.element.nextElementSibling.classList.contains('post-comment')) {
                    this.element.nextElementSibling.remove();
                }

                this.element.insertAdjacentHTML('afterend', response.html);
            }

            this.expandTarget.style.display = 'none';
            this.collapseTarget.style.display = 'block';
        } catch (e) {
        } finally {
            this.loadingValue = false;
        }
    }

    collapseComments(event) {
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

    async expandVoters(event) {
        event.preventDefault();

        try {
            this.loadingValue = true;

            let response = await fetch(event.target.href, {method: 'GET'});

            response = await ok(response);
            response = await response.json();

            event.target.parentNode.innerHTML = response.html;
        } catch (e) {
        } finally {
            this.loadingValue = false;
        }
    }

    loadingValueChanged(val) {
        this.loaderTarget.style.display = val === true ? 'block' : 'none';
    }
}
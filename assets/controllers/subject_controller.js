import {Controller} from '@hotwired/stimulus';
import router from "../utils/routing";
import getIdFromElement from "../utils/kbin";
import {fetch, ok} from "../utils/http";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['loader', 'more']

    connect() {
        const self = this;
        this.moreTarget.addEventListener('focusin', () => {
            self.element.parentNode
                .querySelectorAll('.z-100')
                .forEach((el) => {
                    el.classList.remove('z-100');
                });
            this.element.classList.add('z-100');
        });
    }

    async getCommentForm() {
        try {
            this.loadingValue = true;

            const url = router().generate('ajax_fetch_post_comments', {'id': getIdFromElement(this.element)});

            let response = await fetch(url, {method: 'GET'});

            response = await ok(response);
            response = await response.json();

            this.collapseComments(new Event('click'));

            const preview = this.element.nextElementSibling;
            preview.innerHTML = response.html;

            this.expandTarget.style.display = 'none';
            this.collapseTarget.style.display = 'block';
        } catch (e) {
        } finally {
            this.loadingValue = false;
        }
    }
}
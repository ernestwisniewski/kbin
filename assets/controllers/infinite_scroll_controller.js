import {Controller} from '@hotwired/stimulus';
import {fetch, ok} from "../utils/http";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['loader', 'pagination'];
    static values = {
        loading: Boolean
    };

    connect() {
        window.infiniteScrollUrls = [];

        let self = this;
        let observer = new IntersectionObserver(function (pagination, observer) {
            if (self.loadingValue === true) {
                return;
            }

            if (pagination[0].isIntersecting !== true) {
                return;
            }

            try {
                self.loadingValue = true;

                const paginationElem = pagination[0].target.getElementsByClassName('pagination__item--current-page')[0].nextElementSibling;
                if (paginationElem.classList.contains('pagination__item--disabled')) {
                    return;
                }

                if (window.infiniteScrollUrls.includes(paginationElem.href)) {
                    return;
                }

                window.infiniteScrollUrls.push(paginationElem.href);

                self.handleEntries(paginationElem.href);
            } catch (e) {
                self.showPagination();
            } finally {
                observer.unobserve(self.element);
            }
        }, {threshold: [0]});

        observer.observe(this.element);
    }

    async handleEntries(url) {
        let response = await fetch(url, {method: 'GET'});

        response = await ok(response);

        try {
            response = await response.json();
        } catch (e) {
            this.showPagination();
            throw new Error('Invalid JSON response');
        }

        let div = document.createElement('div');
        div.innerHTML = response.html;

        let elements = div.getElementsByClassName('subject');
        elements = [...elements];

        for (const element of elements) {
            if (null === document.getElementById(element.id)) {
                this.element.before(element);
            }
        }

        this.element.after(div.querySelector(`[data-controller='infinite-scroll']`));

        this.element.remove();

        this.application.getControllerForElementAndIdentifier(document.getElementById('main'), 'lightbox').connect();
    }

    loadingValueChanged(val) {
        this.loaderTarget.style.display = val === true ? 'block' : 'none';
    }

    showPagination() {
        this.loadingValue = false;
        this.paginationTarget.classList.remove('visually-hidden');
    }
}
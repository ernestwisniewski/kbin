import {Controller} from '@hotwired/stimulus';
import {fetch, ok} from "../utils/http";
import Cookies from "js-cookie";

export default class extends Controller {
    static targets = ['loading'];
    static values = {
        loading: Boolean
    };

    connect() {
        if (Cookies.get('user_option_infinite_scroll') === 'true') {
            // this.loadingValue = false;
            window.infiniteScrollUrls = [];
            this.handleInfiniteScroll()
        }
    }

    async handleInfiniteScroll() {
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

                const paginationElem = pagination[0].target.getElementsByClassName('active')[0].nextElementSibling;
                if (paginationElem.classList.contains('disabled')) {
                    return;
                }

                const url = paginationElem.getElementsByTagName('a')[0].href;
                if (window.infiniteScrollUrls.includes(url)) {
                    return;
                }

                window.infiniteScrollUrls.push(url)
                self.handleEntries(url);
            } catch (e) {
            } finally {
                this.loadingValue = false;
                observer.unobserve(self.element);
            }
        }, {threshold: [0]});

        observer.observe(this.element);
    }

    async handleEntries(url) {
        let response = await fetch(url, {method: 'GET'});

        response = await ok(response);
        response = await response.json();

        let div = document.createElement('div');
        div.innerHTML = response.html;

        let articles = div.getElementsByTagName('article');
        articles = [...articles];

        for (const article of articles) {
            if (null === document.getElementById(article.id)) {
                this.element.before(article);

                let comments = div.querySelector(`[data-comment-list-subject-id-value='${article.dataset.postIdValue}']`);
                if (comments) {
                    this.element.before(comments)
                }
            }
        }

        this.element.after(div.querySelector(`[data-controller='infinite-scroll']`));

        this.element.remove();
    }

    loadingValueChanged(val) {
        this.loadingTarget.style.display = val === true ? 'block' : 'none';
    }
}

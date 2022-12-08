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
            window.infiniteScrollInit = true;
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

        let articles = div.getElementsByTagName('article blockquote');

        for (const article of articles) {
            if (document.getElementById(article.id)) {
                continue;
            }

            this.element.append(article);
        }

        this.element.after(div.getElementsByTagName('div')[0]);

        let margin = document.createElement('div');
        margin.classList.add('mb-4');
        this.element.after(margin);

        this.element.remove();
    }

    loadingValueChanged(val) {
        this.loadingTarget.style.display = val === true ? 'block' : 'none';
    }
}

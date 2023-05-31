import {Controller} from '@hotwired/stimulus';
import {fetch, ok} from "../utils/http";
import router from "../utils/routing";
import getIntIdFromElement from "../utils/kbin";

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

        if (this.element.nextElementSibling.classList.contains('moderate-panel')) {
            this.element.nextElementSibling.remove();
        }

        try {
            this.loadingValue = true;

            const url = router().generate('ajax_fetch_post_comments', {'id': getIntIdFromElement(this.element)});

            let response = await fetch(url, {method: 'GET'});

            response = await ok(response);
            response = await response.json();

            this.collapseComments(new Event('click'));

            const preview = this.element.nextElementSibling;
            preview.style.display = 'block';

            if (true === preview.classList.contains('comments')) {
                preview.innerHTML = response.html;
                if (preview.children.length && preview.children[0].classList.contains('comments')) {
                    const container = preview.children[0];
                    const parentDiv = container.parentNode;
                    container.classList.add('post-comments');

                    while (container.firstChild) {
                        parentDiv.insertBefore(container.firstChild, container);
                    }

                    parentDiv.removeChild(container);
                }
            } else {
                while (this.element.nextElementSibling && this.element.nextElementSibling.classList.contains('post-comment')) {
                    this.element.nextElementSibling.remove();
                }

                this.element.insertAdjacentHTML('afterend', response.html);
            }

            this.expandTarget.style.display = 'none';
            this.collapseTarget.style.display = 'block';

            this.application
                .getControllerForElementAndIdentifier(document.getElementById('main'), 'lightbox')
                .connect();
            this.application
                .getControllerForElementAndIdentifier(document.getElementById('main'), 'timeago')
                .connect();
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
        preview.style.display = 'none';
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
        const subjectController = this.application.getControllerForElementAndIdentifier(this.element, 'subject');
        if (null !== subjectController) {
            subjectController.loadingValue = val;
        }
    }

}
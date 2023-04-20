import {Controller} from '@hotwired/stimulus';
import router from "../utils/routing";
import {fetch, ok} from "../utils/http";
import {getLevel, getTypeFromNotification} from "../utils/kbin";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    addComment(data) {
        if (!document.getElementById(data.detail.parentSubject.htmlId)) {
            return;
        }

        this.addMainSubject(data);
    }

    async addMainSubject(data) {
        try {
            const url = router().generate(`ajax_fetch_${getTypeFromNotification(data)}`, {id: data.detail.id});

            let response = await fetch(url);

            response = await ok(response);
            response = await response.json();

            if (!data.detail.parent) {
                if (!document.getElementById(data.detail.htmlId)) {
                    this.element.insertAdjacentHTML('afterbegin', response.html);
                }

                return;
            }

            const parent = document.getElementById(data.detail.parent.htmlId);
            if (parent) {
                const div = document.createElement('div');
                div.innerHTML = response.html;

                let level = getLevel(parent);

                div.firstElementChild.classList.add('comment-level--' + (level >= 10 ? 10 : level + 1));

                let current = parent;
                while (current) {
                    if (!current.nextElementSibling) {
                        break;
                    }
                    if (current.nextElementSibling.dataset.subjectParentValue === 'undefined') {
                        break;
                    }
                    if (current.nextElementSibling.dataset.subjectParentValue !== div.firstElementChild.dataset.subjectParentValue
                        && getLevel(current.nextElementSibling) <= level) {
                        break
                    }

                    current = current.nextElementSibling;
                }

                if (!document.getElementById(div.firstElementChild.id)) {
                    current.insertAdjacentElement('afterend', div.firstElementChild);
                }
            }
        } catch (e) {
        } finally {
            this.application
                .getControllerForElementAndIdentifier(document.getElementById('main'), 'timeago')
                .connect();
        }
    }

    async addCommentOverview(data) {
        try {
            const parent = document.getElementById(data.detail.parentSubject.htmlId);
            if (!parent) {
                return;
            }

            const url = router().generate(`ajax_fetch_${getTypeFromNotification(data)}`, {id: data.detail.id});

            let response = await fetch(url);

            response = await ok(response);
            response = await response.json();

            const div = document.createElement('div');
            div.innerHTML = response.html;

            div.firstElementChild.classList.add('comment-level--2');

            if (!parent.nextElementSibling || !parent.nextElementSibling.classList.contains('comments')) {
                const comments = document.createElement('div');
                comments.classList.add('comments', 'post-comments', 'comments-tree');
                parent.insertAdjacentElement('afterend', comments);
            }

            parent.classList.add('mb-0');
            if (parent.nextElementSibling.querySelector('#' + data.detail.htmlId)) {
                return;
            }

            parent.nextElementSibling.appendChild(div.firstElementChild);
        } catch (e) {
        } finally {
            this.application
                .getControllerForElementAndIdentifier(document.getElementById('main'), 'timeago')
                .connect();
        }
    }
}

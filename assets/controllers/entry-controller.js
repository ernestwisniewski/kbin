import {Controller} from '@hotwired/stimulus';
import router from "../utils/routing";
import {fetch, ok} from "../utils/http";

export default class extends Controller {
    static targets = ['commentCounter'];
    static values = {
        id: Number,
    };

    remove(notification) {
        if (this.idValue !== notification.detail.id) {
            return;
        }

        this.element.remove();
    }

    async edit(notification) {
        if (this.idValue !== notification.detail.id) {
            return;
        }

        try {
            const url = router().generate('ajax_fetch_entry', {'id': notification.detail.id});

            let response = await fetch(url, {method: 'GET'});

            response = await ok(response);
            response = await response.json();

            this.element.outerHTML = response.html;
        } catch (e) {
        }
    }

    increaseComments(notification) {
        if (this.idValue === notification.detail.subject.id && this.hasCommentCounterTarget) {
            this.commentCounterTarget.textContent = Number(this.commentCounterTarget.textContent) + 1;
        }
    }

    decreaseComments(notification) {
        if (this.idValue === notification.detail.subject.id && this.hasCommentCounterTarget) {
            this.commentCounterTarget.textContent = Number(this.commentCounterTarget.textContent) - 1;
        }
    }
}

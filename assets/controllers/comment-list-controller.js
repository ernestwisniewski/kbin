import {ApplicationController} from 'stimulus-use'
import CommentFactory from "../utils/comment-factory";
import router from "../utils/routing";
import {fetch, ok} from "../utils/http";

export default class extends ApplicationController {
    static values = {
        subjectId: Number,
    };

    async add(notification) {
        const subjectId = notification.detail.subject.id;
        const route = notification.detail.op === 'EntryCommentCreatedNotification' ? 'ajax_fetch_entry_comment' : 'ajax_fetch_post_comment';

        if (this.hasSubjectIdValue && this.subjectIdValue !== subjectId) {
            return;
        }

        try {
            const url = router().generate(route, {'id': notification.detail.id});

            let response = await fetch(url, {method: 'GET'});

            response = await ok(response);
            response = await response.json();

            const html = response.html;

            let div = document.createElement('div');
            div.innerHTML = html;

            let parent = div.firstElementChild.dataset.commentParentValue;
            parent = this.element.querySelector(`[data-comment-id-value='${parent}']`);

            const existed = div.firstElementChild.dataset.commentIdValue;
            if (this.element.querySelector(`[data-comment-id-value='${existed}']`)) {
                return;
            }

            if (parent) {
                notification.detail.op === 'EntryCommentCreatedNotification'
                    ? CommentFactory.create(html, parent)
                    : this.element.append(div.firstElementChild);
            } else {
                notification.detail.op === 'EntryCommentCreatedNotification'
                    ? this.element.prepend(div.firstElementChild)
                    : this.element.append(div.firstElementChild);
            }
        } catch (e) {
        }
    }
}

import {ApplicationController} from 'stimulus-use'
import CommentFactory from "../utils/comment-factory";

export default class extends ApplicationController {
    static values = {
        subjectId: Number,
    };

    connect() {
    }

    add(notification) {
        const subjectId = notification.detail.data.entry.id ?? notification.detail.data.post.id;
        if (this.hasSubjectIdValue && this.subjectIdValue !== subjectId) {
            return;
        }

        let html = notification.detail.html;
        let div = document.createElement('div');
        div.innerHTML = html;

        let parent = div.firstElementChild.dataset.commentParentValue;
        parent = this.element.querySelector(`[data-comment-id-value='${parent}']`);

        if (parent) {
            CommentFactory.create(html, parent);
        } else {
            this.element.prepend(div.firstElementChild);
        }
    }
}

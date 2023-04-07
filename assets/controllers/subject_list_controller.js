import {Controller} from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    notification(data) {
        if (data.detail.op.endsWith('CreatedNotification')) {
            this.createSubject(data);
        }
    }

    createSubject(data) {
        if (document.getElementById(data.detail.htmlId)) {
            return
        }

        if (data.detail.op.endsWith('CommentCreatedNotification')) {
            const container = this.element.querySelector('.comments');
            if (!container) {
                return;
            }
        } else {

        }
    }
}

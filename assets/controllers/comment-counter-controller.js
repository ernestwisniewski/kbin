import {ApplicationController} from 'stimulus-use'

export default class extends ApplicationController {
    static targets = ['lenght'];
    static values = {
        subjectId: Number,
    };

    increase(notification) {
        if (this.subjectIdValue === notification.detail.subject.id && this.hasLenghtTarget) {
            this.lenghtTarget.textContent = Number(this.lenghtTarget.textContent) + 1;
        }
    }
}

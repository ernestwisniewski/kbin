import {ApplicationController} from 'stimulus-use'

export default class extends ApplicationController {
    static targets = ['upVotes', 'downVotes'];
    static values = {
        subjectId: Number,
    };

    refresh(notification) {
        if(this.subjectIdValue === notification.detail.id){
            this.upVotesTarget.textContent = notification.detail.up;
            this.downVotesTarget.textContent = notification.detail.down;
        }
    }
}

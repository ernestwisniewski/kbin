import {Controller} from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    notification(data) {
        if (data.detail.parentSubject) {
            const parent = document.getElementById(data.detail.parentSubject.htmlId)

            if (data.detail.op.includes('CommentCreatedNotification')) {
                this.increaseCommentsCounter(parent);
            }
            if (data.detail.op.includes('CommentDeletedNotification')) {
                this.decreaseCommentsCounter(parent);
            }
        }

        const subject = document.getElementById(data.detail.htmlId)

        if (data.detail.op.includes('Vote')) {
            this.updateVotes(subject, data);
        }

        if (data.detail.op.includes('Favourite')) {
            this.updateFavourites(subject, data);
        }
    }

    updateVotes(subject, data) {
        const upButton = subject.querySelector('.vote__up button');

        upButton.replaceChild(document.createTextNode(data.detail.up + ' '), upButton.firstChild);

        const downButton = subject.querySelector('.vote__down button');
        if (downButton) {
            downButton.replaceChild(document.createTextNode(data.detail.down + ' '), downButton.firstChild);
        }
    }

    increaseCommentsCounter(subject) {
        const subjectController = this.application.getControllerForElementAndIdentifier(subject, 'subject')
        if (subjectController.hasCommentsCounterTarget) {
            subjectController.commentsCounterTarget.innerText = parseInt(subjectController.commentsCounterTarget.innerText) + 1;
        }
    }

    decreaseCommentsCounter(subject) {
        const subjectController = this.application.getControllerForElementAndIdentifier(subject, 'subject')
        if (subjectController.hasCommentsCounterTarget) {
            subjectController.commentsCounterTarget.innerText = parseInt(subjectController.commentsCounterTarget.innerText) - 1;
        }
    }

    updateFavourites(subject, data) {
        const subjectController = this.application.getControllerForElementAndIdentifier(subject, 'subject')
        if (subjectController.hasFavCounterTarget) {
            subjectController.favCounterTarget.parentElement.classList.remove('hidden');
            subjectController.favCounterTarget.innerText = data.detail.count;
        }
    }
}

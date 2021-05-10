import {ApplicationController} from 'stimulus-use'

export default class extends ApplicationController {
    static targets = ['notifications', 'messages']
    static classes = ['hidden']

    notification(event) {
        let elem = this.notificationsTarget.getElementsByTagName('span')[0];
        elem.innerHTML = parseInt(elem.innerHTML) + 1;

        this.notificationsTarget.classList.remove(this.hiddenClass);
    }

    message(event) {
        let elem = this.messagesTarget.getElementsByTagName('span')[0];
        elem.innerHTML = parseInt(elem.innerHTML) + 1;

        this.messagesTarget.classList.remove(this.hiddenClass);
    }
}

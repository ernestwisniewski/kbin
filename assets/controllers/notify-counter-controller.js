import {ApplicationController} from 'stimulus-use'

export default class extends ApplicationController {
    static targets = ['notifications', 'messages']
    static classes = ['hidden']

    notification(event) {
        if(!this.hasNotificationsTarget || !window.KBIN_LOGGED_IN) {
            return;
        }

        if(window.notifyCounter) {
            clearTimeout(window.notifyCounter);
        }

        window.notifyCounter = setTimeout(() => {
            // retrieve notifications count
        }, Math.floor(Math.random() * (10000 - 1000 + 1)) + 1000);

        // let elem = this.notificationsTarget.getElementsByTagName('span')[0];
        // elem.innerHTML = parseInt(elem.innerHTML) + 1;
        //
        // this.notificationsTarget.classList.remove(this.hiddenClass);
    }

    message(event) {
        if(!this.hasMessagesTarget) {
            return;
        }

        let elem = this.messagesTarget.getElementsByTagName('span')[0];
        elem.innerHTML = parseInt(elem.innerHTML) + 1;

        this.messagesTarget.classList.remove(this.hiddenClass);
    }
}

import { ApplicationController } from 'stimulus-use'
import { fetch, ok } from "../utils/http";
import router from "../utils/routing";
import Cookies from 'js-cookie';

export default class extends ApplicationController {
    static added = [];
    static values = {
        magazineName: String,
        counter: Boolean
    };

    async add(notification) {
        const magazineName = notification.detail.magazine.name;
        if (this.hasMagazineNameValue && this.magazineNameValue !== magazineName) {
            return;
        }

        if (document.getElementById(`post-${notification.detail.id}`)) {
            return;
        }

        if (Cookies.get('user_option_auto_refresh') === 'true') {
            try {
                const url = router().generate('ajax_fetch_post', { 'id': notification.detail.id });

                let response = await fetch(url, { method: 'GET' });

                response = await ok(response);
                response = await response.json();

                this.element.insertAdjacentHTML('afterbegin', response.html);
            } catch (e) {
            }
        } else {
            if(this.counterValue) {
                this.increaseCounter();
            }
        }
    }

    async refresh() {
        try {
            let response = await fetch(window.location.href, { method: 'GET' });

            response = await ok(response);
            response = await response.json();

            const html = response.html;

            let div = document.createElement('div');
            div.innerHTML = html;

            this.element.replaceWith(div);

            this.resetCounter();
        } catch (e) {
        }
    }

    increaseCounter() {
        let notificationCounter = document.getElementById('kbin-activity-counter');
        notificationCounter.innerHTML = parseInt(notificationCounter.innerHTML) + 1;
        notificationCounter.parentElement.classList.remove('visually-hidden');
    }

    resetCounter() {
        let notificationCounter = document.getElementById('kbin-activity-counter');
        notificationCounter.innerHTML = 0;
        notificationCounter.parentElement.classList.add('visually-hidden');
    }
}

import { ApplicationController } from 'stimulus-use'
import { fetch, ok } from "../utils/http";
import router from "../utils/routing";
import Cookies from 'js-cookie';

export default class extends ApplicationController {
    static values = {
        magazineName: String,
        counter: Boolean
    };

    async add(notification) {
        const magazineName = notification.detail.magazine.name;
        if (this.hasMagazineNameValue && this.magazineNameValue !== magazineName) {
            return;
        }

        if (document.getElementById(`entry-${notification.detail.id}`)) {
            return;
        }

        if (Cookies.get('user_option_auto_refresh') === 'true') {
            try {
                const url = router().generate('ajax_fetch_entry', { 'id': notification.detail.id });

                let response = await fetch(url, { method: 'GET' });

                response = await ok(response);
                response = await response.json();

                const html = response.html;

                let div = document.createElement('div');
                div.innerHTML = html;

                this.element.prepend(div);
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
            let url = window.location.href;

            let pagination = this.element.getElementsByClassName('pagination');
            if(pagination.length) {
                let items = pagination[0].getElementsByTagName('li');
                for (let item of items) {
                    if(item.firstChild.innerHTML == 1) {
                        url = item.firstChild.href;
                        console.log(url);
                    }
                }
            }

            let response = await fetch(url, { method: 'GET' });

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

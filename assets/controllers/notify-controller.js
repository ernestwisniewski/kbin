import bootstrap from "bootstrap/dist/js/bootstrap.min";
import {ApplicationController} from 'stimulus-use'
import Subscribe from '../event-source';

export default class extends ApplicationController {
    static values = {
        magazineName: String,
        username: String
    };

    connect() {
        this.es(
            this.getTopics()
        );
    }

    es(topics) {
        if (document.es !== undefined) {
            document.es.close();
        }

        let self = this;
        let cb = function (e) {
            let data = JSON.parse(e.data);

            if (data.toast) {
                self.toast(data.toast);
            }

            if (data.op.includes('Notification')) {
                self.dispatch('Notification', data);
            }

            self.dispatch(data.op, data);
        }

        document.es = Subscribe(topics, cb);
        // firefox bug: https://github.com/dunglas/mercure/issues/339#issuecomment-650978605
        if(navigator.userAgent.toLowerCase().indexOf('firefox') > -1){
            document.es.onerror = (e) => {
                Subscribe(topics, cb);
            };
        }
    }

    getTopics() {
        let topics = [
            'pub',
        ];

        if(this.hasMagazineNameValue) {
            topics = [
                `/api/magazines/${this.magazineNameValue}`,
            ]
        }
        
        if (this.hasUsernameValue) {
            topics = [
                `/api/magazines/${this.hasMagazineNameValue ? this.magazineNameValue : '{id}'}`,
                `/api/user/${this.usernameValue}`,
            ]
        }

        return topics;
    }

    toast(html) {
        let div = document.createElement('div');
        div.innerHTML = html;
        div = div.firstElementChild;

        let container = document.querySelector('.kbin-toast-container')
        container.append(div);

        let t = new bootstrap.Toast(div);
        t.show();
    }
}

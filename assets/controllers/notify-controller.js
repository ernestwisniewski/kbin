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
        document.es = Subscribe(topics, function (e) {
            let data = JSON.parse(e.data);
            self.toast(data.toast);

            self.dispatch(data.op, data);

            if (data.op.includes('Notification')) {
                self.dispatch('Notification', data);
            }

            if (data.op === 'EntryNotification') {
                self.dispatch('EntryNotification', data);
            }
        });
    }

    getTopics() {
        let topics = [
            'pub',
        ];

        if (this.hasUsernameValue) {
            topics = [
                `/api/magazines/${this.HasMagazineNameValue ? this.magazineNameValue : '{id}'}`,
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

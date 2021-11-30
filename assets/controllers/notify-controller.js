import bootstrap from "bootstrap/dist/js/bootstrap.min";
import {ApplicationController} from 'stimulus-use'
import Subscribe from '../event-source';

export default class extends ApplicationController {
    static values = {
        magazineName: String,
        username: String,
        entryId: Number
    };

    connect() {
        this.es(
            this.getTopics()
        );

        window.onbeforeunload = function (event) {
            if (document.es !== undefined) {
                document.es.close();
            }
        };
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
        if (navigator.userAgent.toLowerCase().indexOf('firefox') > -1) {
            document.es.onerror = (e) => {
                Subscribe(topics, cb);
            };
        }
    }

    getTopics() {
        let topics = [
            'pub',
            'count'
        ];

        if (this.hasMagazineNameValue || this.hasUsernameValue || this.hasPostIdValue) {
            topics = [
                'count',
            ]

            if (this.hasMagazineNameValue) {
                topics.push(`/api/magazines/${this.magazineNameValue}`);
            } else {
                topics.push(`/api/magazines/{id}`);
            }

            if (this.hasUsernameValue) {
                topics.push(`/api/user/${this.usernameValue}`);
            }

            if (this.hasEntryIdValue) {
                topics.push(`/api/entries/${this.entryIdValue}`);
            }
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

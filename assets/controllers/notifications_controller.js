import {Controller} from '@hotwired/stimulus';
import Subscribe from '../utils/event-source';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {
        magazineName: String,
    }

    connect() {
        this.es(this.getTopics());

        window.onbeforeunload = function (event) {
            if (window.es !== undefined) {
                window.es.close();
            }
        };
    }

    es(topics) {
        if (window.es !== undefined) {
            window.es.close();
        }

        let self = this;
        let cb = function (e) {
            let data = JSON.parse(e.data);

            if (data.op.endsWith('Notification')) {
                self.dispatch('Notification', data);
            }

            self.notify(data);
            self.dispatch(data.op, data);
        }

        window.es = Subscribe(topics, cb);
        // firefox bug: https://github.com/dunglas/mercure/issues/339#issuecomment-650978605
        if (navigator.userAgent.toLowerCase().indexOf('firefox') > -1) {
            window.es.onerror = (e) => {
                Subscribe(topics, cb);
            };
        }
    }

    getTopics() {
        const topics = [
            'pub',
            'count'
        ];


        return topics;
    }

    notify(data) {

    }
}
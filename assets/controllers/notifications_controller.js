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

            self.dispatch(data.op, {detail: data});

            self.dispatch('Notification', {detail: data});

            // if (data.op.includes('Create')) {
            //     self.dispatch('CreatedNotification', {detail: data});
            // }

            // if (data.op === 'EntryCreatedNotification' || data.op === 'PostCreatedNotification') {
            //     self.dispatch('MainSubjectCreatedNotification', {detail: data});
            // }
            //
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
        let pub = true;
        const topics = [
            'count'
        ]

        if (window.KBIN_USER) {
            topics.push(`/api/users/${window.KBIN_USER}`);
            pub = true;
        }

        if (window.KBIN_MAGAZINE) {
            topics.push(`/api/magazines/${window.KBIN_MAGAZINE}`);
            pub = false;
        }

        if (window.KBIN_ENTRY_ID) {
            topics.push(`/api/entries/${window.KBIN_ENTRY_ID}`);
            pub = false;
        }

        if (window.KBIN_POST_ID) {
            topics.push(`/api/posts/${window.KBIN_POST_ID}`);
            pub = false;
        }

        if (pub) {
            topics.push('pub');
        }

        return topics;
    }
}
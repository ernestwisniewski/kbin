import Cookies from 'js-cookie';

export default function
    subscribe(topics, cb) {

    const url = new URL(window.MERCURE_PUBLISH_URL, window.origin);

    topics.forEach(topic => {
        url.searchParams.append('topic', topic);
    })

    const eventSource = new EventSource(url);
    eventSource.onmessage = e => cb(e);

    return eventSource;
}

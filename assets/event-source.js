export default function
    subscribe(topics, cb) {
    const url = new URL('https://localhost/.well-known/mercure', window.origin);

    topics.forEach(topic => {
        url.searchParams.append('topic', topic);
    })

    const eventSource = new EventSource(url);
    eventSource.onmessage = e => cb(e);

    return eventSource;
}

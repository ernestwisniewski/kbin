export default function
    subscribe(uri = '', cb) {
    const url = new URL('https://localhost/.well-known/mercure');
    url.searchParams.append('topic', uri);

    const eventSource = new EventSource(url);
    eventSource.onmessage = e => cb(e);
}

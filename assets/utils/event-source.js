export default function
    subscribe(topics, cb) {
    const mercureElement = document.getElementById("mercure-url");

    if (!mercureElement) {
        return;
    }

    const url = new URL(mercureElement.textContent.trim());

    topics.forEach(topic => {
        url.searchParams.append('topic', topic);
    })

    const eventSource = new EventSource(url);
    eventSource.onmessage = e => cb(e);

    return eventSource;
}

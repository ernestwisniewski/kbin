export default function
    subscribe(topics, cb) {
    const url = new URL(document.getElementById("mercure-url").textContent.trim());

    topics.forEach(topic => {
        url.searchParams.append('topic', topic);
    })

    const eventSource = new EventSource(url);
    eventSource.onmessage = e => cb(e);

    return eventSource;
}

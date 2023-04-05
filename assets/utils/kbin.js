export default function getIntIdFromElement(element) {
    return element.id.substring(element.id.lastIndexOf("-") + 1);
}

export function getIdPrefixFromNotification(type) {
    switch (type) {
        case 'Entry':
            return 'entry-';
        case 'EntryComment':
            return 'entry-comment-';
        case 'Post':
            return 'post-';
        case 'PostComment':
            return 'post-comment-';
    }
}

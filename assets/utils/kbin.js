export default function getIntIdFromElement(element) {
    return element.id.substring(element.id.lastIndexOf("-") + 1);
}

export function getIdPrefixFromNotification(data) {
    switch (data.type) {
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

export function getTypeFromNotification(data) {
    switch (data.detail.op) {
        case 'EntryEditedNotification':
        case 'EntryCreatedNotification':
            return 'entry';
        case 'EntryCommentEditedNotification':
        case 'EntryCommentCreatedNotification':
            return 'entry_comment';
        case 'PostEditedNotification':
        case 'PostCreatedNotification':
            return 'post';
        case 'PostCommentEditedNotification':
        case 'PostCommentCreatedNotification':
            return 'post_comment';
    }
}

export function getLevel(element) {
    let level = parseInt(element.className.split('--')[1]);
    return isNaN(level) ? 1 : level;
}
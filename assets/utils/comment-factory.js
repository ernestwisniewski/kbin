export default class CommentFactory {
    static create(html, parent, nested) {
        const id = parent.dataset.commentIdValue;

        let level = parent.dataset.commentLevelValue;
        const div = document.createElement('div');
        div.innerHTML = html;

        // Create node
        level = (level >= 7 ? 7 : parseInt(level) + 1);
        div.firstElementChild.classList.add('kbin-comment-level--' + level);
        div.firstElementChild.dataset.commentLevelValue = level;

        let children = parent
            .parentNode
            .querySelectorAll(`[data-comment-parent-value='${id}']`);

        if (children.length && nested) {
            let child = children[children.length - 1];

            while (true) {
                if (!child.nextElementSibling.dataset.commentLevelValue || (child.nextElementSibling.dataset.commentLevelValue <= Number(level))) {
                    break;
                }

                child = child.nextElementSibling;
            }

            child.parentNode.insertBefore(div.firstElementChild, child.nextSibling);
        } else {
            parent
                .parentNode
                .insertBefore(div.firstElementChild, parent.nextSibling);
        }
    }

    static edit(html, parent) {
        const level = parent.dataset.commentLevelValue;

        let div = document.createElement('div');
        div.innerHTML = html;

        // Edit node
        div.firstElementChild.classList.add('kbin-comment-level--' + level);
        div.firstElementChild.dataset.commentLevelValue = level;
        parent.replaceWith(div.firstElementChild);
    }
}

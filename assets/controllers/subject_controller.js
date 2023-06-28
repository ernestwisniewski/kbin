import {Controller} from '@hotwired/stimulus';
import {fetch, ok} from "../utils/http";
import {useIntersection} from 'stimulus-use'
import router from "../utils/routing";
import getIntIdFromElement, {getLevel, getTypeFromNotification} from "../utils/kbin";
import GLightbox from 'glightbox';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static previewInit = false;
    static targets = ['loader', 'more', 'container', 'commentsCounter', 'favCounter', 'upvoteCounter', 'downvoteCounter']
    static values = {
        loading: Boolean,
        isExpandedValue: Boolean
    };
    static sendBtnLabel = null;

    connect() {
        const params = {selector: '.thumb', openEffect: 'none', closeEffect: 'none', slideEffect: 'none'};
        GLightbox(params);

        const self = this;
        this.moreTarget.addEventListener('focusin', () => {
            self.element.parentNode
                .querySelectorAll('.z-5')
                .forEach((el) => {
                    el.classList.remove('z-5');
                });
            this.element.classList.add('z-5');
        });

        if (this.element.classList.contains('show-preview')) {
            useIntersection(this)
        }

        this.checkHeight();
        this.handleAdultThumbs()
    }
    
    async getForm(event) {
        event.preventDefault();

        if ('' !== this.containerTarget.innerHTML.trim()) {
            if (false === confirm('Do you really want to leave?')) {
                return;
            }
        }

        try {
            this.loadingValue = true;

            let response = await fetch(event.target.href, {method: 'GET'});

            response = await ok(response);
            response = await response.json();

            this.containerTarget.style.display = 'block';
            this.containerTarget.innerHTML = response.form;

            const textarea = this.containerTarget.querySelector('textarea');
            if (textarea) {
                let firstLineEnd = textarea.value.indexOf("\n");
                if (-1 === firstLineEnd) {
                    firstLineEnd = textarea.value.length;
                    textarea.value = textarea.value.slice(0, firstLineEnd) + " " + textarea.value.slice(firstLineEnd);
                    textarea.selectionStart = firstLineEnd + 1;
                    textarea.selectionEnd = firstLineEnd + 1;
                } else {
                    textarea.value = textarea.value.slice(0, firstLineEnd) + " " + textarea.value.slice(firstLineEnd);
                    textarea.selectionStart = firstLineEnd + 1;
                    textarea.selectionEnd = firstLineEnd + 1;
                }

                textarea.focus();
            }
        } catch (e) {
            window.location.href = event.target.href;
        } finally {
            this.loadingValue = false;
            popover.togglePopover(false);
        }
    }

    async sendForm(event) {
        event.preventDefault();

        const form = event.target.closest('form');
        const url = form.action;

        try {
            this.loadingValue = true;
            self.sendBtnLabel = event.target.innerHTML;
            event.target.disabled = true;
            event.target.innerHTML = 'Sending...';

            let response = await fetch(url, {
                method: 'POST',
                body: new FormData(form)
            });

            response = await ok(response);
            response = await response.json();

            if (response.form) {
                this.containerTarget.style.display = 'block';
                this.containerTarget.innerHTML = response.form;
            } else if (form.classList.contains('replace')) {
                const div = document.createElement('div');
                div.innerHTML = response.html;
                div.firstElementChild.className = this.element.className;

                this.element.innerHTML = div.firstElementChild.innerHTML;
            } else {
                const div = document.createElement('div');
                div.innerHTML = response.html;

                let level = getLevel(this.element);

                div.firstElementChild.classList.add('comment-level--' + (level >= 10 ? 10 : level + 1));

                if (this.element.nextElementSibling && this.element.nextElementSibling.classList.contains('comments')) {
                    this.element.nextElementSibling.appendChild(div.firstElementChild);
                    this.element.classList.add('mb-0');
                } else {
                    this.element.parentNode.insertBefore(div.firstElementChild, this.element.nextSibling);
                }

                this.containerTarget.style.display = 'none';
                this.containerTarget.innerHTML = '';
            }
        } catch (e) {
            // this.containerTarget.innerHTML = '';
        } finally {
            this.application
                .getControllerForElementAndIdentifier(document.getElementById('main'), 'lightbox')
                .connect();
            this.application
                .getControllerForElementAndIdentifier(document.getElementById('main'), 'timeago')
                .connect();
            this.loadingValue = false;
            event.target.disabled = false;
            event.target.innerHTML = self.sendBtnLabel;
        }

    }

    async favourite(event) {
        event.preventDefault();

        const form = event.target.closest('form');

        try {
            this.loadingValue = true;

            let response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form)
            });

            response = await ok(response);
            response = await response.json();

            form.innerHTML = response.html;
        } catch (e) {
            form.submit();
        } finally {
            this.loadingValue = false;
        }
    }

    async vote(event) {
        event.preventDefault();

        const form = event.target.closest('form');

        try {
            this.loadingValue = true;

            let response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form)
            });

            response = await ok(response);
            response = await response.json();

            event.target.closest('.vote').outerHTML = response.html;
        } catch (e) {
            form.submit();
        } finally {
            this.loadingValue = false;
        }
    }

    loadingValueChanged(val) {
        const submitButton = this.containerTarget.querySelector('form button[type="submit"]');

        if (true === val) {
            if (submitButton) {
                submitButton.disabled = true;
            }
            this.loaderTarget.style.display = 'block';
        } else {
            if (submitButton) {
                submitButton.disabled = false;
            }
            this.loaderTarget.style.display = 'none';
        }
    }

    async showModPanel(event) {
        event.preventDefault();

        let container = this.element.nextElementSibling && this.element.nextElementSibling.classList.contains('js-container') ? this.element.nextElementSibling : null;
        if (null === container) {
            container = document.createElement('div');
            container.classList.add('js-container');
            this.element.insertAdjacentHTML('afterend', container.outerHTML);
        } else {
            if (container.querySelector('.moderate-panel')) {
                return;
            }
        }

        try {
            this.loadingValue = true;

            let response = await fetch(event.target.href);

            response = await ok(response);
            response = await response.json();

            this.element.nextElementSibling.insertAdjacentHTML('afterbegin', response.html);
        } catch (e) {
            window.location.href = event.target.href;
        } finally {
            this.loadingValue = false;
        }
    }

    notification(data) {
        if (data.detail.parentSubject && this.element.id === data.detail.parentSubject.htmlId) {
            if (data.detail.op.endsWith('CommentDeletedNotification') || data.detail.op.endsWith('CommentCreatedNotification')) {
                this.updateCommentCounter(data);
            }
        }

        if (this.element.id !== data.detail.htmlId) {
            return;
        }

        if (data.detail.op.endsWith('EditedNotification')) {
            this.refresh(data);
            return;
        }

        if (data.detail.op.endsWith('DeletedNotification')) {
            this.element.remove();
            return;
        }

        if (data.detail.op.endsWith('Vote')) {
            this.updateVotes(data);
            return;
        }

        if (data.detail.op.endsWith('Favourite')) {
            this.updateFavourites(data);
            return;
        }
    }

    async refresh(data) {
        try {
            this.loadingValue = true;

            const url = router().generate(`ajax_fetch_${getTypeFromNotification(data)}`, {id: getIntIdFromElement(this.element)});

            let response = await fetch(url);

            response = await ok(response);
            response = await response.json();

            const div = document.createElement('div');
            div.innerHTML = response.html;

            div.firstElementChild.className = this.element.className;
            this.element.outerHTML = div.firstElementChild.outerHTML;
        } catch (e) {
        } finally {
            this.loadingValue = false;
        }
    }

    updateVotes(data) {
        this.upvoteCounterTarget.innerText = `(${data.detail.up})`;

        if(data.detail.up > 0) {
            this.upvoteCounterTarget.classList.remove('hidden');
        } else {
            this.upvoteCounterTarget.classList.add('hidden');
        }

        if (this.hasDownvoteCounterTarget) {
            this.downvoteCounterTarget.innerText = data.detail.down;
        }
    }

    updateFavourites(data) {
        if (this.hasFavCounterTarget) {
            this.favCounterTarget.innerText = data.detail.count;
        }
    }

    updateCommentCounter(data) {
        if (data.detail.op.endsWith('CommentCreatedNotification') && this.hasCommentsCounterTarget) {
            this.commentsCounterTarget.innerText = parseInt(this.commentsCounterTarget.innerText) + 1;
        }

        if (data.detail.op.endsWith('CommentDeletedNotification') && this.hasCommentsCounterTarget) {
            this.commentsCounterTarget.innerText = parseInt(this.commentsCounterTarget.innerText) - 1;
        }
    }

    async removeImage(event) {
        event.preventDefault();

        try {
            this.loadingValue = true;

            let response = await fetch(event.target.parentNode.formAction, {method: 'POST'});

            response = await ok(response);
            response = await response.json();

            event.target.parentNode.previousElementSibling.remove();
            event.target.parentNode.nextElementSibling.classList.remove('hidden');
            event.target.parentNode.remove();
        } catch (e) {
        } finally {
            this.loadingValue = false;
        }
    }

    appear() {
        if (this.previewInit) {
            return;
        }

        const prev = this.element.querySelectorAll('.show-preview');

        prev.forEach((el) => {
            el.click();
        });

        this.previewInit = true;
    }

    checkHeight() {
        this.isExpandedValue = false;
        const elem = this.element.querySelector('.content');
        elem.style.maxHeight = '25rem'

        if (elem.scrollHeight - 30 > elem.clientHeight
            || elem.scrollWidth > elem.clientWidth) {

            this.moreBtn = this.createMoreBtn(elem);
            this.more();
        } else {
            elem.style.maxHeight = null;
        }
    }

    createMoreBtn(elem) {
        let moreBtn = document.createElement('div')
        moreBtn.innerHTML = '<i class="fa-solid fa-angles-down"></i>';
        moreBtn.classList.add('more');

        elem.parentNode.insertBefore(moreBtn, elem.nextSibling);

        return moreBtn;
    }

    more() {
        this.moreBtn.addEventListener('click', e => {
            if (e.target.previousSibling.style.maxHeight) {
                e.target.previousSibling.setAttribute('style', 'margin-bottom: 2rem !important');
                e.target.previousSibling.style.maxHeight = null;
                e.target.innerHTML = '<i class="fa-solid fa-angles-up"></i>';
                this.isExpandedValue = true;
            } else {
                e.target.previousSibling.style.maxHeight = '25rem';
                e.target.previousSibling.style.marginBottom = null;
                e.target.innerHTML = '<i class="fa-solid fa-angles-down"></i>';
                e.target.previousSibling.scrollIntoView();
                this.isExpandedValue = false;
            }
        })
    }

    expand() {
        if (!this.isExpandedValue) {
            this.moreBtn.click();
        }
    }

    handleAdultThumbs() {
        // @todo temporary fix
        const adultBadge = this.element.querySelector('.danger');
        if (adultBadge && adultBadge.textContent === '+18') {
            const image = this.element.querySelector('img');
            image.style.filter = 'blur(8px)';
            image.addEventListener('mouseenter', () => {
                image.style.filter = 'none';
            });
            image.addEventListener('mouseleave', () => {
                image.style.filter = 'blur(8px)';
            });
        }
    }
}
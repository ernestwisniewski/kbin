import {Controller} from '@hotwired/stimulus';
import Cookies from 'js-cookie';

export default class extends Controller {
    static targets = ['auto_refresh', 'auto_embed', 'notifications', 'federation'];

    connect() {
        if (Cookies.get('user_option_auto_embed') === 'true') {
            document.addEventListener('turbo:load', () => {
                this.togglePreview()
            });
        }
    }

    toggle(e) {
        Cookies.set('user_option_' + e.target.dataset.userDataValue, e.target.checked);

        if (e.target.dataset.userDataValue === 'auto_embed') {
            location.reload();
            return;
            this.togglePreview();
        }
    }

    async togglePreview() {
        const sleep = ms => new Promise(r => setTimeout(r, Math.random() * (500 - 2000) + 500));

        let observer = new IntersectionObserver(function (entries, observer) {
            if (entries[0].isIntersecting === true) {
                entries[0].target.click();
                observer.unobserve(entries[0].target);
            }
        }, { threshold: [0] });

        for (const el of document.querySelectorAll('.kbin-preview')) {
            observer.observe(el);
            await sleep();
        }
    }
}

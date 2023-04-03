import {ApplicationController, useDebounce} from 'stimulus-use'
import router from "../utils/routing";
import {fetch, ok} from "../utils/http";

/* stimulusFetch: 'lazy' */
export default class extends ApplicationController {
    static values = {
        loading: Boolean,
    }

    static debounces = ['mention']

    connect() {
        useDebounce(this, {wait: 800})
        this.handleAndroidDropdowns();
    }

    handleAndroidDropdowns() {
        const ua = navigator.userAgent.toLowerCase();
        const isAndroid = ua.indexOf("android") > -1;
        if (isAndroid) {
            this.element.querySelectorAll('.dropdown > a').forEach((dropdown) => {
                dropdown.addEventListener('click', (event) => {
                    event.preventDefault();
                });
            });
        }
    }

    async mention(event) {
        if (false === event.target.matches(':hover')) {
            return;
        }

        try {
            const username = event.target.title.includes('@') ? `@${event.target.title}` : event.target.title;
            const url = router().generate('ajax_fetch_user_popup', {username: username});

            this.loadingValue = true;

            let response = await fetch(url);

            response = await ok(response);
            response = await response.json();

            document.querySelector('.popover').innerHTML = response.html;

            popover.trigger = event.target;
            popover.selectedTrigger = event.target;
            popover.element.dispatchEvent(new Event('openPopover'));
        } catch (e) {
        } finally {
            this.loadingValue = false;
        }
    }
}
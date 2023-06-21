import {Controller} from '@hotwired/stimulus';
import {useDebounce} from 'stimulus-use';
import router from "../utils/routing";
import {fetch, ok} from "../utils/http";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static debounces = ['user_popup']

    connect() {
        useDebounce(this, {wait: 800});
    }

    async user_popup(event) {
        if (false === event.target.matches(':hover')) {
            return;
        }

        try {
            let param = event.params.username;

            if (param.charAt(0) === "@") {
                param = param.substring(1);
            }
            const username = param.includes('@') ? `@${param}` : param;
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

    async navigate_user(event) {
        event.preventDefault();

        window.location = '/u/' + event.params.username;
    }

    async navigate_magazine(event) {
        event.preventDefault();

        window.location = '/m/' + event.params.username;
    }
}
import { Controller } from '@hotwired/stimulus';
import router from "../utils/routing";
import { fetch, ok } from "../utils/http";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    //static debounces = ['user_popup']

    /**
     * Instance of setTimeout to be used for the display of the popup. This is cleared if the user 
     * exits the target before the delay is reached
     */
    userPopupTimeout;

    /**
     * Delay to wait until the popup is displayed
     */
    userPopupTimeoutDelay = 1200;

    /**
     * Called on mouseover
     * @param {*} event 
     * @returns 
     */
    async user_popup(event) {

        if (false === event.target.matches(':hover')) {
            return;
        }

        //create a setTimeout callback to be executed when the user has hovered over the target for a set amount of time
        this.userPopupTimeout = setTimeout(this.trigger_user_popup, this.userPopupTimeoutDelay, event);
    }

    /**
     * Called on mouseout, cancel the UI popup as the user has moved off the element
     * @param {*} event 
     */
    async user_popup_out(event) {
        clearTimeout(this.userPopupTimeout);
    }

    /**
     * Called when the user popup should open
     */
    async trigger_user_popup(event) {

        try {
            let param = event.params.username;

            if (param.charAt(0) === "@") {
                param = param.substring(1);
            }
            const username = param.includes('@') ? `@${param}` : param;
            const url = router().generate('ajax_fetch_user_popup', { username: username });

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
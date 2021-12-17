import {Controller} from '@hotwired/stimulus';
import {fetch, ok} from "../utils/http";

export default class extends Controller {
    static targets = ['form'];
    static classes = ['active'];
    static values = {
        isBlocked: Boolean,
        loading: Boolean,
        addUrl: String,
        removeUrl: String,
    };

    async blockOrUnblock(event) {
        event.preventDefault();

        this.loadingValue = true;

        if (!window.KBIN_LOGGED_IN) {
            document.querySelector(".kbn-login-btn a").click()
            return;
        }

        try {
            let response = await fetch(this.isBlockedValue ? this.removeUrlValue : this.addUrlValue, {
                method: 'POST',
                body: new FormData(event.target)
            });

            response = await ok(response);
            response = await response.json();

            this.isBlockedValue = response.isBlocked;
        } catch (e) {
            alert('Oops, something went wrong.');
            throw e;
        } finally {
            this.loadingValue = false;
        }
    }

    isBlockedValueChanged(isBlocked) {
        if (isBlocked) {
            this.formTarget.classList.add(this.activeClass);
            this.element.closest('.kbin-sub').getElementsByClassName('kbin-sub-form')[0].classList.remove('kbin-sub--active')
            this.element.closest('.kbin-sub').dataset.subscribeIsSubscribedValue = false;
        } else {
            this.formTarget.classList.remove(this.activeClass);
        }
    }
}

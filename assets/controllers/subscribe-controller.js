import {Controller} from '@hotwired/stimulus';
import {fetch, ok} from "../utils/http";

export default class extends Controller {
    static targets = ['form', 'subCount'];
    static classes = ['active'];
    static values = {
        isSubscribed: Boolean,
        loading: Boolean,
        subCount: Number,
        addUrl: String,
        removeUrl: String,
        follow: String,
        unfollow: String,
    };

    async subOrUnsub(event) {
        event.preventDefault();

        this.loadingValue = true;

        if (!window.KBIN_LOGGED_IN) {
            window.location = window.KBIN_LOGIN;
            return;
        }

        try {
            let response = await fetch(this.isSubscribedValue ? this.removeUrlValue : this.addUrlValue, {
                method: 'POST',
                body: new FormData(event.target)
            });

            response = await ok(response);
            response = await response.json();

            this.isSubscribedValue = response.isSubscribed;
            this.subCountValue = response.subCount;
        } catch (e) {
            alert('Oops, something went wrong.');
            throw e;
        } finally {
            this.loadingValue = false;
        }
    }

    isSubscribedValueChanged(isSub) {
        if (isSub) {
            this.formTarget.classList.add(this.activeClass);
            this.element.closest('.kbin-sub').getElementsByClassName('kbin-block-form')[0].classList.remove('kbin-block--active');
            this.element.closest('.kbin-sub').getElementsByClassName('kbin-block')[0].dataset.blockIsBlockedValue = false;
            this.formTarget.getElementsByTagName('button')[0].innerHTML = this.unfollowValue
        } else {
            this.formTarget.classList.remove(this.activeClass);
            this.formTarget.getElementsByTagName('button')[0].innerHTML = this.followValue
        }
    }

    subCountValueChanged(count) {
        this.subCountTarget.innerHTML = count;
    }
}

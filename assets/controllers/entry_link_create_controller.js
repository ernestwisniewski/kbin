import {ApplicationController,useThrottle} from 'stimulus-use'
import {fetch,ok} from "../utils/http";
import router from "../utils/routing";

/* stimulusFetch: 'lazy' */
export default class extends ApplicationController {
    static throttles = ['fetchLink']
    static targets = ['title', 'description', 'url', 'loader'];
    static values = {
        loading: Boolean
    };

    connect() {
        useThrottle(this, {
            wait: 1000
        })

        let params = new URLSearchParams(window.location.search);
        let url = params.get('url');
        if (url) {
            this.urlTarget.value = url;
            this.urlTarget.dispatchEvent(new Event('input'));
        }
    }

    async fetchLink(event) {

        if (!event.target.value) {
            return
        }

        try {
            this.loadingValue = true;

            await this.fetchTitleAndDescription(event);

            this.loadingValue = false;
        } catch (e) {
            this.loadingValue = false;
        } finally {}
    }

    loadingValueChanged(val) {
        this.titleTarget.disabled = val;
        this.descriptionTarget.disabled = val;

        if (val) {
            this.loaderTarget.classList.remove('hide');
        } else {
            this.loaderTarget.classList.add('hide');
        }
    }

    async fetchTitleAndDescription(event) {
        if (this.titleTarget.value && confirm('Are you sure you want to fetch the title and description? This will overwrite the current values.') === false) {
            return;
        }

        const url = router().generate('ajax_fetch_title');
        let response = await fetch(url, {
            method: 'POST',
            body: JSON.stringify({
                'url': event.target.value
            })
        });

        response = await ok(response);
        response = await response.json();

        this.titleTarget.value = response.title;
        this.descriptionTarget.value = response.description;

        // required for input length indicator
        this.titleTarget.dispatchEvent(new Event('input'));
        this.descriptionTarget.dispatchEvent(new Event('input'));
    }
}
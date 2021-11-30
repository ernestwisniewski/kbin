import {Controller} from 'stimulus';
import {fetch, ok} from "../utils/http";

export default class extends Controller {
    static targets = ['counter'];
    static classes = ['active'];
    static values = {
        magazineName: String,
        entryId: Number,
    };


    async connect() {
        console.log('ac');
        let url = `https://${window.location.host}/.well-known/mercure/subscriptions/` + 'count';

        if(this.hasEntryIdValue) {
            url = `https://${window.location.host}/.well-known/mercure/subscriptions/` + encodeURIComponent('/api/entries/' + this.entryIdValue);
        } else if(this.hasMagazineNameValue) {
            url = `https://${window.location.host}/.well-known/mercure/subscriptions/` + encodeURIComponent('/api/magazines/' + this.magazineNameValue);
        }

        try {
            let response = await fetch(url, {
                method: 'GET',
                withCredentials: true,
                credentials: 'include',
                headers: {
                    Authorization: 'Bearer ' + window.MERCURE_SUBSCRIPTIONS_TOKEN,
                }
            });

            response = await ok(response);
            response = await response.json();

            this.counterTarget.innerHTML = response.subscriptions.length + 1;
        } catch (e) {
        }

    }
}

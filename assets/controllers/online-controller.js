import {Controller} from '@hotwired/stimulus';
import {fetch, ok} from "../utils/http";
import router from "../utils/routing";

export default class extends Controller {
    static targets = ['counter'];
    static classes = ['active'];
    static values = {
        magazineName: String,
        entryId: Number,
    };


    async connect() {
        let topic = 'count';

        if (this.hasEntryIdValue) {
            topic = encodeURIComponent('/api/entries/' + this.entryIdValue);
        } else if (this.hasMagazineNameValue) {
            topic = encodeURIComponent('/api/magazines/' + this.magazineNameValue);
        }

        try {
            const url = router().generate('ajax_fetch_online', { topic: topic });

            let response = await fetch(url);

            response = await ok(response);
            response = await response.json();

            this.counterTarget.innerHTML = response.online;
        } catch (e) {
            throw e;
        }

    }
}

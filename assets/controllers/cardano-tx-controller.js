import {Controller} from 'stimulus';
import {fetch, ok} from "../utils/http";

export default class extends Controller {
    static values = {
        url: String
    }
    static targets = ['counter'];

    async init(event) {
        event.preventDefault();

        try {
            let response = await fetch(this.urlValue, {method: 'POST'});

            response = await ok(response);
        } catch (e) {
            throw e;
        }
    }
}

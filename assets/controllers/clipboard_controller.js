import {Controller} from "@hotwired/stimulus";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    copy(event) {
        event.preventDefault();

        const url = event.target.href;
        navigator.clipboard.writeText(url);
    }
}
import {Controller} from '@hotwired/stimulus';
import Popover from "bootstrap/js/dist/popover";

export default class extends Controller {
    connect() {
        super.connect();

        const popoverTriggerList = [].slice.call(this.element.querySelectorAll('[data-bs-toggle="popover"]'))
        popoverTriggerList.map(function (popoverTriggerEl) {
            return new Popover(popoverTriggerEl)
        })
    }
}

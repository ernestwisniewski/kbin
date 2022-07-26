import {Controller} from '@hotwired/stimulus';
import Cookies from 'js-cookie';

export default class extends Controller {
    static targets = ['autorefresh', 'notifications', 'federation'];
    static values = {
        autorefresh: Boolean,
        notifications: Boolean,
        federation: Boolean
    }

    toggle(e) {
        Cookies.set(e.target.dataset.userDataValue, e.target.checked);
    }
}

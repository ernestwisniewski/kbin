import {Controller} from '@hotwired/stimulus';
import Cookies from 'js-cookie';

export default class extends Controller {
    connect() {
        if(!Cookies.get('cookie-info')) {
            this.element.classList.remove('d-none');
        }
    }

    close() {
        Cookies.set('cookie-info', true);
        this.element.classList.add('d-none');
    }
}

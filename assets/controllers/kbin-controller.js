import {Controller} from 'stimulus';
import {fetch, ok} from "./utils/http";
import Cookies from 'js-cookie';

export default class extends Controller {
    connect() {
        if (Cookies.get('dark')) {
            this.element.classList.add('kbin-dark');
        }
    }

    dark() {
        this.element.classList.toggle('kbin-dark');
        if (Cookies.get('dark')) {
            Cookies.remove('dark');
        } else {
            Cookies.set('dark', true);
        }
    }

}

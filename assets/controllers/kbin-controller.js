import {Controller} from 'stimulus';
import {fetch, ok} from "./utils/http";

export default class extends Controller {

    dark() {
        this.element.classList.toggle('kbin-dark')
    }

}

import {Controller} from '@hotwired/stimulus';
import Cookies from 'js-cookie';

export default class extends Controller {
    connect() {
        super.connect();

        let self = this;
        window.onscroll = function () {
            self.scroll();
        };
    }

    scroll(){
        if (
            document.body.scrollTop > 20 ||
            document.documentElement.scrollTop > 20
        ) {
            this.element.style.display = "block";
        } else {
            this.element.style.display = "none";
        }
    }

    up() {
        document.body.scrollTop = 0;
        document.documentElement.scrollTop = 0;
    }
}

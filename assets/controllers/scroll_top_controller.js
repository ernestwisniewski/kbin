import {Controller} from '@hotwired/stimulus';
import Cookies from 'js-cookie';
import { useDispatch } from 'stimulus-use'

export default class extends Controller {
    connect() {
        useDispatch(this)

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

        let notificationCounter = document.getElementById('kbin-activity-counter');
        if(parseInt(notificationCounter.innerHTML) > 0) {
            this.dispatch('up');
        }
    }
}

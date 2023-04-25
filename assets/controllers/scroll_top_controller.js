import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        let self = this;
        window.onscroll = function () {
            self.scroll();
        };
    }

    scroll() {
        if (
            document.body.scrollTop > 20 ||
            document.documentElement.scrollTop > 20
        ) {
            this.element.style.display = "block";
        } else {
            this.element.style.display = "none";
        }
    }

    increaseCounter() {
        const counter = this.element.querySelector('small');
        counter.innerHTML = parseInt(counter.innerHTML) + 1;
        counter.classList.remove('hidden');
    }

    scrollTop() {
        document.body.scrollTop = 0;
        document.documentElement.scrollTop = 0;
    }
}

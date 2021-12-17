import {Controller} from '@hotwired/stimulus';
import {fetch, ok} from "../utils/http";

export default class extends Controller {
    static values = {
        url: String
    }
    static targets = ['counter'];

    async observe(event) {
        event.preventDefault();

        try {
            let response = await fetch(this.urlValue, {method: 'POST'});

            response = await ok(response);

            clearInterval(this.interval);

            this.startTimer(60 * 5);
        } catch (e) {
            throw e;
        }
    }

    startTimer(duration) {
        let timer = duration, minutes, seconds;

        let target = this.counterTarget;
        this.interval = setInterval(function () {
            minutes = parseInt(timer / 60, 10);
            seconds = parseInt(timer % 60, 10);

            minutes = minutes < 10 ? "0" + minutes : minutes;
            seconds = seconds < 10 ? "0" + seconds : seconds;

            target.innerHTML = minutes + ":" + seconds;

            if (--timer < 0) {
                timer = duration;
            }
        }, 1000);
    }
}

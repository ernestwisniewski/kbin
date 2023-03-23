import {Controller} from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    connect() {
        this.handleAndroidDropdowns();
    }

    handleAndroidDropdowns() {
        const ua = navigator.userAgent.toLowerCase();
        const isAndroid = ua.indexOf("android") > -1;
        if (isAndroid) {
            this.element.querySelectorAll('.dropdown > a').forEach((dropdown) => {
                dropdown.addEventListener('click', (event) => {
                    event.preventDefault();
                });
            });
        }
    }
}
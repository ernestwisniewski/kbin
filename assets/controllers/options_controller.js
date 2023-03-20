import {Controller} from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['federation', 'settings'];
    static values = {
        initial: Boolean
    }

    toggleFederation(event) {
        event.preventDefault();

        if (false === this.initialValue || this.federationTarget.style.display === 'none') {
            this.settingsTarget.style.display = 'none';
            this.federationTarget.style.display = 'block';
            this.initialValue = true;
        } else {
            this.federationTarget.style.display = 'none';
        }
    }

    toggleSettings(event) {
        event.preventDefault();

        if (false === this.initialValue || this.settingsTarget.style.display === 'none') {
            this.federationTarget.style.display = 'none';
            this.settingsTarget.style.display = 'block';
            this.initialValue = true;
        } else {
            this.settingsTarget.style.display = 'none';
        }
    }
}
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

            this.removeActiveClass();
            event.target.classList.add('active');

            this.initialValue = true;
        } else {
            event.target.classList.remove('active');
            this.federationTarget.style.display = 'none';
        }
    }

    toggleSettings(event) {
        event.preventDefault();

        if (false === this.initialValue || this.settingsTarget.style.display === 'none') {
            this.federationTarget.style.display = 'none';
            this.settingsTarget.style.display = 'block';

            this.removeActiveClass();
            event.target.classList.add('active');

            this.initialValue = true;
        } else {
            event.target.classList.remove('active');
            this.settingsTarget.style.display = 'none';
        }
    }

    removeActiveClass() {
        this.element.querySelector('.options')
            .querySelectorAll('.active').forEach(element => {
            element.classList.remove('active');
        });
    }
}
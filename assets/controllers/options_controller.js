import {Controller} from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['federation', 'settings', 'actions'];
    static values = {
        initial: Boolean
    }

    /**
     * Handling toggling the federation section
     * @param {*} event 
     */
    toggleFederation(event) {
        event.preventDefault();

        if (false === this.initialValue || this.federationTarget.style.display === 'none') {
            this.settingsTarget.style.display = 'none';
            this.federationTarget.style.display = 'block';

            this.removeActiveClass();
            event.currentTarget.classList.add('active');

            this.initialValue = true;
        } else {
            event.currentTarget.classList.remove('active');
            this.federationTarget.style.display = 'none';
        }
    }

    /**
     * Handles toggling the settings section 
     * @param {*} event 
     */
    toggleSettings(event) {
        event.preventDefault();

        if (false === this.initialValue || this.settingsTarget.style.display === 'none') {
            this.federationTarget.style.display = 'none';
            this.settingsTarget.style.display = 'block';

            this.removeActiveClass();
            event.currentTarget.classList.add('active');

            this.initialValue = true;
        } else {
            event.currentTarget.classList.remove('active');
            this.settingsTarget.style.display = 'none';
        }
    }

    /**
     * Removes all active classes, ensuring only the current active item is highlighted 
     */
    removeActiveClass() {
        this.actionsTarget.querySelectorAll('.active').forEach(element => {
            element.classList.remove('active');
        });    
    }

    /**
     * Handles toggling the navigation closed
     * @param {*} e 
     */
    closeNav(e) {
        e.preventDefault();
        document.getElementById('sidebar').classList.remove('open');
    }

    /**
     * Handles the home button press
     * @param {*} e 
     * @returns 
     */
    home(e){
        e.preventDefault();
        window.location = e.target.closest('a').href;
        return;
    }
}
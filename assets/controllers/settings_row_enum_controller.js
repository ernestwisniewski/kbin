import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    /**
     * Calls the action at the given path when the value changes
     * @param actionPath {string} - The path to the action to be called
     * @param reloadRequired {boolean} - Whether the page needs to be reloaded after the action is called
     */
    change({params: {actionPath, reloadRequired}}) {
        return fetch(actionPath).then(() => {
            if (reloadRequired) {
                document.querySelector('.settings-list').classList.add('reload-required');
            }
        });
    }
}
import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    /**
     * Calls the action at the given path when the value changes
     * @param actionPath {string} - The path to the action to be called
     */
    change({params: {actionPath}}) {
        return fetch(actionPath);
    }
}
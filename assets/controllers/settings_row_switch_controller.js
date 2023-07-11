import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    /**
     * Calls the action at the given path when the toggle is checked or unchecked
     * @param target {HTMLInputElement} - The checkbox element of the toggle that was clicked
     * @param truePath {string} - The path to the action to be called when the toggle is checked
     * @param falsePath {string} - The path to the action to be called when the toggle is unchecked
     */
    toggle({target, params: {truePath, falsePath}}) {
        const path = target.checked ? truePath : falsePath;
        return fetch(path);
    }
}
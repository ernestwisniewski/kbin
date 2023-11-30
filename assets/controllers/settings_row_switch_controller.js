// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    /**
     * Calls the action at the given path when the toggle is checked or unchecked
     * @param target {HTMLInputElement} - The checkbox element of the toggle that was clicked
     * @param truePath {string} - The path to the action to be called when the toggle is checked
     * @param falsePath {string} - The path to the action to be called when the toggle is unchecked
     * @param reloadRequired {boolean} - Whether the page needs to be reloaded after the action is called
     */
    toggle({target, params: {truePath, falsePath, reloadRequired}}) {
        const path = target.checked ? truePath : falsePath;
        return fetch(path).then(() => {
            if (reloadRequired) {
                document.querySelector('.settings-list').classList.add('reload-required');
            }
        });
    }
}
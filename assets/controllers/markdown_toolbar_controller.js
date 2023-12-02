// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

import {Controller} from "@hotwired/stimulus";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    addSpoiler(event) {
        event.preventDefault();

        const input = document.getElementById(this.element.getAttribute('for'));
        let spoilerBody = '_____';
        let contentAfterCursor;

        const start = input.selectionStart;
        const end = input.selectionEnd;

        const contentBeforeCursor = input.value.substring(0, start);
        if (start === end) {
            contentAfterCursor = input.value.substring(start);
        } else {
            contentAfterCursor = input.value.substring(end);
            spoilerBody = input.value.substring(start, end);
        }

        const spoiler = `
::: spoiler spoiler
${spoilerBody}
:::`;

        input.value = contentBeforeCursor + spoiler + contentAfterCursor;
        input.dispatchEvent(new Event('input'));

        const spoilerTitlePosition = contentBeforeCursor.length + "::: spoiler ".length + 1;
        input.setSelectionRange(spoilerTitlePosition, spoilerTitlePosition);
        input.focus();
    }
}
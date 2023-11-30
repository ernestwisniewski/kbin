// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

import {Controller} from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    connect() {
        const container = this.element;
        const input = container.querySelector('.image-input');
        const preview = container.querySelector('.image-preview');
        const clearButton = container.querySelector('.image-preview-clear');
        
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                clearButton.setAttribute('style', 'display: inline-block !important');
            };
            
            reader.readAsDataURL(file);
        });
    }

    clearPreview() {
        const container = this.element;
        const input = container.querySelector('.image-input');
        const preview = container.querySelector('.image-preview');
        const clearButton = container.querySelector('.image-preview-clear');

        input.value = '';
        preview.src = '#';
        preview.style.display = 'none';
        clearButton.style.display = 'none';
    }
}
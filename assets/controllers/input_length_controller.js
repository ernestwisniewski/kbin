import {Controller} from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {
        max: Number
    }

    /** DOM element that will hold the current/max text */
    lengthIndicator

    connect(){
        if(!this.hasMaxValue){
            return;
        }

        //create a html element to display the current/max text
        let indicator = document.createElement('div');

        indicator.classList.add('length-indicator');

        this.element.insertAdjacentElement('afterend', indicator);

        this.lengthIndicator = indicator;

        this.updateDisplay();
    }

    updateDisplay(){
        if (!this.lengthIndicator) {
            return;
        }

        //trim to max length if needed
        if(this.element.value.length >= this.maxValue){
            this.element.value = this.element.value.substring(0, this.maxValue);
        }

        //display to user
        this.lengthIndicator.innerHTML = `${this.element.value.length}/${this.maxValue}`;
    }
}
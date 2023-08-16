import {Controller} from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
   
    previewButton;

    previewIcon;

    input;

    connect(){
        this.input = this.element.querySelector('[type="password"]');
        //create the preview button
        this.setupPasswordPreviewButton();
    }

    /**
     * Create the preview button and bind its event listener
     */
    setupPasswordPreviewButton(){

        let previewButton = document.createElement("div");
        previewButton.classList.add('password-preview-button','btn','btn__secondary')
        this.previewButton = previewButton;

        let previewIcon = document.createElement("span");
        previewIcon.classList.add('fas', 'fa-eye-slash');
        this.previewIcon = previewIcon;

        previewButton.append(previewIcon);
        this.element.append(previewButton);

        //setup event listener
        previewButton.addEventListener('click', () => {
            this.onPreviewButtonClick();
        });
    }

    /**
     * On press, switch out the input 'type' to show or hide the password
     */
    onPreviewButtonClick(){
        
        let inputType = this.input.getAttribute('type');
        if(inputType === 'password'){
            this.input.setAttribute('type', 'text');
            this.previewIcon.classList.remove('fa-eye-slash');
            this.previewIcon.classList.add('fa-eye');

        }else{
            this.input.setAttribute('type', 'password');
            this.previewIcon.classList.remove('fa-eye');
            this.previewIcon.classList.add('fa-eye-slash');
        }
    }
   
}
import {Datepicker} from 'vanillajs-datepicker';

export default class KChoices {
    constructor() {
        document.querySelectorAll('.kbin-login-alert').forEach(el => {
            el.addEventListener('click', (event) => {
                event.preventDefault();
                alert('Musisz być zalogowany.')
            })
        });
    }
}

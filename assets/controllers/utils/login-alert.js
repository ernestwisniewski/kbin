import {Datepicker} from 'vanillajs-datepicker';

export default class LoginAlert {
    constructor() {
        document.querySelectorAll('.kbin-login-alert').forEach(el => {
            el.addEventListener('click', (event) => {
                event.stopImmediatePropagation();
                alert('Musisz być zalogowany.')
            })
        });
    }
}

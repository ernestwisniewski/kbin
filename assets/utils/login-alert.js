export default class LoginAlert {
    constructor() {
        document.querySelectorAll('.kbin-login-alert').forEach(el => {
            el.addEventListener('click', (event) => {
                event.preventDefault();
                alert('Musisz być zalogowany.')
            })
        });

        document.querySelectorAll('.kbin-link-block').forEach(el => {
            el.addEventListener('click', (event) => {
                event.preventDefault();
            })
        });
    }
}

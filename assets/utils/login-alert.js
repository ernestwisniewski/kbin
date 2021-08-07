export default class LoginAlert {
    constructor() {
        document.querySelectorAll('.kbin-login-alert').forEach(el => {
            el.addEventListener('click', (event) => {
                this.loginAlert(event)
            })
        });

        document.addEventListener('turbo:load', (event) => {
            event.target.querySelectorAll('.kbin-login-alert').forEach(el => {
                el.addEventListener('click', (event) => {
                    this.loginAlert(event)
                })
            });
        });

        document.querySelectorAll('.kbin-link-block').forEach(el => {
            el.addEventListener('click', (event) => {
                event.preventDefault();
            })
        });

        document.addEventListener('turbo:load', (event) => {
            event.target.querySelectorAll('.kbin-login-alert').forEach(el => {
                el.addEventListener('click', (event) => {
                    event.preventDefault();
                })
            });
        });
    }

    loginAlert(event){
        event.preventDefault();
        alert('Musisz być zalogowany.')
    }
}

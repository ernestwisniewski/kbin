import {Controller} from '@hotwired/stimulus';
import {fetch, ok} from "../utils/http";
import Cookies from 'js-cookie';

export default class extends Controller {
    static targets = ['topBar', 'icon'];

    connect() {
        if (window.KBIN_LOGGED_IN) {
            return true;
        }

        // const prefersDarkScheme = window.matchMedia("(prefers-color-scheme: dark)");

        if (!Cookies.get('theme')) {
            Cookies.set('theme', 'kbin-dark');
            document.body.classList.toggle('kbin-dark');
        }
    }

    toggleTopBar(e) {
        e.preventDefault();

        this.topBarTarget.classList.toggle('visually-hidden');

        document.querySelector('.navbar-collapse').classList.remove('show')

        let icons = document.querySelectorAll(`[data-kbin-target='icon']`);

        icons.forEach(function (elem) {
            if(elem.classList.contains('fa-caret-down')) {
                elem.classList.remove('fa-caret-down');
                elem.classList.add('fa-caret-up');
            } else {
                elem.classList.remove('fa-caret-up');
                elem.classList.add('fa-caret-down');
            }
        })
    }

    async toggleTheme(e) {
        e.preventDefault();

        if (window.KBIN_LOGGED_IN) {
            try {
                let response = await fetch(e.target.href, {method: 'POST'});

                response = await ok(response);
                await response.json();

                document.body.classList.toggle('kbin-dark');
            } catch (e) {
                document.body.classList.toggle('kbin-dark');
            } finally {
            }
        } else {
            if (!Cookies.get('theme')) {
                document.body.classList.add('kbin-dark');
                Cookies.set('theme', 'kbin-dark');
                return true;
            }

            Cookies.set('theme', document.body.classList.contains('kbin-dark') ? 'kbin-light' : 'kbin-dark');
            document.body.classList.toggle('kbin-dark');
        }
    }
}

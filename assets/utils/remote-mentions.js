import {trim} from "core-js/internals/string-trim";

export default class RemoteMentions {
    constructor() {
        document.querySelectorAll('.mention').forEach(el => {
            this.build(el);
        });

        document.addEventListener('turbo:load', (event) => {
            event.target.querySelectorAll('.mention').forEach(el => {
                this.build(el);
            });
        });
    }

    build(el) {
        el.addEventListener('click', (event) => {
            event.preventDefault();

            window.location = window.location.origin + '/u/' + trim(event.target.innerHTML);
        })
    }
}

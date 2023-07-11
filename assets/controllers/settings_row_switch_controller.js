import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    toggle({target, params: {truePath, falsePath, key}}) {
        const path = target.checked ? truePath : falsePath;
        fetch(path);
    }
}
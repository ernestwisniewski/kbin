import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    change({params}) {
        const {actionPath} = params;
        console.log("change", params);
        fetch(actionPath);
    }
}
import {Controller} from 'stimulus';
import router from "./utils/routing";

export default class extends Controller {
    static targets = ['upVote', 'downVote']
    static classes = ['vote-dv', 'vote-dv']
    static values = {
        loading: Boolean,
    };

    upVote(event) {
        // event.preventDefault()
        // let url = router().generate('ajax_fetch_title');
        // console.log(this)
    }

    downVote(event) {
        // event.preventDefault()
    }
}

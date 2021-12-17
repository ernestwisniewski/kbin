import {Controller} from '@hotwired/stimulus';
import {fetch, ok} from "../utils/http";

const VOTE_UP = 1;
const VOTE_DOWN = -1;

export default class extends Controller {
    static targets = ['upVote', 'upVoteCount', 'downVote', 'downVoteCount'];
    static classes = ['uv', 'dv'];
    static values = {
        alreadyVoted: Boolean,
        choice: Number,
        upVoteCount: Number,
        downVoteCount: Number,
        loading: Boolean,
        uvUrl: String,
        dvUrl: String,
    };

    async up(event) {
        event.preventDefault();
        (async () => await this.vote(event, VOTE_UP))();
    }

    async down(event) {
        event.preventDefault();
        (async () => await this.vote(event, VOTE_DOWN))();
    }

    async vote(event, val) {
        this.loadingValue = true;
        this.alreadyVotedValue = true;

        if (!window.KBIN_LOGGED_IN) {
            document.querySelector(".kbn-login-btn a").click()
            return;
        }

        try {
            let voteUrl = this.uvUrlValue;
            if (val === VOTE_DOWN) {
                voteUrl = this.dvUrlValue;
            }

            let response = await fetch(voteUrl, {
                method: 'POST',
                body: new FormData(event.target)
            });

            response = await ok(response);
            response = await response.json();

            this.choiceValue = response.choice;
            this.upVoteCountValue = response.upVotes;

            if (this.hasDownVoteTarget) {
                this.downVoteCountValue = response.downVotes;
            }
        } catch (e) {
            alert('Oops, something went wrong');
            throw e;
        } finally {
            this.loadingValue = false;
        }
    }

    loadingValueChanged(loading) {
        if (loading) {
        } else {
        }
    }

    choiceValueChanged(event) {
        if (!this.alreadyVotedValue) {
            return;
        }

        this.upVoteTarget.classList.remove(this.uvClass);
        if (this.hasDownVoteTarget) {
            this.downVoteTarget.classList.remove(this.dvClass);
        }

        if (event === VOTE_UP) {
            this.upVoteTarget.classList.add(this.uvClass);
        } else if (event === VOTE_DOWN) {
            this.downVoteTarget.classList.add(this.dvClass);
        }

        this.upVoteCountTarget.innerHTML = this.upVoteCountValue
        if (this.hasDownVoteTarget) {
            this.downVoteCountTarget.innerHTML = this.downVoteCountValue
        }
    }
}

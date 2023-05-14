import {Application} from '@hotwired/stimulus'
import TextareaAutogrow from 'stimulus-textarea-autogrow'
import './styles/app.scss';
import './utils/popover';
// start the Stimulus application
import './bootstrap';
import '@github/markdown-toolbar-element'

const application = Application.start()
application.register('textarea-autogrow', TextareaAutogrow)
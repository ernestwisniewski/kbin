const routes = require('../../../public/js/fos_js_routes.json');
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';

export default function router() {
    Routing.setRoutingData(routes);

    return Routing;
}

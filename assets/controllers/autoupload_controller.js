import { Controller } from '@hotwired/stimulus';

/*
 * This is an example Stimulus controller!
 *
 * Any element with a data-controller="autoupload" attribute will cause
 * this controller to be executed. The name "autoupload" comes from the filename:
 * autoupload_controller.js -> "autoupload"
 *
 * Delete this file or adapt it for your use!
 */

export default class extends Controller {
    connect() {
        document.addEventListener('DOMContentLoaded', function () {
            const forms = document.querySelectorAll('.autoupload__form');
            
            if (forms.length > 0) {
                forms.forEach(form => {
                    form.addEventListener('change', function (event) {
                        if (event.target.type === 'file') {
                            form.submit();
                        }
                    });
                });
            }
        });
    }
}
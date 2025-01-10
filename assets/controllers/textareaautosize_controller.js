import { Controller } from '@hotwired/stimulus';

/*
 * This is an example Stimulus controller!
 *
 * Any element with a data-controller="textareaautosize" attribute will cause
 * this controller to be executed. The name "textareaautosize" comes from the filename:
 * textareaautosize_controller.js -> "textareaautosize"
 *
 * Delete this file or adapt it for your use!
 */

export default class extends Controller {
    connect() {
        this.body = document.querySelector('body');
        
        this.txtareas = document.querySelectorAll('.txtarea__autoh');
        if(!this.txtareas) return;

        this.txtareas.forEach((txtarea) => {
            txtarea.style.height = txtarea.scrollHeight + "px";
            txtarea.style.overflowY = "hidden";

            txtarea.addEventListener("input", function() {
                this.style.height = "auto";
                this.style.height = this.scrollHeight + "px";
            });
        });
    }
}
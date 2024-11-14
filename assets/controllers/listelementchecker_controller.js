import { Controller } from '@hotwired/stimulus';

/*
 * This is an example Stimulus controller!
 *
 * Any element with a data-controller="listelementchecker" attribute will cause
 * this controller to be executed. The name "listelementchecker" comes from the filename:
 * listelementchecker_controller.js -> "listelementchecker"
 *
 * Delete this file or adapt it for your use!
 */

export default class extends Controller {
    connect() {
        this.items = document.querySelectorAll('.section__element__item');
        if(!this.items) return;

        this.items.forEach((checkbox) => {
            checkbox.addEventListener('click', (event) => this.toggleElement(event));
        });
    }

    toggleElement(event) {
        const checkbox = event.target;
        const elementId = checkbox.dataset.id;

        fetch('/admin/ajax/element-toggle/', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ elementId: elementId })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Element toggled:', data);
        })
        .catch(error => console.error('Error:', error));
    }
}
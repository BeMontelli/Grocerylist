import { Controller } from '@hotwired/stimulus';

/*
 * This is an example Stimulus controller!
 *
 * Any element with a data-controller="modalsearch" attribute will cause
 * this controller to be executed. The name "modalsearch" comes from the filename:
 * modalsearch_controller.js -> "modalsearch"
 *
 * Delete this file or adapt it for your use!
 */

export default class extends Controller {
    connect() {

        this.modalSearch = document.querySelector('#modalsearch');

        this.searchBtn = document.querySelector('#btnsearch');

        if(!this.modalSearch || !this.searchBtn) return;

        console.log(this.modalSearch);
        console.log(this.searchBtn);
        console.log("test");

        this.modalSearch.addEventListener('click', (event) => this.closeModal(event));

        this.searchBtn.addEventListener('click', (event) => this.showModal());
    }

    showModal() {
        this.modalSearch.classList.add('show');
    }

    closeModal(event) {
        const closeBtn = this.modalSearch.querySelector('.modal__cancel');
        if(event.target === this.modalSearch || event.target === closeBtn) {
            this.modalSearch.classList.remove('show');
        }
    }
}
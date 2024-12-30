import { Controller } from '@hotwired/stimulus';

/*
 * This is an example Stimulus controller!
 *
 * Any element with a data-controller="modalscreate" attribute will cause
 * this controller to be executed. The name "modalscreate" comes from the filename:
 * modalscreate_controller.js -> "modalscreate"
 *
 * Delete this file or adapt it for your use!
 */

export default class extends Controller {
    connect() {
        this.modals = document.querySelectorAll('.modal_entity');
        this.btns = document.querySelectorAll('.btn_modal_entity');

        if(!this.modals || !this.btns) return;

        const urlParams = new URLSearchParams(window.location.search);
        const createParam = urlParams.get('create');

        this.modals.forEach((modal) => {
            modal.addEventListener('click', (event) => this.closeModal(modal, event));
            const form = modal.querySelector('form');
            const confirmArea = modal.querySelector('button.modal__confirm');
            if(form && confirmArea) confirmArea.addEventListener('click', (event) => this.submitForm(form));
        
            if (createParam && modal.getAttribute('data-entity') === createParam) {
                this.showModal(modal);
            }
        });

        this.btns.forEach((btn) => {
            btn.addEventListener('click', (event) => this.clickBtn(btn));
        });
    }

    clickBtn(btn) {
        const entity = btn.getAttribute('data-entity');
        if(!entity) return;

        this.modals.forEach((modal) => {
            if(modal.getAttribute('data-entity') === entity) this.showModal(modal);
        });
    }

    showModal(modal) {
        modal.classList.add('show');
    }

    closeModal(modal, event) {
        const closeBtn = modal.querySelector('.modal__cancel');
        if(event.target === modal || event.target === closeBtn) {
            modal.classList.remove('show');
        }
    }

    submitForm(form) {
        form.submit();
    }
}
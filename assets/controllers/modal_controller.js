import { Controller } from '@hotwired/stimulus';

/*
 * This is an example Stimulus controller!
 *
 * Any element with a data-controller="modal" attribute will cause
 * this controller to be executed. The name "modal" comes from the filename:
 * modal_controller.js -> "modal"
 *
 * Delete this file or adapt it for your use!
 */

export default class extends Controller {
    connect() {

        this.modal = document.querySelector('.modal');

        this.forms = document.querySelectorAll('.delete__form');

        if(!this.modal || !this.forms) return;

        this.modal.addEventListener('click', (event) => this.closeModal(event));

        this.forms.forEach((form) => {
            const button = form.querySelector('button.btn__confirm');
            button.addEventListener('click', (event) => this.clickBtn(event,form));
        });
    }

    clickBtn(event,form) {

        const title = form.getAttribute('data-title');
        const txts = form.getAttribute('data-txts');
        const confirmTxt = form.getAttribute('data-confirm');
        const cancelTxt = form.getAttribute('data-cancel');

        if(title && txts && confirmTxt && cancelTxt) {
            event.preventDefault();
            this.showModal(form,title,txts,confirmTxt,cancelTxt);
        }

        return;
    }

    showModal(form,title,txts,confirmTxt,cancelTxt) {

        const titleArea = this.modal.querySelector('.modal__title');
        const txtsArea = this.modal.querySelector('.modal__txts');
        const cancelArea = this.modal.querySelector('.modal__cancel');
        const confirmArea = this.modal.querySelector('.modal__confirm');

        if(!titleArea || !txtsArea || !confirmArea || !cancelArea) return;

        this.modal.classList.add('show');
        titleArea.innerHTML = title;
        txtsArea.innerHTML = txts;
        confirmArea.innerHTML = confirmTxt;
        cancelArea.innerHTML = cancelTxt;

        confirmArea.addEventListener('click', (event) => this.submitForm(form));
    }

    closeModal(event) {
        const closeBtn = this.modal.querySelector('.modal__cancel');
        if(event.target === this.modal || event.target === closeBtn) {
            this.modal.classList.remove('show');
        }
    }

    submitForm(form) {
        form.submit();
    }
}
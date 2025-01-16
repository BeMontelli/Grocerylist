import { Controller } from '@hotwired/stimulus';

/*
 * This is an example Stimulus controller!
 *
 * Any element with a data-controller="modallistcomment" attribute will cause
 * this controller to be executed. The name "modallistcomment" comes from the filename:
 * modallistcomment_controller.js -> "modallistcomment"
 *
 * Delete this file or adapt it for your use!
 */

export default class extends Controller {
    connect() {
        this.body = document.querySelector('body');
        this.callSide = (this.body.classList.contains('admin')) ? '/admin' : '/front';

        this.modal = document.querySelector('.modal.modal_comments');
        this.btns = document.querySelectorAll('.section__element__content .comment__item');

        if(!this.modal || !this.btns) return;

        this.modal.addEventListener('click', (event) => this.closeModal(event));
        const form = this.modal.querySelector('form');
        const confirmArea = this.modal.querySelector('button.modal__confirm');
        if(form && confirmArea) confirmArea.addEventListener('click', (event) => this.submitForm(form));
        
        this.btns.forEach((btn) => {
            btn.addEventListener('click', (event) => this.actionBtn(btn));
        });
    }
    
    actionBtn(btn) {
        const ingredientId = encodeURIComponent(btn.getAttribute('data-ing-id'));
        const listId = encodeURIComponent(btn.getAttribute('data-list-id'));
        if(!ingredientId || !listId) return;

        fetch(this.callSide+'/ajax/ingredient-comments/'+ingredientId+'/'+listId, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log(data);
            //window.location.reload();
            if (data.success) {
                this.showModal(btn,data);
            }
            else this.modal.classList.remove('show');
        })
        .catch(error => console.error('Error:', error));
    }

    showModal(btn,data) {
        const title = this.modal.querySelector('.modal__title .ingredient');

        if(title) title.innerHTML = data.ingredientTitle;
        this.modal.classList.add('show');
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
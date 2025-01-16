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

        const confirmArea = this.modal.querySelector('button.modal__confirm');
        if(confirmArea) confirmArea.addEventListener('click', (event) => this.submitForm());
        
        this.btns.forEach((btn) => {
            btn.addEventListener('click', (event) => this.actionBtn(btn));
        });
    }
    
    actionBtn(btn) {
        this.ingredientId = encodeURIComponent(btn.getAttribute('data-ing-id'));
        this.listId = encodeURIComponent(btn.getAttribute('data-list-id'));
        if(!this.ingredientId || !this.listId) return;

        fetch(this.callSide+'/ajax/ingredient-comments/'+this.ingredientId+'/'+this.listId, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showModal(data);
            }
            else this.modal.classList.remove('show');
        })
        .catch(error => console.error('Error:', error));
    }

    showModal(data) {
        const title = this.modal.querySelector('.modal__title .ingredient');
        const inputsRange = this.modal.querySelector('.modal__txts .inputs');

        if(inputsRange) {
            inputsRange.innerHTML = '';
            if(data.comments.length > 0) {
                for(let i = 0; i < data.comments.length; i++) {
                    const input = document.createElement('input');
                    input.type = 'text';
                    input.className = '';

                    if(i+1 === data.comments.length) input.className = 'main__input mb-0';
                    else input.className = 'lost__input mt-0 mb-0';

                    input.value = data.comments[i];
                    inputsRange.appendChild(input);
                }
            } else {
                const input = document.createElement('input');
                input.type = 'text';
                input.className = 'main__input mb-0';
                inputsRange.appendChild(input);
            }
        }

        if(title) title.innerHTML = data.ingredientTitle;
        this.modal.classList.add('show');
    }

    closeModal(event) {
        const closeBtn = this.modal.querySelector('.modal__cancel');
        if(event.target === this.modal || event.target === closeBtn) {
            this.modal.classList.remove('show');
        }
    }

    submitForm() {
        const inputKept = this.modal.querySelector('.modal__txts .main__input');
        if(!inputKept) return;

        fetch(this.callSide+'/ajax/ingredient-comments/', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ ingredientId: this.ingredientId, listId: this.listId, comment: inputKept.value })
        })
        .then(response => response.json())
        .then(data => {
            console.log(data);
            if (data.success) {
                window.location.reload();
            }
        })
        .catch(error => console.error('Error:', error));
    }
}
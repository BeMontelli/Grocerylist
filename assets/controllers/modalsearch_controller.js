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

        this.modalSearch.addEventListener('click', (event) => this.closeModal(event));

        this.searchBtn.addEventListener('click', (event) => this.showModal());

        this.searchInput = this.modalSearch.querySelector('#search');
        this.searchResults = this.modalSearch.querySelector('#searchresults');
        this.debounceTimer = null;
        if(!this.searchInput || !this.searchResults) return;

        this.searchInput.addEventListener('input', (event) => this.inputTyping());
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

    inputTyping(event) {
        console.log('Current value:', this.searchInput.value);

        // Debounce pour exécuter après 1.5 secondes
        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(() => {
            if(this.searchInput.value) this.execSearch(this.searchInput.value);
        }, 1500);
    }

    execSearch(title) {
        fetch('/admin/ajax/search-by-title/', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ title: title })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Search result:', data);
        })
        .catch(error => console.error('Error:', error));
    }

    buildList() {
        this.searchResults.classList.add('show');
    }

    clearList() {
        this.searchResults.classList.remove('show');
        this.searchResults.innerHTML = "...";
    }
}
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
            this.clearList();
        }
    }

    inputTyping(event) {
        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(() => {
            this.searchInput.blur();
            const parent = this.searchInput.parentNode;
            parent.classList.add('loading');
            console.log(parent);
            if(this.searchInput.value) this.execSearch(this.searchInput.value);
            else this.clearList();
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
        .then(response => {
            const parent = this.searchInput.parentNode;
            parent.classList.remove('loading');
            if (!response.status === 200) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.results && data.results.length > 0) this.buildList(data.results);
            else this.clearList();
        })
        .catch(error => console.error('Error:', error));
    }

    buildList(results) {
        this.searchResults.innerHTML = '';

        const ul = document.createElement('ul');

        results.forEach(result => {
            const li = document.createElement('li');
            li.classList = "result__item";

            const a = document.createElement('a');
            a.href = result.path;
            a.textContent = result.title;

            const i = document.createElement('i');
            if(result.type === "ingredient") {
                i.classList = "ingredient__item bx bxs-bowl-rice";
            } else if(result.type === "recipe") {
                i.classList = "recipe__item bx bxs-bowl-hot";
            }

            li.appendChild(a);
            a.appendChild(i);
            ul.appendChild(li);
        });

        this.searchResults.appendChild(ul);
        this.searchResults.classList.add('show');
    }

    clearList() {
        this.searchResults.classList.remove('show');
        this.searchResults.innerHTML = "...";
    }
}
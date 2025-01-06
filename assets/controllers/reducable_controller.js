import { Controller } from '@hotwired/stimulus';

/*
 * This is an example Stimulus controller!
 *
 * Any element with a data-controller="reducable" attribute will cause
 * this controller to be executed. The name "reducable" comes from the filename:
 * reducable_controller.js -> "reducable"
 *
 * Delete this file or adapt it for your use!
 */

export default class extends Controller {
    connect() {
        this.items = document.querySelectorAll('.reducable_item');
        if(!this.items) return;

        this.items.forEach((item) => {
            this.prepare(item);
        });

    }

    prepare(item) {
        
        const inner = item.querySelector('.card');
        if(!inner) return;

        const h = inner.offsetHeight;
        if(h < 150) return;

        item.classList.add('reducable');
        inner.classList.add('reduced');
        const i = document.createElement('i');
        i.className = 'bx bx-chevron-down';

        const filter = document.createElement('div');
        filter.className = 'reducer__filter';
        inner.appendChild(filter);

        const btn = document.createElement('button');
        btn.className = 'btn btn-primary reducer';
        btn.appendChild(i);
        inner.appendChild(btn);

        btn.addEventListener('click', (event) => this.toggle(item,inner));
    }

    toggle(item,inner) {
        const distanceFromTop = item.getBoundingClientRect().top + window.scrollY;
        window.scroll({
            top: distanceFromTop,
            left: 0,
            behavior: "smooth",
          });
        inner.classList.toggle('reduced');
    }
}
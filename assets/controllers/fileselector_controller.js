import { Controller } from '@hotwired/stimulus';
import TomSelect from 'tom-select';

/*
 * This is an example Stimulus controller!
 *
 * Any element with a data-controller="fileselector" attribute will cause
 * this controller to be executed. The name "fileselector" comes from the filename:
 * fileselector_controller.js -> "fileselector"
 *
 * Delete this file or adapt it for your use!
 */

export default class extends Controller {
    connect() {
        
        document.addEventListener('DOMContentLoaded', () => {
            const selectElements = document.querySelectorAll('.ts-wrapper.fileselector');

            if(!selectElements || selectElements.length === 0 ) return;

            selectElements.forEach(selectElement => {
                if (selectElement) {
                    // selector area
                    let dropdownContent = selectElement.querySelector('.ts-dropdown-content');
                    if(dropdownContent) dropdownContent.classList.add('image__display');
                    
                    setInterval(function() {
                        // selection options
                        let options = selectElement.querySelectorAll('.option:not(.imagefiled)');
                        options.forEach(option => {
                            if (!option.classList.contains('imagefiled')) {
                                let imageUrl = option.textContent.trim();
                                option.innerHTML = `<div class="img__disp" style="background-image:url(${imageUrl})" alt="Thumbnail ${imageUrl}" aria-label="${imageUrl}">`;
                                option.classList.add('imagefiled');
                            }
                        });

                        // selected area
                        let ControlItem = selectElement.querySelector('.ts-control .item');
                        if(ControlItem) {
                            if (!ControlItem.classList.contains('imagefiled')) {
                                let imageUrl = ControlItem.textContent.trim();
                                ControlItem.innerHTML = `<div class="img__disp" style="background-image:url(${imageUrl})" alt="Thumbnail ${imageUrl}" aria-label="${imageUrl}">`;
                                ControlItem.classList.add('imagefiled');
                            }
                        }
                    }, 10);
                }
            });
        });
        
    }
}
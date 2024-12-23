import { Controller } from '@hotwired/stimulus';

/*
 * This is an example Stimulus controller!
 *
 * Any element with a data-controller="recipesform" attribute will cause
 * this controller to be executed. The name "recipesform" comes from the filename:
 * recipesform_controller.js -> "recipesform"
 *
 * Delete this file or adapt it for your use!
 */

export default class extends Controller {
    connect() {
        this.recipeChecker = document.querySelector('.recipes__check');
        this.recipeFields = document.querySelector('.recipe__fields');
        if(!this.recipeChecker || !this.recipeFields) return;

        this.recipeFields = this.recipeFields.parentElement.parentElement;
        if(!this.recipeFields) return;
        
        this.recipeChecker.addEventListener('change', this.toggleRecipesField.bind(this));
        this.toggleRecipesField();
    }
    
    toggleRecipesField() {
        if (this.recipeChecker.checked) this.recipeFields.style.display = 'flex';
        else this.recipeFields.style.display = 'none';   
    }
}
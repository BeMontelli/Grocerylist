import { Controller } from '@hotwired/stimulus';
import './../Sortable.min.js';

/*
 * This is an example Stimulus controller!
 *
 * Any element with a data-controller="sortablesections" attribute will cause
 * this controller to be executed. The name "sortablesections" comes from the filename:
 * sortablesections_controller.js -> "sortablesections"
 *
 * Delete this file or adapt it for your use!
 */

export default class extends Controller {
    connect() {

        const self = this;
        const listhandler = document.getElementById("listhandler");
        const trList = listhandler.getElementsByTagName("tr");

        if(listhandler && trList.length > 0) {
            let sortable = new Sortable(listhandler, {
                handle: '.handle',
                animation: 150,
                onEnd: function (/**Event*/evt) {
                    let idsOrder = [];
                    [...trList].map(tr => {
                        const id = tr.getAttribute('data-id');
                        idsOrder.push(id);
                        return tr;
                    });
                    // WIP : Ajax save sections order
                    console.log(idsOrder);
                    self.execUpdate(idsOrder);
                },
            });
        }
    }

    // WIP : Complete execUpdate
    execUpdate(ids) {
        fetch('/admin/ajax/sections-order/', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ ids: ids })
        })
        .then(response => {
            if (!response.status === 200) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            //return response.json();
        })
        /*.then(data => {
            if (data.results && data.results.length > 0) this.buildList(data.results);
            else this.clearList();
        })*/
        .catch(error => console.error('Error:', error));
    }
}
import { Controller } from '@hotwired/stimulus';

/*
 * This is an example Stimulus controller!
 *
 * Any element with a data-controller="listmobiletoggle" attribute will cause
 * this controller to be executed. The name "listmobiletoggle" comes from the filename:
 * listmobiletoggle.js -> "listmobiletoggle"
 *
 * Delete this file or adapt it for your use!
 */

export default class extends Controller {

    // btns : data-group="x" data-id="y" class="btn-toggler"
    // areas : data-group="x" data-id="y" class="area-toggle"

    connect() {
        this.btns = document.querySelectorAll('.btn-toggler');
        this.areas = document.querySelectorAll('.area-toggler');
        if(!this.btns || !this.areas) return;

        this.groups = [];

        this.areas.forEach((area) => {
            const groupTarget = area.getAttribute('data-group');
            this.foundgroup = false;
            this.groups.map(group => {
                if(group.groupName === groupTarget) {
                    this.foundgroup = true;
                    group.areas.push(area);
                }
                return group;
            });
            if(!this.foundgroup) this.groups.push({'groupName':groupTarget,'btns': [],'areas': [area]});
        });

        this.btns.forEach((btn) => {
            const groupTarget = btn.getAttribute('data-group');
            this.foundgroup = false;
            this.groups.map(group => {
                if(group.groupName === groupTarget) {
                    this.foundgroup = true;
                    group.btns.push(btn);
                }
                return group;
            });
            if(!this.foundgroup) this.groups.push({'groupName':groupTarget,'btns': [btn],'areas': []});

            btn.addEventListener('click', (event) => {
                const idTarget = btn.getAttribute('data-id');
                this.groups.map(group => {
                    if(group.groupName === groupTarget) {
                        group.btns.map(btn => {
                            btn.parentNode.classList.remove('disabled');
                            return btn;
                        })
                        group.areas.map(area => {
                            const idArea = area.getAttribute('data-id');
                            area.classList.remove('mobile__show');
                            area.classList.add('mobile__hide');
                            if(idArea === idTarget) {
                                area.classList.add('mobile__show');
                                btn.parentNode.classList.add('disabled');

                                const newUrl = `${window.location.pathname}?group=${groupTarget}&gid=${idTarget}`;
                                history.replaceState(null, '', newUrl);
                            }
                            return area;
                        })
                    }
                    return group;
                });
            });
        });

    }
}
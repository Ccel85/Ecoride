import { Controller } from '@hotwired/stimulus';

/*
 * This is an example Stimulus controller!
 *
 * Any element with a data-controller="hello" attribute will cause
 * this controller to be executed. The name "hello" comes from the filename:
 * hello_controller.js -> "hello"
 *
 * Delete this file or adapt it for your use!
 */
export default class extends Controller {
   /*  connect() {
        this.element.textContent = 'Hello Stimulus! Edit me in assets/controllers/hello_controller.js';
    } */

    static targets = ['chart', 'chartCredit'];

    connect() {
        console.log('🎯 chart_toggle_controller connecté');
    }

    showChart() {
        this.chartTarget.classList.remove('d-none');
        this.chartCreditTarget.classList.add('d-none');
    }

    showChartCredit() {
        this.chartCreditTarget.classList.remove('d-none');
        this.chartTarget.classList.add('d-none');
    }
}

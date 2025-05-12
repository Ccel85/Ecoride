import { Controller } from '@hotwired/stimulus';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://github.com/symfony/stimulus-bridge#lazy-controllers
*/
/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['chart', 'chartCredit','buttonChart','buttonChartCredit']
    
     connect() {
        console.log('🎯 chart_toggle_controller connecté');
    }

    showChart() {
        this.chartTarget.classList.remove('d-none');
        this.chartCreditTarget.classList.add('d-none');
        this.buttonChartCreditTarget.classList.remove('active');
        this.buttonChartTarget.classList.add('active');

    }

    showChartCredit() {
        this.chartCreditTarget.classList.remove('d-none');
        this.chartTarget.classList.add('d-none');
        this.buttonChartTarget.classList.remove('active');
        this.buttonChartCreditTarget.classList.add('active');
    }
}

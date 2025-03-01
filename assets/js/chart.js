
document.addEventListener('DOMContentLoaded',()=>{
    
  //bouton
  const chartButton = document.getElementById('chartButton');
  const chartCreditButton = document.getElementById('chartCreditButton');

  //div chart
  const chart = document.getElementById('chart');
  const chartCredit = document.getElementById('chartCredit');

  if( 'chartButton' && 'chartCreditButton' ) {  
    chartButton.addEventListener('click',()=> {
      chart.className = 'chart';
      chartCredit.className = 'chartCredit d-none';
      
    })
    chartCreditButton.addEventListener('click',()=> {

      chartCredit.className = 'chartCredit';
      chart.className = 'chart d-none';
      
    })
  }
});

  // Vérification si les éléments existent
  /* if (chartButton && chartCreditButton && chart && chartCredit) {  
    // Écoute des événements click
    chartButton.addEventListener('click', () => {
      chart.classList.remove('d-none');
     /*  chartCredit.classList.add('d-none'); 
    });

    chartCreditButton.addEventListener('click', () => {
      chartCredit.classList.remove('d-none');
      /* chart.classList.add('d-none'); 
    }); */

    
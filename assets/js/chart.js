
document.addEventListener('DOMContentLoaded',()=>{
    
  //bouton
  const chartButton = document.getElementById('chartButton');
  const chartCreditButton = document.getElementById('chartCreditButton');

  //div chart
  const chart = document.getElementById('chart');
  const chartCredit = document.getElementById('chartCredit');

  if( 'chartButton' || 'chartCreditButton' ) {  
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
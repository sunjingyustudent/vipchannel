<div class="wait-statistics-page">
    <canvas id="myChart" width="400" height="150"></canvas>
</div>
<script src="/js/Chart.js"></script>
<script>
    var ctx = document.getElementById("myChart").getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= $time?>,
            datasets: [
                {
                    label: '未读消息统计',
                    fillColor: "rgba(220,220,220,0.5)",
                    strokeColor: "rgba(220,220,220,1)",
                    pointColor: "rgba(220,220,220,1)",
                    pointStrokeColor: "#fff",
                    data: <?= $num?>
                },
//{  
//fillColor : "rgba(151,187,205,0.5)",  
//strokeColor : "rgba(151,187,205,1)",  
//pointColor : "rgba(151,187,205,1)",  
//pointStrokeColor : "#fff",  
//data : [28,48,40,19,96,27,100]  
//}  
            ]
        },

        options: {
            scales: {
                yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
            }
        }
    });
</script>


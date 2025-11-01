/**
 * Theme: Rasket- Responsive Bootstrap 5 Admin Dashboard
 * Author: Techzaa
 * Module/App: Apex Bar Charts
 */

//
// BASIC POLAR AREA CHART
//

var colors = ['#1e84c4', '#53389f', '#7f56da', '#ff86c8', '#ed5565', '#23c6c8'];
var options = {
    series: [14, 23, 21, 17, 15, 10],
    chart: {
        height: 380,
        type: 'polarArea',
    },
    stroke: {
        colors: ['#fff']
    },
    fill: {
        opacity: 0.8
    },
    labels: ['Vote A', 'Vote B', 'Vote C', 'Vote D', 'Vote E', 'Vote F'],
    legend: {
        position: 'bottom'
    },
    colors: colors,
    responsive: [{
        breakpoint: 480,
        options: {
            chart: {
                width: 200
            },
            legend: {
                position: 'bottom'
            }
        }
    }]
};
var chart = new ApexCharts(document.querySelector("#basic-polar-area"), options);
chart.render();

//
// MONOCHROME POLAR AREA
//

var colors = ["#1e84c4"];
var options = {
    series: [42, 47, 52, 58, 65],
    chart: {
        height: 380,
        type: 'polarArea'
    },
    labels: ['Rose A', 'Rose B', 'Rose C', 'Rose D', 'Rose E'],
    fill: {
        opacity: 1
    },
    stroke: {
        width: 1
    },
    yaxis: {
        show: false
    },
    legend: {
        position: 'bottom'
    },
    plotOptions: {
        polarArea: {
            rings: {
                strokeWidth: 0
            },
            spokes: {
                strokeWidth: 0
            },
        }
    },
    theme: {
        monochrome: {
            enabled: true,
            shadeTo: 'light',
            color: '#727cf5',
            shadeIntensity: 0.6
        }
    }
};

var chart = new ApexCharts(document.querySelector("#monochrome-polar-area"), options);
chart.render();
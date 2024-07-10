<template>
    <Doughnut id="my-chart-id" :options="chartOptions" :data="chartData" :arc="45" />
</template>
  
<script>
import { Doughnut } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement } from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement)

export default {
    name: 'BarChart',
    components: { Doughnut },
    props: {

    },
    props: {
        chartData: {
            type: Object,
            required: false
        },
        chartOptions: {
            type: Object,
            default: {
                responsive: true,
                backgroundColor: [
                    'rgb(75, 192, 192)',
                    'rgb(255, 205, 86)',
                    'rgb(54, 162, 235)',
                    'rgb(255, 99, 132)',
                    'rgb(153, 102, 255)',
                    'rgb(255, 159, 64)',
                    'rgb(128, 128, 128)',
                    'rgb(0, 128, 0)',
                    'rgb(255, 0, 0)',
                ],
                offset: 2 
            }
        }
    },
    // data() {
    //     return {
    //         chartData: {
    //             labels: ['January', 'February', 'March'],
    //             datasets: [{ data: [40, 20, 12] }],
    //         },
    //         chartOptions: {
    //             responsive: true,
    //             backgroundColor: [
    //                 'rgb(75, 192, 192)',
    //                 'rgb(255, 205, 86)',
    //                 'rgb(54, 162, 235)',
    //                 'rgb(255, 99, 132)',
    //                 'rgb(153, 102, 255)',
    //                 'rgb(255, 159, 64)',
    //                 'rgb(128, 128, 128)',
    //                 'rgb(0, 128, 0)',
    //                 'rgb(255, 0, 0)',
    //             ],
    //         }
    //     }
    // }
}
</script>
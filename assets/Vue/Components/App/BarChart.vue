<template>
    <Bar id="my-chart-id" :options="chartOptions" :data="chartData" />
</template>
  
<script>
import { Bar } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

export default {
    name: 'BarChart',
    components: { Bar },
    props: {
        chartData: {
            type: Object,
            required: false,
            default: {
            }
        },
        chartOptions: {
            type: Object,
            default: {
                scales: {
                    y: {
                        suggestedMin: 0,
                        //suggestedMax: 5,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                responsive: true,
                backgroundColor: ['rgb(60, 120, 50)'],
            }
        }

    },


}
</script>
import ApexCharts from './apexcharts';
import { call as fetchMany } from 'core/ajax';

let chart = null;

/**
 * Fetch and render data.
 */
const renderData = (selector, year = 0) => {
    fetchMany([{
        methodname: 'local_analysis_dashboard_get_site_stats',
        args: { year: parseInt(year) }
    }])[0]
    .then(data => {
        if (chart) {
            chart.updateOptions({
                series: [{
                    name: 'Users',
                    data: data.series
                }],
                xaxis: {
                    categories: data.labels
                }
            });
        } else {
            const options = {
                chart: {
                    type: 'bar',
                    height: 350
                },
                series: [{
                    name: 'Users',
                    data: data.series
                }],
                xaxis: {
                    categories: data.labels
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                    }
                },
                colors: ['#008FFB'],
                title: {
                    text: 'User Distribution',
                    align: 'center'
                }
            };

            chart = new ApexCharts(document.querySelector(selector), options);
            chart.render();
        }
    })
    .catch(error => {
        console.error('Analysis Dashboard Error:', error);
    });
};

/**
 * Initialize Dashboard.
 */
export const init = (selector) => {
    const yearFilter = document.getElementById('year-filter');
    const currentYear = new Date().getFullYear();

    // Populate year filter (last 5 years)
    for (let i = 0; i < 5; i++) {
        const year = currentYear - i;
        const option = document.createElement('option');
        option.value = year;
        option.textContent = year;
        yearFilter.appendChild(option);
    }

    // Listen for changes
    yearFilter.addEventListener('change', (e) => {
        renderData(selector, e.target.value);
    });

    // Initial render
    renderData(selector, 0);
};

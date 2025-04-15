(function ($) {
    $(function () {
        const salesData = window.salesData || [];
        const currency = window.currency || '$';
        const chartElement = document.getElementById('salesChart');

        if (!chartElement) {
            console.error('Sales chart element not found');
            return;
        }

        const ctx = chartElement.getContext('2d');

        const formatNumber = function(value) {
            if (value >= 1000000) {
                return (value / 1000000).toFixed(2) + 'M';
            } else if (value >= 1000) {
                return (value / 1000).toFixed(2) + 'K';
            }
            return value.toFixed(2);
        };

        const growthData = [];
        if (salesData.length > 1) {
            for (let i = 1; i < salesData.length; i++) {
                const prevValue = salesData[i - 1].total;
                const currValue = salesData[i].total;
                const growth = prevValue === 0 ? 0 : ((currValue - prevValue) / prevValue) * 100;
                growthData.push(parseFloat(growth.toFixed(2)));
            }
            growthData.unshift(null);
        }

        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: salesData.map(item => item.date),
                datasets: [
                    {
                        label: 'Sales',
                        data: salesData.map(item => parseFloat(item.total.toFixed(2))),
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        borderColor: '#0d6efd',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true,
                        pointBackgroundColor: '#0d6efd',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        order: 1
                    },
                    {
                        label: 'Growth %',
                        data: growthData,
                        borderColor: '#198754',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        tension: 0.2,
                        fill: false,
                        pointBackgroundColor: '#198754',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 1,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        yAxisID: 'y1',
                        order: 2,
                        hidden: salesData.length <= 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 6
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                        titleColor: '#212529',
                        bodyColor: '#212529',
                        borderColor: 'rgba(0, 0, 0, 0.1)',
                        borderWidth: 1,
                        padding: 10,
                        cornerRadius: 4,
                        displayColors: true,
                        callbacks: {
                            label: function (context) {
                                if (context.datasetIndex === 0) {
                                    return 'Sales: ' + context.parsed.y.toFixed(2) + ' ' + currency;
                                } else {
                                    if (context.parsed.y === null) return 'Growth: N/A';
                                    return 'Growth: ' + context.parsed.y.toFixed(2) + '%';
                                }
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false,
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            padding: 8,
                            callback: function (value) {
                                return formatNumber(value) + ' ' + currency;
                            }
                        }
                    },
                    y1: {
                        position: 'right',
                        beginAtZero: false,
                        grid: {
                            drawOnChartArea: false
                        },
                        ticks: {
                            padding: 8,
                            callback: function (value) {
                                return value.toFixed(2) + '%';
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            padding: 8
                        }
                    }
                },
                animations: {
                    tension: {
                        duration: 1000,
                        easing: 'linear'
                    }
                }
            }
        });

        chartElement.addEventListener('wheel', function (e) {
            if (e.ctrlKey || e.metaKey) {
                e.preventDefault();
                const delta = e.deltaY > 0 ? 1.1 : 0.9;
                const yScale = chart.scales.y;
                const newMin = yScale.min * delta;
                const newMax = yScale.max * delta;

                chart.options.scales.y.min = newMin > 0 ? newMin : 0;
                chart.options.scales.y.max = newMax;
                chart.update();
            }
        });

        window.salesChart = chart;

        $(window).on('resize', function () {
            chart.resize();
        });
    });
}(jQuery));

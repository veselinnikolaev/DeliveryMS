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

        const formatNumber = function (value) {
            if (value >= 1000000) {
                return (value / 1000000).toFixed(2) + 'M';
            } else if (value >= 1000) {
                return (value / 1000).toFixed(2) + 'K';
            }
            return value.toFixed(2);
        };

        // Improved growth calculation with smoothing and caps
        const calculateGrowth = (prevValue, currValue) => {
            if (prevValue === 0 && currValue === 0)
                return 0;
            if (prevValue === 0)
                return currValue > 0 ? 100 : 0;
            const growth = ((currValue - prevValue) / prevValue) * 100;
            // Cap growth between -50% and 100%
            return Math.max(Math.min(growth, 100), -50);
        };

        // Calculate growth data with smoothing
        const growthData = [];
        const smoothingFactor = 0.3; // Adjust this value between 0 and 1

        if (salesData.length > 1) {
            let previousGrowth = 0;
            for (let i = 1; i < salesData.length; i++) {
                const prevValue = salesData[i - 1].total;
                const currValue = salesData[i].total;
                const rawGrowth = calculateGrowth(prevValue, currValue);
                // Apply exponential smoothing
                const smoothedGrowth = (smoothingFactor * rawGrowth) + ((1 - smoothingFactor) * previousGrowth);
                growthData.push(parseFloat(smoothedGrowth.toFixed(2)));
                previousGrowth = smoothedGrowth;
            }
            growthData.unshift(null); // Add null for the first point
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
                                    if (context.parsed.y === null)
                                        return 'Growth: N/A';
                                    const growth = context.parsed.y.toFixed(2);
                                    const indicator = Math.abs(growth) >= 49 ? ' (capped)' : '';
                                    return 'Growth: ' + growth + '%' + indicator;
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
                        beginAtZero: true, // Changed to true for better visualization
                        min: -50, // Set minimum value
                        max: 100, // Set maximum value
                        grid: {
                            drawOnChartArea: false
                        },
                        ticks: {
                            padding: 8,
                            callback: function (value) {
                                return value.toFixed(0) + '%'; // Removed decimal places for cleaner display
                            },
                            stepSize: 25 // Add step size for cleaner intervals
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            padding: 8,
                            maxRotation: 45, // Improve readability for long dates
                            minRotation: 0
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

        // Zoom functionality
        chartElement.addEventListener('wheel', function (e) {
            if (e.ctrlKey || e.metaKey) {
                e.preventDefault();
                const delta = e.deltaY > 0 ? 1.1 : 0.9;
                const yScale = chart.scales.y;
                const newMin = yScale.min * delta;
                const newMax = yScale.max * delta;

                chart.options.scales.y.min = newMin > 0 ? newMin : 0;
                chart.options.scales.y.max = newMax;
                chart.update('none'); // Use 'none' for better performance
            }
        });

        // Store chart reference
        window.salesChart = chart;

        // Optimize resize handler
        let resizeTimeout;
        $(window).on('resize', function () {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(function () {
                chart.resize();
            }, 250); // Debounce resize events
        });
    });
}(jQuery));
function loadReviewerWorkloadChart(reviewerLabels, reviewerData) {
    new Chart(document.getElementById('reviewerWorkloadChart'), {
        type: 'bar',
        data: {
            labels: reviewerLabels,
            datasets: [{
                label: 'Tickets Assigned',
                data: reviewerData,
                backgroundColor: '#2E3192',
                borderRadius: 6,
                barThickness: 22
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    grid: { color: '#e5e7eb' },
                    ticks: {
                        stepSize: 1,  // This ensures the ticks are whole numbers
                        precision: 0  // No decimals
                    }
                },
                y: {
                    grid: { display: false }
                }
            }
        }
    });
}

function loadTicketVolumeChart(labels, data) {
    const ctx = document.getElementById("ticketVolumeChart");
    if (!ctx) return;

    new Chart(ctx, {
        type: "line",
        data: {
            labels: labels,
            datasets: [{
                label: "Tickets Created",
                data: data,
                fill: false,
                borderColor: "#2E3192",
                borderWidth: 2,
                tension: 0.3,
                pointBackgroundColor: "#2E3192",
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: "Number of Tickets"
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: "Date"
                    }
                }
            }
        }
        
    });
}

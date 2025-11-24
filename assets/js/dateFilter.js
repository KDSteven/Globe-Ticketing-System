// dateFilter.js
function renderDateInput(period, date, month, quarter, year) {
    const container = document.getElementById("dateContainer");
    let html = "";

    switch(period) {
        case "daily":
            html = `<input type="date" name="date" value="${date}">`;
            break;

        case "monthly":
            html = `<input type="month" name="month" value="${month}">`;
            break;

        case "quarterly":
            html = `
                <select name="quarter">
                    <option value="Q1" ${quarter === "Q1" ? "selected" : ""}>Q1 (Jan–Mar)</option>
                    <option value="Q2" ${quarter === "Q2" ? "selected" : ""}>Q2 (Apr–Jun)</option>
                    <option value="Q3" ${quarter === "Q3" ? "selected" : ""}>Q3 (Jul–Sep)</option>
                    <option value="Q4" ${quarter === "Q4" ? "selected" : ""}>Q4 (Oct–Dec)</option>
                </select>
                <input type="number" name="year" min="2020" max="2100" value="${year}">
            `;
            break;

        case "yearly":
            html = `<input type="number" name="year" min="2020" max="2100" value="${year}">`;
            break;
    }

    container.innerHTML = html;
}

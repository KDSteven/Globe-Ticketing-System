// dateFilter.js
function renderDateInput(period, date, month, quarter, year) {
    const container = document.getElementById("dateContainer");
    container.textContent = ''; // clear safely

    function makeInput(type, name, value, attrs = {}) {
        const el = document.createElement('input');
        el.type = type;
        el.name = name;
        el.value = value;
        Object.entries(attrs).forEach(([k, v]) => el.setAttribute(k, v));
        return el;
    }

    switch (period) {
        case "daily":
            container.appendChild(makeInput('date', 'date', date));
            break;

        case "monthly":
            container.appendChild(makeInput('month', 'month', month));
            break;

        case "quarterly": {
            const quarters = [
                ['Q1', 'Q1 (Jan–Mar)'],
                ['Q2', 'Q2 (Apr–Jun)'],
                ['Q3', 'Q3 (Jul–Sep)'],
                ['Q4', 'Q4 (Oct–Dec)'],
            ];
            const sel = document.createElement('select');
            sel.name = 'quarter';
            quarters.forEach(([val, label]) => {
                const opt = document.createElement('option');
                opt.value = val;
                opt.textContent = label;
                if (val === quarter) opt.selected = true;
                sel.appendChild(opt);
            });
            container.appendChild(sel);
            container.appendChild(makeInput('number', 'year', year, { min: '2020', max: '2100' }));
            break;
        }

        case "yearly":
            container.appendChild(makeInput('number', 'year', year, { min: '2020', max: '2100' }));
            break;
    }
}

(async function () {

    // HTML elements
    const groupSel   = document.getElementById("group");
    const tribeSel   = document.getElementById("tribe");
    const tribeBlock = document.getElementById("tribe-block");

    const lawyerDisp = document.getElementById("lawyer_display");
    const lawyer     = document.getElementById("lawyer");

    const ccDisp = document.getElementById("cc_display");
    const cc     = document.getElementById("cc_emails");

    // Load routing rules from DB
    let routing = {};
    try {
        const res = await fetch("/api/get_routing_list.php");
        routing = await res.json();
    } catch (err) {
        console.error("Cannot load routing list:", err);
    }

    // Populate GROUP dropdown (ALL ticket types including tribes)
    const ticketTypes = Object.keys(routing).sort();

    ticketTypes.forEach(type => {
        const opt = document.createElement("option");
        opt.value = type;
        opt.textContent = type;
        groupSel.appendChild(opt);
    });

    // When group changes
    groupSel.addEventListener("change", () => {
        const type = groupSel.value;
        const rule = routing[type];

        if (!rule) return;

        lawyerDisp.value = rule.lawyer;
        lawyer.value = rule.email;

        ccDisp.value = rule.cc.join(", ");
        cc.value = rule.cc.join(",");
    });

})();

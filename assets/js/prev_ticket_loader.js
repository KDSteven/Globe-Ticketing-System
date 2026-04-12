/******************************************************
 *  LOAD PREVIOUS TICKET DETAILS MODULE
 *  Works for customer_portal.php
 ******************************************************/

// === Show/hide Previous Ticket ID input ===
document.querySelectorAll("input[name='prev_reviewed']").forEach(radio => {
    radio.addEventListener("change", e => {
        const show = e.target.value === "Yes";
        document.getElementById("prevTicketWrap").style.display = show ? "block" : "none";
    });
});

// === Utility: Wait until group dropdown is populated ===
function waitForGroupOptions(callback) {
    const sel = document.getElementById("group");

    // If already loaded, run immediately
    if (sel.options.length > 0) {
        callback();
        return;
    }

    // Otherwise, wait for changes
    const observer = new MutationObserver(() => {
        if (sel.options.length > 0) {
            observer.disconnect();
            callback();
        }
    });

    observer.observe(sel, { childList: true });
}

// === Status helper: avoids innerHTML with dynamic content ===
function setStatus(el, text, type = 'info') {
    const colors = { error: 'red', success: 'green', info: '' };
    el.textContent = '';
    const span = document.createElement('span');
    if (colors[type]) span.style.color = colors[type];
    span.textContent = text;
    el.appendChild(span);
}

// === Load Previous Ticket Details ===
document.getElementById("loadPrevTicketBtn").addEventListener("click", () => {
    const ticketId = document.getElementById("prev_ticket_id").value.trim();
    const statusBox = document.getElementById("prevTicketStatus");

    if (ticketId === "") {
        setStatus(statusBox, "Please enter a Ticket ID.", "error");
        return;
    }

    setStatus(statusBox, "Loading…");

    fetch(`/api/get_previous_ticket.php?code=${encodeURIComponent(ticketId)}`)
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                setStatus(statusBox, data.error, "error");
                return;
            }

            setStatus(statusBox, "Ticket loaded! Auto-filled details.", "success");

            // === Fill text fields ===
            document.getElementById("full_name").value = data.full_name;
            document.getElementById("email").value = data.email;

            // === WAIT FOR GROUP OPTIONS TO LOAD BEFORE SETTING ===
            waitForGroupOptions(() => {
                const groupSel = document.getElementById("group");
                groupSel.value = data.grp;
                groupSel.dispatchEvent(new Event("change")); // triggers routing auto-fill

                // Tribe handling
                if (data.tribe) {
                    document.getElementById("tribe-block").style.display = "block";
                    const tribeSel = document.getElementById("tribe");
                    tribeSel.value = data.tribe;
                    tribeSel.dispatchEvent(new Event("change"));
                }
            });

            // === Contract details ===
            document.getElementById("summary").value = data.summary;
            document.getElementById("customer").value = data.customer;
            document.getElementById("vendor").value = data.vendor;

            // === Contract type selector ===
            if (["Data Processing Agreement (DPA)", "Data Sharing Agreement (DSA)", "Non-Disclosure Agreement (NDA)"].includes(data.contract_type)) {
                document.querySelector(`input[name="contract_type"][value="${data.contract_type}"]`).checked = true;
            } else {
                document.getElementById("contract_other_radio").checked = true;
                document.getElementById("contract_other").value = data.contract_type;
            }

            // === PD Nature ===
            if (data.pd_nature === "OTHER") {
                document.getElementById("pd_other_radio").checked = true;
                document.getElementById("pd_other_text").style.display = "block";
                document.getElementById("pd_other_text").value = data.pd_other_text;
            } else {
                document.querySelector(`input[name="pd_nature"][value="${data.pd_nature}"]`).checked = true;
            }

            // === Remaining details ===
            document.getElementById("clauses").value = data.clauses;
            document.getElementById("doc_link").value = data.doc_link;
        })
        .catch(err => {
            setStatus(statusBox, `System error: ${err instanceof Error ? err.message : String(err)}`, "error");
        });
});

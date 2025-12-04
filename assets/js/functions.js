// Holiday Modal Functions
document.addEventListener("DOMContentLoaded", function () {
    
    const openBtn  = document.getElementById("addHolidayBtn");
    const closeBtn = document.getElementById("closeHolidayModal");
    const modal    = document.getElementById("holidayModal");

    if (openBtn && modal) {
        openBtn.addEventListener("click", () => {
            modal.style.display = "flex";
        });
    }

    if (closeBtn && modal) {
        closeBtn.addEventListener("click", () => {
            modal.style.display = "none";
        });
    }
});

// -------------------------------
// Add Lawyer Modal (Admin Only)
// -------------------------------
function initAddLawyerModal() {
    const openBtn = document.getElementById("openAddLawyerModal");
    const closeBtn = document.getElementById("closeAddLawyerModal");
    const modal = document.getElementById("addLawyerModal");

    if (!openBtn || !closeBtn || !modal) return; // Prevent errors on pages without modal

    openBtn.addEventListener("click", () => {
        modal.style.display = "flex";
    });

    closeBtn.addEventListener("click", () => {
        modal.style.display = "none";
    });
}

// Initialize automatically on page load
document.addEventListener("DOMContentLoaded", initAddLawyerModal);

// -------------------------------
// Edit Lawyer Modal Logic
// -------------------------------
function initEditLawyerModal() {
    const modal = document.getElementById("editLawyerModal");
    const closeBtn = document.getElementById("closeEditLawyerModal");

    const idField = document.getElementById("editLawyerId");
    const nameField = document.getElementById("editLawyerName");
    const emailField = document.getElementById("editLawyerEmail");
    const roleField = document.getElementById("editLawyerRole");

    if (!modal || !closeBtn) return;

    // Attach click events to all Edit buttons
    document.querySelectorAll(".editLawyerBtn").forEach(btn => {
        btn.addEventListener("click", () => {

            // Fill modal inputs with button's data attributes
            idField.value = btn.dataset.id;
            nameField.value = btn.dataset.name;
            emailField.value = btn.dataset.email;
            roleField.value = btn.dataset.role;

            modal.style.display = "flex";
        });
    });

    closeBtn.addEventListener("click", () => {
        modal.style.display = "none";
    });
}

document.addEventListener("DOMContentLoaded", () => {
    initAddLawyerModal();
    initEditLawyerModal();
});


// DELETE MODAL FOR ROUTING
document.querySelectorAll(".deleteRuleBtn").forEach(btn => {
    btn.onclick = () => {
        const id = btn.dataset.id;
        const type = btn.dataset.type;

        document.getElementById("deleteRuleId").value = id;

        document.getElementById("deleteRuleLabel").innerText =
            `Are you sure you want to delete the routing rule for: "${type}"?`;

        document.getElementById("deleteRuleModal").style.display = "flex";
    };
});

document.getElementById("closeDeleteRuleModal").onclick = () =>
    document.getElementById("deleteRuleModal").style.display = "none";

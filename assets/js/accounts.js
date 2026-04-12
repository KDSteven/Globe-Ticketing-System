document.getElementById("changePwForm")?.addEventListener("submit", async function (e) {
    e.preventDefault();

    const current = document.getElementById("curr_pw").value.trim();
    const newpw   = document.getElementById("new_pw").value.trim();
    const confirm = document.getElementById("confirm_pw").value.trim();

    if (!current || !newpw || !confirm) {
        toast.warning("Missing Fields", "Please fill out all fields.");
        return;
    }

    if (newpw !== confirm) {
        toast.error("Mismatch", "New passwords do not match.");
        return;
    }

    const payload = { current, new: newpw, confirm };

    try {
        const data = await apiFetch("/api/change_password.php", {
            method: "POST",
            body: JSON.stringify(payload)
        });

        if (data.ok) {
            toast.success("Password Updated", "Your password has been changed.");
            
            // Optional: wipe fields
            document.getElementById("curr_pw").value = "";
            document.getElementById("new_pw").value = "";
            document.getElementById("confirm_pw").value = "";

        } else {
            toast.error("Error", data.error || "Unable to update password.");
        }

    } catch (err) {
        toast.error("Network Error", "Could not reach server.");
    }
});

const newPw = document.getElementById("new_pw");
const confirmPw = document.getElementById("confirm_pw");
const pwMsg = document.getElementById("pwMatchMsg");

// Live check for password match
function checkPasswordMatch() {
    const p1 = newPw.value.trim();
    const p2 = confirmPw.value.trim();

    if (p1 === "" && p2 === "") {
        pwMsg.textContent = "";
        pwMsg.className = "pw-hint";
        return;
    }

    if (p1 === p2) {
        pwMsg.textContent = "✓ Passwords match";
        pwMsg.className = "pw-hint ok";
    } else {
        pwMsg.textContent = "✗ Passwords do not match";
        pwMsg.className = "pw-hint error";
    }
}

newPw.addEventListener("input", checkPasswordMatch);
confirmPw.addEventListener("input", checkPasswordMatch);


// ===== GET LICENSE FROM IndexedDB =====
async function getLicense() {
  const db = await new Promise((resolve, reject) => {
    const req = indexedDB.open("EthiomarkBingoDB");
    req.onsuccess = () => resolve(req.result);
    req.onerror = () => reject();
  });

  return new Promise((resolve, reject) => {
    const tx = db.transaction("license", "readonly");
    const store = tx.objectStore("license");
    const req = store.getAll();

    req.onsuccess = () => resolve(req.result[0] || null);
    req.onerror = () => reject();
  });
}


// ===== LICENSE SECURITY CHECK =====
// This function ensures that:
// 1. The system is running on the correct machine
// 2. IndexedDB (license) and server checkpoint are in sync
// 3. No rollback, tampering, or copying has occurred
async function checkLicenseSecurity() {
  // 🔹 Get machine ID from server (trusted source)
  const resMachine = await fetch('logic/machine.php');
  const machineData = await resMachine.json();
  const staticMachineId = machineData.static_machine_id;

  // 🔹 Get local license from IndexedDB (client-side)
  const license = await getLicense();

  console.log("static_machine_id:", staticMachineId);
  console.log("license:", license);

  // =========================================================
  // 🆕 CASE 1: FIRST RUN (NO LICENSE AT ALL)
  // =========================================================
  if (!license) {
    console.warn("No license found → creating initial checkpoint");

    const data = {
      total_deposited: 0,
      total_revenue: 0,
      static_machine_id: staticMachineId,
    };

    // Create initial checkpoint on server
    await fetch('logic/machine.php?action=saveCheckpoint', {
      method: 'POST',
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(data)
    });

    console.log("🆕 Skeletal checkpoint created");
    return true;
  }

  // =========================================================
  // 🔹 Load checkpoint from server (trusted storage)
  // =========================================================
  const resCheckpoint = await fetch('logic/machine.php?action=load_checkpoint');
  const checkpoint = await resCheckpoint.json();

  // =========================================================
  // ⚠️ CASE 2: CHECKPOINT MISSING
  // =========================================================
  if (!checkpoint || Object.keys(checkpoint).length === 0) {

    // Allow only if this is truly a clean system (no money yet)
    if (license.total_deposited === 0 && license.total_revenue === 0) {
      console.log("🆕 Creating checkpoint (clean first run)");

      const data = {
        total_deposited: 0,
        total_revenue: 0,
        static_machine_id: staticMachineId,
      };

      await fetch('logic/machine.php?action=saveCheckpoint', {
        method: 'POST',
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data)
      });

      return true;
    } else {
      // ❌ License exists but checkpoint missing → likely copied system
      console.error("🚨 Missing checkpoint but license has data → possible copy detected");
      return false;
    }
  }

  console.log("Checkpoint:", checkpoint);
  console.log("License:", license);

  // =========================================================
  // 🚨 CASE 3: MACHINE VALIDATION
  // Prevent running copied data on another machine
  // =========================================================
  if (checkpoint.static_machine_id !== staticMachineId) {
    console.error("🚨 Copied system detected (machine mismatch)");
    return false;
  }

  // =========================================================
  // 🚨 CASE 4: DATA CONSISTENCY CHECK
  // Ensure both sources (IndexedDB + server) match exactly
  // =========================================================
  if (
    license.total_revenue !== checkpoint.total_revenue ||
    license.total_deposited !== checkpoint.total_deposited
  ) {
    console.error("🚨 Data mismatch detected (tampering or corruption)");

    console.error("Mismatch details:", {
      license,
      checkpoint
    });

    return false;
  }

  // =========================================================
  // ✅ ALL CHECKS PASSED
  // =========================================================
  console.log("✅ License is secure and consistent");
  return true;
}


async function showSecurityResetModal() {
  return new Promise((resolve) => {

    const modal = document.getElementById("securityModal");
    const msg = document.getElementById("securityMsg");

    const yesBtn = document.getElementById("resetYes");
    const noBtn  = document.getElementById("resetNo");

    modal.style.display = "flex";

    // reset state every time
    yesBtn.disabled = false;
    noBtn.disabled = false;

    msg.innerHTML = `
      System data is inconsistent.<br>
      This may be due to copying or corruption.<br><br>
      Do you want to reset data and continue?
    `;

    yesBtn.onclick = async () => {
      yesBtn.disabled = true;
      noBtn.disabled = true;

      msg.innerHTML = `⚠ Resetting system...<br>Please wait...`;

      try {
        const res = await fetch('logic/machine.php');
        const { static_machine_id } = await res.json();

        // delete local license
        await API.deleteLicense();

        // reset server checkpoint
        await fetch('logic/machine.php?action=saveCheckpoint', {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            total_deposited: 0,
            total_revenue: 0,
            static_machine_id
          })
        });

        msg.innerHTML = `✅ Reset successful.<br>Reloading...`;

        setTimeout(() => {
          modal.style.display = "none";
          resolve(true);
          location.reload();
        }, 5000);

      } catch (err) {
        console.error(err);

        msg.innerHTML = `❌ Reset failed.<br>Please try again.`;

        yesBtn.disabled = false;
        noBtn.disabled = false;

        resolve(false);
      }
    };

    noBtn.onclick = () => {
      msg.innerHTML = `
        🚫 Access denied.<br><br>
        Please contact your system provider:<br><br>
        📞 <a href="tel:0918101037"
           style="color:#00c6ff; text-decoration:none; font-weight:700;">
           0918101037
        </a><br><br>
        Or click YES to reset system.
      `;
    };

  });
}
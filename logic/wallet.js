// ===== GET MACHINE ID =====
async function getMachineId() {
  const res = await fetch('logic/machine.php');
  const data = await res.json();
  return data.machine_id;
}

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

// ===== LOAD CHECKPOINT FILE =====
async function loadCheckpoint() {
  const res = await fetch('logic/machine.php?action=load');
  return await res.json();
}

// ===== SAVE CHECKPOINT FILE =====
async function saveCheckpoint(data) {
  await fetch('logic/machine.php?action=save', {
    method: 'POST',
    body: JSON.stringify(data)
  });
}

// ===== MAIN CHECK =====
async function checkLicenseSecurity() {
  const machineId = await getMachineId();
  const license = await getLicense();

  if (!license) {
    console.warn("No license found");
    return true;
  }

  const checkpoint = await loadCheckpoint();

  // 👉 FIRST RUN → CREATE CHECKPOINT
  if (!checkpoint) {
    console.log("🆕 Creating checkpoint...");

    await saveCheckpoint({
      total_deposited: license.total_deposited,
      total_revenue: license.total_revenue
    });

    return true;
  }

  // 🚨 CHECKS
  if (checkpoint.machine_id !== machineId) {
    alert("🚨 Copied system detected (machine mismatch)");
    return false;
  }

  if (checkpoint.total_deposited !== license.total_deposited) {
    alert("🚨 Deposit tampering detected");
    return false;
  }

  if (license.total_revenue < checkpoint.total_revenue) {
    alert("🚨 Rollback detected (database replaced)");
    return false;
  }

  // ✅ UPDATE CHECKPOINT (important)
  await saveCheckpoint({
    total_deposited: license.total_deposited,
    total_revenue: license.total_revenue
  });

  console.log("✅ License secure");
  return true;
}
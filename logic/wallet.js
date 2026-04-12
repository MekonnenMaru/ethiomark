
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


// ===== MAIN CHECK =====
async function checkLicenseSecurity() {
    const getstaticmachinid = await fetch('logic/machine.php');
    const getstaticmachiniddata = await getstaticmachinid.json();
    const staticMachineId = getstaticmachiniddata.static_machine_id;  // get machine id from php its live checked
    const license = await getLicense();  // get license from indexeddb its from indexeddb
    console.log("static_machine_id: ", staticMachineId);
    console.log("license: ", license);
    
    if (!license) {
      console.warn("No license found, we will create skeletal checkpoint. Please make a deposit to initialize license.");
      data={
          total_deposited: 0,
          total_revenue: 0,
          static_machine_id: staticMachineId,
      };
      await fetch('logic/machine.php?action=saveCheckpoint', {
        method: 'POST',
        body: JSON.stringify(data)
      });
      console.log("🆕 Skeletal checkpoint created succesfully");
      return true;
    }





    // get checkpoint from file system (php) its from local storage
    const res_fromcheckpointFile = await fetch('logic/machine.php?action=load_checkpoint');
    const checkpoint_res = await res_fromcheckpointFile.json();

    // 👉 FIRST RUN → CREATE CHECKPOINT
    if (!checkpoint_res) {
      console.log("🆕 Creating checkpoint...");
      data={
          total_deposited: 0,
          total_revenue: 0,
          static_machine_id: staticMachineId,
      };
      await fetch('logic/machine.php?action=saveCheckpoint', {
        method: 'POST',
        body: JSON.stringify(data)
      });
      return true;
    }

    console.log("FROM DB",
      "checkpoint_res: ", checkpoint_res,
      "license: ", license
    );

    /*
    licence
      {
          "machine_id": "06248F93",
          "packages": [
              {
                  "sn": "1",
                  "amount": 3000,
                  "activated_at": "2026-04-12T05:05:37.635Z"
              },
              {
                  "sn": "7",
                  "amount": 5000,
                  "activated_at": "2026-04-12T05:15:31.749Z"
              }
          ],
          "total_deposited": 8000,
          "total_revenue": 1100,
          "activation_attempts": 0,
          "activation_locked": false,
          "activation_locked_at": null,
          "unlock_nonce": 0,
          "static_machine_id": ""
      }

checkpoint_res
{
    "static_machine_id": "c0f4e2d71359b471d4408509765d88e04e1fb44f86fc6356ea3a0932e765a169",
    "total_deposited": 8000,
    "total_revenue": 0
}

    */
    

    // 🚨 CHECKS
    if (checkpoint_res.static_machine_id !== staticMachineId) {
      alert("🚨 Copied system detected (machine mismatch)");
      return false;
    }

    if (checkpoint_res.total_deposited !== license.total_deposited) {
      alert("🚨 Deposit tampering detected");
      return false;
    }

    if (license.total_revenue < checkpoint_res.total_revenue) {
      alert("🚨 Rollback detected (database replaced)");
      return false;
    }

    console.log("✅ License secure");
    return true;
}
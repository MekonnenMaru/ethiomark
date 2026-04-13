/*  ═══════════════════════════════════════════════════════════════
    api.js  —  Ethiomark Bingo  |  Service / API abstraction layer
    ───────────────────────────────────────────────────────────────
    The frontend (index.html, reg_new_game.html, report.html,
    login.html) talks ONLY to window.API.  Never to db.js directly.

    CURRENT DRIVER: IndexedDB  (db.js)
    TO SWITCH BACKEND: replace the body of each function below
    with fetch() calls to your PHP / Node.js REST endpoint.
    The HTML/JS frontend requires zero changes.
  ═══════════════════════════════════════════════════════════════ */

window.API = (() => {

  /* ────────────────────────────────────────────────────────────
     INITIALISATION
  ──────────────────────────────────────────────────────────── */

  async function init() {
    return EthiomarkDB.openDB();
  }

  async function seedCards(onProgress) {
    return EthiomarkDB.seedCards(onProgress);
  }

  /* ────────────────────────────────────────────────────────────
     CARD CATALOGUE
  ──────────────────────────────────────────────────────────── */

  async function getCard(cardNumber) {
    return EthiomarkDB.getCard(cardNumber);
  }

  async function getCardsBatch(cardNumbers) {
    return EthiomarkDB.getCardsBatch(cardNumbers);
  }

  async function getAllCardIds() {
    return EthiomarkDB.getAllCardIds();
  }

  /* ────────────────────────────────────────────────────────────
     GAME STATE
  ──────────────────────────────────────────────────────────── */

  async function getGameState() {
    return EthiomarkDB.dbGetGameState();
  }

  async function saveGameState(state) {
    return EthiomarkDB.dbSaveGameState(state);
  }

  async function clearGameState() {
    return EthiomarkDB.dbClearGameState();
  }

  /* ────────────────────────────────────────────────────────────
     APP SETTINGS
  ──────────────────────────────────────────────────────────── */

  async function getSettings() {
    return EthiomarkDB.dbGetAppSettings();
  }

  async function saveSettings(settings) {
    return EthiomarkDB.dbSaveAppSettings(settings);
  }

  /* ────────────────────────────────────────────────────────────
     GAME HISTORY
  ──────────────────────────────────────────────────────────── */

  async function getHistory() {
    return EthiomarkDB.dbGetHistory();
  }

  /* ── card categories ── */
  async function getCardCategories() {
    return EthiomarkDB.dbGetCardCategories();
  }
  async function getCardIdsByCategory(cat) {
    return EthiomarkDB.dbGetCardIdsByCategory(cat);
  }

  async function addHistory(entry) {
    return EthiomarkDB.dbAddHistory(entry);
  }

  /* Update the most-recent history record for `round` whose status === fromStatus.
     Merges `updates` into it. Falls back to addHistory if no match found. */
  async function updateHistoryByRound(round, fromStatus, updates) {
    console.log("updateHistoryByRound", round, fromStatus, updates);
    
    const found = await EthiomarkDB.dbUpdateHistoryByRound(round, fromStatus, updates);
    return found;
  }

  /* ────────────────────────────────────────────────────────────
     GAME TRANSACTION LOG
     One record per completed game round — mirrors SQL `transaction`.
     Fields: cashier_id, game_round, santim, sew, income,
             bonus, date, result, checked_number, status
  ──────────────────────────────────────────────────────────── */

  async function addTransaction(entry) {
    return EthiomarkDB.dbAddTransaction(entry);
  }

  async function getTransactions() {
    return EthiomarkDB.dbGetTransactions();
  }

  /* ────────────────────────────────────────────────────────────
     WALLET TRANSACTION LOG
     One record per deposit/activation — mirrors SQL `wallettransaction`.
     Fields: event_type, done_by, role, action, amount,
             cashier_id, status, message, timestamp
  ──────────────────────────────────────────────────────────── */

  async function addWalletTransaction(entry) {
    return EthiomarkDB.dbAddWalletTransaction(entry);
  }

  async function getWalletTransactions() {
    return EthiomarkDB.dbGetWalletTransactions();
  }

  /* ────────────────────────────────────────────────────────────
     AUTHENTICATION
  ──────────────────────────────────────────────────────────── */

  async function seedCashiers(list) {
    return EthiomarkDB.seedCashiers(list);
  }

  async function getCashier(id) {
    return EthiomarkDB.getCashier(id);
  }
  /* Merge fields into a cashier record */
  async function updateCashier(id, updates) {
    return EthiomarkDB.dbUpdateCashier(id, updates);
  }

  async function verifyCredentials(id, passwordHash) {
    const cashier = await EthiomarkDB.getCashier(id);
    if (!cashier) return null;
    if (cashier.password_hash !== passwordHash) return null;
    return cashier;
  }

  /* ────────────────────────────────────────────────────────────
     SESSION
  ──────────────────────────────────────────────────────────── */

  function getSession() {
    return localStorage.getItem('em_cashier_id') || null;
  }

  function setSession(id) {
    localStorage.setItem('em_cashier_id', id);
  }

  function clearSession() {
    localStorage.removeItem('em_cashier_id');
  }

  /* ────────────────────────────────────────────────────────────
     LICENSE  /  PACKAGE  SYSTEM
     ─────────────────────────────────────────────────────────────
     Machine ID     : 8-char hex, generated once per install.
     License key    : EM-{MID8}-{AMT}-{SN}-{SIG8}
     Balance        : total_deposited - total_revenue
     Trial lock     : 5 failed activation attempts → locked.
                      Unlock with a code from keygen.html.
  ──────────────────────────────────────────────────────────── */

  /* HMAC secret — same constant must be in keygen.html */
  const _LS = 'EMbingo!X9pQ#2025-EthioMark-Lic3ns3-S3cr3t@Key';

  /* Separate secret for unlock codes */
  const _ULS = 'EMbingo!UNLOCK@2025-EthioMark-Unl0ck-S3cr3t';

  async function _hmac(data) {
    const enc = new TextEncoder();
    const k = await crypto.subtle.importKey(
      'raw', enc.encode(_LS), { name:'HMAC', hash:'SHA-256' }, false, ['sign']
    );
    const s = await crypto.subtle.sign('HMAC', k, enc.encode(data));
    return Array.from(new Uint8Array(s))
      .map(b => b.toString(16).padStart(2,'0')).join('');
  }

  async function _hmacUnlock(data) {
    const enc = new TextEncoder();
    const k = await crypto.subtle.importKey(
      'raw', enc.encode(_ULS), { name:'HMAC', hash:'SHA-256' }, false, ['sign']
    );
    const s = await crypto.subtle.sign('HMAC', k, enc.encode(data));
    return Array.from(new Uint8Array(s))
      .map(b => b.toString(16).padStart(2,'0')).join('');
  }

  /** Get this machine's unique 8-char hex ID (creates on first run). */
  async function getMachineId() {
    const lic = await EthiomarkDB.dbGetLicense();

    console.log("get license", lic);
    
    if (lic && lic.machine_id) return lic.machine_id;
    const uuid = crypto.randomUUID();
    const buf  = await crypto.subtle.digest('SHA-256', new TextEncoder().encode(uuid));
    const mid  = Array.from(new Uint8Array(buf))
      .map(b => b.toString(16).padStart(2,'0')).join('')
      .substring(0, 8).toUpperCase();

    // get main machin id
    const res = await fetch('logic/machine.php');
    const data = await res.json(); // its dynamic
    const static_machine_id=data.machine_id?data.machine_id: "";

    await EthiomarkDB.dbSaveLicense({
      machine_id: mid, 
      packages: [], 
      total_deposited: 0, 
      total_revenue: 0,
      activation_attempts: 0, 
      activation_locked: false, activation_locked_at: null,
      unlock_nonce: 0,
      static_machine_id: static_machine_id,
    });
    return mid;
  }

  /** Full license record with defaults. */
  async function getLicenseInfo() {
    const lic = await EthiomarkDB.dbGetLicense();
    return lic || {
      machine_id: null, 
      packages: [], 
      total_deposited: 0, 
      total_revenue: 0,
      activation_attempts: 0, 
      activation_locked: false, 
      activation_locked_at: null,
      unlock_nonce: 0,
      static_machine_id: '',
    };
  }
  

  /** Balance breakdown (all amounts in ብር). */
  async function getBalance() {
    const lic = await getLicenseInfo();
    const deposited = lic.total_deposited || 0;
    const used      = lic.total_revenue   || 0;
    return { deposited, used, available: deposited - used };
  }

  /** True when available balance > 0. */
  async function isLicensed() {
    const b = await getBalance();
    return b.available > 0;
  }

  /**
   * Return current trial-lock state.
   * { locked, attempts, remaining }
   */
  async function getActivationLockStatus() {
    const lic = await getLicenseInfo();
    const attempts = lic.activation_attempts || 0;
    return {
      locked    : lic.activation_locked || false,
      attempts,
      remaining : Math.max(0, 5 - attempts),
    };
  }

  /**
   * Validate and activate a license key.
   * Format: EM-{MID8}-{AMT}-{SN}-{SIG8}  (compact, all uppercase)
   * - Tracks failed attempts; locks after 5 failures.
   * - On success: resets attempt counter and logs wallet transaction.
   * - Returns { amount, new_balance } on success, throws on failure.
   */
  async function activatePackage(keyStr) {
    keyStr = (keyStr || '').replace(/\s/g,'').toUpperCase();

    /* ── pre-check: activation locked? ── */
    const licCheck = await getLicenseInfo();
    if (licCheck.activation_locked) {
      throw new Error('🔒 Activation is locked after too many failed attempts. Enter your unlock code to continue.');
    }

    /* ── inner validation (throws on any error) ── */
    async function _validate() {
      if (!keyStr.startsWith('EM-'))
        throw new Error('Invalid key — must start with EM-');
      const parts = keyStr.split('-');
      if (parts.length !== 5)
        throw new Error('Invalid key format — expected EM-MACHINEID-AMOUNT-SERIAL-CODE');
      const [, mid, amtStr, sn, sigIn] = parts;
      if (mid.length !== 8)
        throw new Error('Machine ID in key must be 8 characters');
      const amt = Number(amtStr);
      if (!amt || isNaN(amt) || amt <= 0)
        throw new Error('Invalid amount in key');

      const myMid = await getMachineId();
      if (mid !== myMid) {
        const fmt = s => s.slice(0,4) + '-' + s.slice(4);
        throw new Error('Key is for machine ' + fmt(mid) + ' — yours is ' + fmt(myMid));
      }

      const expectedSig = (await _hmac([mid, sn, amt].join('|'))).substring(0,8).toUpperCase();
      if (sigIn !== expectedSig)
        throw new Error('Key signature is invalid — key may be typed incorrectly');

      return { mid, sn, amt };
    }

    /* ── run validation ── */
    let validated;
    try {
      validated = await _validate();
    } catch (err) {
      /* duplicate serial check should NOT consume an attempt */
      const lic = await getLicenseInfo();
      const isDupe = err.message.includes('already been activated');

      if (!isDupe) {
        /* count the failed attempt */
        const attempts = (lic.activation_attempts || 0) + 1;
        const locked   = attempts >= 5;
        await EthiomarkDB.dbSaveLicense({
          ...lic,
          activation_attempts   : attempts,
          activation_locked     : locked,
          activation_locked_at  : locked ? new Date().toISOString() : (lic.activation_locked_at || null),
        });

        /* log failed wallet transaction */
        await EthiomarkDB.dbAddWalletTransaction({
          event_type : 'transaction',
          timestamp  : new Date().toISOString(),
          done_by    : getSession() || 'unknown',
          role       : 'Cashier',
          action     : 'deposit_to_cashier',
          amount     : 0,
          cashier_id : getSession() || '',
          partner_id : '',
          status     : 'failure',
          message    : 'Attempt ' + attempts + ': ' + err.message,
        }).catch(() => {});

        if (locked) {
          throw new Error('🔒 Activation locked after ' + attempts + ' failed attempts. Contact your admin for an unlock code.');
        }
        const left = 5 - attempts;
        throw new Error(err.message + ' — ' + left + ' attempt' + (left === 1 ? '' : 's') + ' remaining before lockout');
      }

      /* duplicate — throw as-is, no penalty */
      throw err;
    }

    /* ── activation succeeded ── */
    const { sn, amt } = validated;

    /* re-read license fresh before writing */
    const lic = await getLicenseInfo();

    /* reject duplicates (race-condition safety) */
    if ((lic.packages || []).some(p => p.sn === sn))
      throw new Error('Serial ' + sn + ' has already been activated on this machine');

    const packages        = [...(lic.packages || []), { sn, amount: amt, activated_at: new Date().toISOString() }];
    const total_deposited = (lic.total_deposited || 0) + amt;

    // we must save the new package checkpoint after checking the machin 
    // =================================================================
    // =================================================================
    // =================================================================
    const ok = await checkLicenseSecurity();

    if (!ok) {
      console.error("❌ SYSTEM BLOCKED");
      return;
    }
    console.log("✅ SAFE BEFORE DEPOSIT TO CONTINUE");
    // =================================================================
    // =================================================================
    // =================================================================
    

    await EthiomarkDB.dbSaveLicense({
      ...lic,
      packages,
      total_deposited,
      activation_attempts  : 0,
      activation_locked    : false,
      activation_locked_at : null,
    });


    // we must save the new package checkpoint after checking the machin 
    // =================================================================
    // =================================================================
    // =================================================================
    const update_new_checkpoint = await updateCheckPoint();

    if (!update_new_checkpoint) {
      console.error("❌ SYSTEM BLOCKED TO UPDATE CHECKPOINT AFTER DEPOSIT");
      return;
    }
    console.log("✅ SAFE AFTER DEPOSIT TO CONTINUE");
    // =================================================================
    // =================================================================
    // =================================================================
    

    /* log successful wallet transaction */
    await EthiomarkDB.dbAddWalletTransaction({
      event_type : 'transaction',
      timestamp  : new Date().toISOString(),
      done_by    : getSession() || 'cashier',
      role       : 'Cashier',
      action     : 'deposit_to_cashier',
      amount     : amt,
      cashier_id : getSession() || '',
      partner_id : '',
      status     : 'success',
      message    : 'Package SN:' + sn + ' activated — ' + amt.toLocaleString() + ' ብር added',
    }).catch(() => {});

    return { amount: amt, new_balance: total_deposited - (lic.total_revenue || 0) };
  }

  /**
   * Verify a one-time unlock code and reset the trial lock.
   * Unlock code = first 8 chars of HMAC-SHA256(machineId + ":" + nonce, _ULS), uppercase.
   * On success: clears the lock AND increments unlock_nonce so the same code never works again.
   * Throws if the code is wrong.
   */
  async function unlockActivation(code) {
    code = (code || '').replace(/\s/g,'').toUpperCase();
    const mid   = await getMachineId();
    const lic   = await getLicenseInfo();
    const nonce = lic.unlock_nonce ?? 0;

    const expected = (await _hmacUnlock(mid + ':' + nonce)).substring(0, 8).toUpperCase();
    if (code !== expected)
      throw new Error('Invalid unlock code. Please contact your administrator.');

    await EthiomarkDB.dbSaveLicense({
      ...lic,
      activation_attempts  : 0,
      activation_locked    : false,
      activation_locked_at : null,
      unlock_nonce         : nonce + 1,
    });

    /* log unlock wallet transaction */
    await EthiomarkDB.dbAddWalletTransaction({
      event_type : 'transaction',
      timestamp  : new Date().toISOString(),
      done_by    : getSession() || 'admin',
      role       : 'Admin',
      action     : 'deposit_to_cashier',
      amount     : 0,
      cashier_id : getSession() || '',
      partner_id : '',
      status     : 'success',
      message    : 'Activation lock cleared with one-time unlock code (use #' + nonce + ')',
    }).catch(() => {});
  }

  /**
   * Admin helper: compute the one-time unlock code for a given machine ID + nonce.
   * Used in keygen.html — format: XXXXXXXX (8 hex chars, uppercase).
   * nonce must match the machine's current unlock_nonce value.
   */
  async function generateUnlockCode(machineId, nonce) {
    machineId = machineId.replace(/-/g,'').trim().toUpperCase();
    nonce     = parseInt(nonce, 10) || 0;
    return (await _hmacUnlock(machineId + ':' + nonce)).substring(0, 8).toUpperCase();
  }

  /**
   * Return the current unlock nonce for display in the UI.
   * The cashier shares this number with the admin when requesting an unlock code.
   */
  async function getUnlockNonce() {
    const lic = await getLicenseInfo();
    return lic.unlock_nonce ?? 0;
  }

  /** Record collected revenue (called when a game finishes). */
  async function addRevenue(amount) {
    const lic = await getLicenseInfo();
    // we must save the new package checkpoint after TRANSACTION PERFORMED
    // =================================================================
      const update_new_checkpoint = await updateCheckPoint(lic.total_deposited, (lic.total_revenue || 0) + Number(amount));

      if (!update_new_checkpoint) {
        console.error("❌ SYSTEM BLOCKED TO UPDATE CHECKPOINT AFTER BET PLACED");
        return;
      }
      console.log("✅ SAFE AFTER BET PLACED TO CONTINUE");
      // =================================================================
    await EthiomarkDB.dbSaveLicense({ ...lic, total_revenue: (lic.total_revenue || 0) + Number(amount) });

  }

  /**
   * Admin-only: generate a compact license key.
   * Format: EM-{MID8}-{AMT}-{SN}-{SIG8}
   */
  async function generateLicenseKey(machineId, serial, amount) {
    machineId = machineId.replace(/-/g,'').trim().toUpperCase();
    serial    = String(serial).trim();
    amount    = Number(amount);
    const sig = (await _hmac([machineId, serial, amount].join('|'))).substring(0,8).toUpperCase();
    return 'EM-' + machineId + '-' + amount + '-' + serial + '-' + sig;
  }

  /* ────────────────────────────────────────────────────────────
     DAILY ROUND RESET
  ──────────────────────────────────────────────────────────── */

  async function checkAndResetDailyRound() {
    const today = new Date().toISOString().split('T')[0];
    const gs    = await getGameState();
    const last  = gs ? gs.last_active_date : null;

    if (last && last !== today) {
      await saveGameState({
        round           : 1,
        cards           : [],
        pattern         : 1,
        price           : 0,
        sound           : gs.sound       || 1,
        speed_range     : gs.speed_range || 3,
        randomstring    : '',
        last_active_date: today,
      });
      return true;
    }

    if (!last) {
      await saveGameState({ ...(gs || {}), last_active_date: today });
    }
    return false;
  }

  async function stampActiveDate() {
    const today = new Date().toISOString().split('T')[0];
    const gs    = await getGameState();
    if (gs && gs.last_active_date !== today) {
      await saveGameState({ ...gs, last_active_date: today });
    }
  }







  // =====================================================================
  // =====================================================================
  // =====================================================================
  // =====================================================================
  // =====================================================================
  
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

      modal.style.display = "flex";

      const yesBtn = document.getElementById("resetYes");
      const noBtn  = document.getElementById("resetNo");

      // reset message each time
      msg.innerHTML = `
        System data is inconsistent.<br>
        This may be due to copying or corruption.<br><br>
        Do you want to reset data and continue?
      `;

      yesBtn.onclick = async () => {
        console.warn("⚠ Fixing system (syncing values)...");

        const res = await fetch('logic/machine.php');
        const { static_machine_id } = await res.json();

        const lic = await getLicense();

        if (!lic) {
          modal.style.display = "none";
          resolve(false);
          return;
        }

        // ✅ FIX instead of delete
        const fixedLicense = {
          ...lic,
          total_revenue: lic.total_deposited
        };

        await EthiomarkDB.dbSaveLicense(fixedLicense);

        // ✅ update checkpoint correctly
        await fetch('logic/machine.php?action=saveCheckpoint', {
          method: 'POST',
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            total_deposited: fixedLicense.total_deposited,
            total_revenue: fixedLicense.total_revenue,
            static_machine_id
          })
        });

        modal.style.display = "none";
        resolve(true);
      };

      noBtn.onclick = () => {
        // 🚨 show contact info instead of alert
        msg.innerHTML = `
          🚫 Access denied.<br><br>
          Please contact your system provider:<br><br>
          📞 <a href="tel:0918101037" style="color:#00c6ff; text-decoration:none; font-weight:700;">
          0918101037
          </a><br><br>
          Or click YES to continue self maintenance.
        `;

        yesBtn.disabled = true;
        noBtn.disabled = true;

        resolve(false);
      };

    });
  }

  // ==== UPDATE CHECKPOINT AFTER CHECKING ====
  async function updateCheckPoint(total_deposited, total_revenue) {
    const res = await fetch('logic/machine.php');
    const data = await res.json();
    const staticMachineId = data.static_machine_id;

    const license = await getLicense();

    if (!license) {
      console.error("❌ No license found");
      return false;
    }

    // ✅ only override if value is provided
    const finalDeposited =
      total_deposited !== undefined && total_deposited !== null
        ? total_deposited
        : license.total_deposited;

    const finalRevenue =
      total_revenue !== undefined && total_revenue !== null
        ? total_revenue
        : license.total_revenue;

    await fetch('logic/machine.php?action=saveCheckpoint', {
      method: 'POST',
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        total_deposited: finalDeposited,
        total_revenue: finalRevenue,
        static_machine_id: staticMachineId
      })
    });

    console.log("✅ checkpoint updated", {
      finalDeposited,
      finalRevenue,
      staticMachineId
    });

    return true;
  }
  // async function updateCheckPoint(total_deposited = 0, total_revenue = 0)
  // {
  //     const getstaticmachinid = await fetch('logic/machine.php');
  //     const getstaticmachiniddata = await getstaticmachinid.json();
  //     const staticMachineId = getstaticmachiniddata.static_machine_id;  // get machine id from php its live checked

  //     const license = await getLicense();  // get license from indexeddb its from indexeddb

  //     const total_deposited_1 = total_deposited ? total_deposited >0 : license.total_deposited
  //     const total_revenue_1 = total_revenue ? total_revenue >0 : license.total_revenue
  //     // ✅ UPDATE CHECKPOINT (important)
  //       data={
  //           total_deposited: total_deposited_1,
  //           total_revenue: total_revenue_1,
  //           static_machine_id: staticMachineId,
  //       };
  //       await fetch('logic/machine.php?action=saveCheckpoint', {
  //         method: 'POST',
  //         body: JSON.stringify(data)
  //       });

  //       console.log(`✅ Update checkpoint 
  //       total_deposited: ${total_deposited_1}
  //       total_revenue: ${total_revenue_1}
  //       machine: ${staticMachineId}`);

  //       return true;
  // }

  // =====================================================================
  // =====================================================================
  // =====================================================================
  // =====================================================================
  // =====================================================================



  /* ── public interface ── */
  return {
    init,
    seedCards,
    getCard, getCardsBatch, getAllCardIds,
    getGameState, saveGameState, clearGameState,
    getSettings, saveSettings,
    getCardCategories, getCardIdsByCategory,
    getHistory, addHistory, updateHistoryByRound,
    addTransaction, getTransactions,
    addWalletTransaction, getWalletTransactions,
    seedCashiers, getCashier, updateCashier, verifyCredentials,
    getSession, setSession, clearSession,
    
    /* license */
    getMachineId, getLicenseInfo, getBalance,
    isLicensed, activatePackage, addRevenue,
    generateLicenseKey,
    getActivationLockStatus, unlockActivation, generateUnlockCode, getUnlockNonce,

    /* daily reset */
    checkAndResetDailyRound, stampActiveDate,

    // check sequirty 
    getLicense, checkLicenseSecurity, showSecurityResetModal, updateCheckPoint, 

  };

})();

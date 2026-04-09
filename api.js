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

  async function addHistory(entry) {
    return EthiomarkDB.dbAddHistory(entry);
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
    if (lic && lic.machine_id) return lic.machine_id;
    const uuid = crypto.randomUUID();
    const buf  = await crypto.subtle.digest('SHA-256', new TextEncoder().encode(uuid));
    const mid  = Array.from(new Uint8Array(buf))
      .map(b => b.toString(16).padStart(2,'0')).join('')
      .substring(0, 8).toUpperCase();
    await EthiomarkDB.dbSaveLicense({
      machine_id: mid, packages: [], total_deposited: 0, total_revenue: 0,
      activation_attempts: 0, activation_locked: false, activation_locked_at: null,
      unlock_nonce: 0,
    });
    return mid;
  }

  /** Full license record with defaults. */
  async function getLicenseInfo() {
    const lic = await EthiomarkDB.dbGetLicense();
    return lic || {
      machine_id: null, packages: [], total_deposited: 0, total_revenue: 0,
      activation_attempts: 0, activation_locked: false, activation_locked_at: null,
      unlock_nonce: 0,
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

    await EthiomarkDB.dbSaveLicense({
      ...lic,
      packages,
      total_deposited,
      activation_attempts  : 0,
      activation_locked    : false,
      activation_locked_at : null,
    });

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

  /* ── public interface ── */
  return {
    init,
    seedCards,
    getCard, getCardsBatch, getAllCardIds,
    getGameState, saveGameState, clearGameState,
    getSettings, saveSettings,
    getHistory, addHistory,
    addTransaction, getTransactions,
    addWalletTransaction, getWalletTransactions,
    seedCashiers, getCashier, verifyCredentials,
    getSession, setSession, clearSession,
    /* license */
    getMachineId, getLicenseInfo, getBalance,
    isLicensed, activatePackage, addRevenue,
    generateLicenseKey,
    getActivationLockStatus, unlockActivation, generateUnlockCode, getUnlockNonce,
    /* daily reset */
    checkAndResetDailyRound, stampActiveDate,
  };

})();

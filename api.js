/*  ═══════════════════════════════════════════════════════════════
    api.js  —  Ethiomark Bingo  |  Service / API abstraction layer
    ───────────────────────────────────────────────────────────────
    The frontend (index.html, reg_new_game.html, report.html,
    login.html) talks ONLY to window.API.  Never to db.js directly.

    CURRENT DRIVER: IndexedDB  (db.js)
    TO SWITCH BACKEND: replace the body of each function below
    with fetch() calls to your PHP / Node.js REST endpoint.
    The HTML/JS frontend requires zero changes.

    Example swap (game state endpoint):
      async function getGameState() {
        const r = await fetch('/api/game-state');
        return r.ok ? r.json() : null;
      }
  ═══════════════════════════════════════════════════════════════ */

window.API = (() => {

  /* ────────────────────────────────────────────────────────────
     INITIALISATION
     Call once on page load to open the DB and seed initial data.
  ──────────────────────────────────────────────────────────── */

  /**
   * Open the database.
   * When using a REST backend, replace with a connectivity check /
   * fetch of an auth token, or leave as a no-op.
   */
  async function init() {
    return EthiomarkDB.openDB();
  }

  /**
   * Seed the local bingo card catalogue.
   * Progress callback receives 0-100 percent values.
   * (REST equivalent: cards live on the server, skip seeding.)
   */
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
     AUTHENTICATION
     verifyCredentials: returns the cashier object on success,
     or null on wrong ID / wrong password.
  ──────────────────────────────────────────────────────────── */

  async function seedCashiers(list) {
    return EthiomarkDB.seedCashiers(list);
  }

  async function getCashier(id) {
    return EthiomarkDB.getCashier(id);
  }

  /**
   * Verify login credentials.
   * @param {string} id           — cashier ID (e.g. "@temp1")
   * @param {string} passwordHash — MD5 hash of the entered password
   * @returns {object|null}       — cashier record, or null on failure
   */
  async function verifyCredentials(id, passwordHash) {
    const cashier = await EthiomarkDB.getCashier(id);
    if (!cashier) return null;
    if (cashier.password_hash !== passwordHash) return null;
    return cashier;
  }

  /* ────────────────────────────────────────────────────────────
     SESSION
     Currently stored in localStorage.
     REST backend equivalent: use an HttpOnly cookie or JWT header.
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
     Machine ID  : 8-char hex, generated once per installation,
                   stored in IndexedDB. Survives page reloads.
     License key : "EM-{base64(JSON payload)}"
                   Payload: { mid, sn, amt, iat, sig }
                   sig = HMAC-SHA256(mid|sn|amt|iat, _LS)
     Balance     : total_deposited - total_revenue
     Lock        : balance ≤ 0  →  game start is blocked.
  ──────────────────────────────────────────────────────────── */

  /* shared HMAC secret — same constant must be in keygen.html */
  const _LS = 'EMbingo!X9pQ#2025-EthioMark-Lic3ns3-S3cr3t@Key';

  async function _hmac(data) {
    const enc = new TextEncoder();
    const k = await crypto.subtle.importKey(
      'raw', enc.encode(_LS), { name:'HMAC', hash:'SHA-256' }, false, ['sign']
    );
    const s = await crypto.subtle.sign('HMAC', k, enc.encode(data));
    return Array.from(new Uint8Array(s))
      .map(b => b.toString(16).padStart(2,'0')).join('');
  }

  /** Get the machine's unique ID (create once, store in IDB). */
  async function getMachineId() {
    const lic = await EthiomarkDB.dbGetLicense();
    if (lic && lic.machine_id) return lic.machine_id;
    /* first run — generate a random permanent ID */
    const uuid = crypto.randomUUID();
    const buf  = await crypto.subtle.digest('SHA-256', new TextEncoder().encode(uuid));
    const mid  = Array.from(new Uint8Array(buf))
      .map(b => b.toString(16).padStart(2,'0')).join('')
      .substring(0, 8).toUpperCase();
    await EthiomarkDB.dbSaveLicense({
      machine_id: mid, packages: [], total_deposited: 0, total_revenue: 0
    });
    return mid;
  }

  /** Full license record. */
  async function getLicenseInfo() {
    const lic = await EthiomarkDB.dbGetLicense();
    return lic || { machine_id: null, packages: [], total_deposited: 0, total_revenue: 0 };
  }

  /** Current balance breakdown (all amounts in ብር). */
  async function getBalance() {
    const lic = await getLicenseInfo();
    const deposited  = lic.total_deposited || 0;
    const used       = lic.total_revenue   || 0;
    const available  = deposited - used;
    return { deposited, used, available };
  }

  /** True when available balance > 0. */
  async function isLicensed() {
    const b = await getBalance();
    return b.available > 0;
  }

  /**
   * Validate and activate a license key.
   * New compact format: EM-{MID8}-{AMT}-{SN}-{SIG8}  (all uppercase)
   * Returns { amount, new_balance } on success, throws on failure.
   */
  async function activatePackage(keyStr) {
    keyStr = (keyStr || '').replace(/\s/g,'').toUpperCase();
    if (!keyStr.startsWith('EM-')) throw new Error('Invalid key — must start with EM-');
    const parts = keyStr.split('-');
    /* expected 5 parts: ['EM', MID8, AMT, SN, SIG8] */
    if (parts.length !== 5) throw new Error('Invalid key format — expected EM-MACHINEID-AMOUNT-SERIAL-CODE');
    const [, mid, amtStr, sn, sigIn] = parts;
    if (mid.length !== 8) throw new Error('Machine ID in key must be 8 characters');
    const amt = Number(amtStr);
    if (!amt || isNaN(amt) || amt <= 0) throw new Error('Invalid amount in key');
    /* machine check */
    const myMid = await getMachineId();
    if (mid !== myMid) {
      const fmt = s => s.slice(0,4)+'-'+s.slice(4);
      throw new Error('Key is for machine ' + fmt(mid) + ' — yours is ' + fmt(myMid));
    }
    /* signature check — HMAC of "MID|SN|AMT", first 8 chars uppercase */
    const expectedSig = (await _hmac([mid, sn, amt].join('|'))).substring(0,8).toUpperCase();
    if (sigIn !== expectedSig) throw new Error('Key signature is invalid — key may be forged or typed incorrectly');
    /* duplicate check */
    const lic = await getLicenseInfo();
    if ((lic.packages || []).some(p => p.sn === sn))
      throw new Error('Serial ' + sn + ' has already been activated on this machine');
    /* activate */
    const packages       = [...(lic.packages || []), { sn, amount: amt, activated_at: new Date().toISOString() }];
    const total_deposited = (lic.total_deposited || 0) + amt;
    await EthiomarkDB.dbSaveLicense({ ...lic, packages, total_deposited });
    return { amount: amt, new_balance: total_deposited - (lic.total_revenue || 0) };
  }

  /**
   * Record collected revenue (called when a game finishes).
   * @param {number} amount  ብር collected this game
   */
  async function addRevenue(amount) {
    const lic = await getLicenseInfo();
    await EthiomarkDB.dbSaveLicense({ ...lic, total_revenue: (lic.total_revenue || 0) + Number(amount) });
  }

  /**
   * Admin-only: generate a short compact license key for a given machine.
   * Format: EM-{MID8}-{AMT}-{SN}-{SIG8}  (all uppercase, ~28 chars)
   * Used in keygen.html — never call this from the cashier UI.
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
     Rounds start at 1 every calendar day.
     Call once on page load — history is NEVER cleared here;
     only the active round counter is reset.
  ──────────────────────────────────────────────────────────── */

  /**
   * If the stored last_active_date is before today, reset round → 1
   * and clear any in-progress game state (cards, randomstring).
   * History rows are permanent and are never deleted.
   * Returns true if a reset happened.
   */
  async function checkAndResetDailyRound() {
    const today = new Date().toISOString().split('T')[0];   /* "YYYY-MM-DD" */
    const gs    = await getGameState();
    const last  = gs ? gs.last_active_date : null;

    if (last && last !== today) {
      /* New day — reset round to 1, wipe current game */
      await saveGameState({
        round           : 1,
        cards           : [],
        pattern         : 1,
        price           : 0,
        sound           : gs.sound        || 1,
        speed_range     : gs.speed_range  || 3,
        randomstring    : '',
        last_active_date: today,
      });
      return true;
    }

    if (!last) {
      /* First boot ever — just stamp today, leave everything else */
      await saveGameState({ ...(gs || {}), last_active_date: today });
    }
    return false;
  }

  /** Convenience: write today's date into game_state after any save. */
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
    seedCashiers, getCashier, verifyCredentials,
    getSession, setSession, clearSession,
    /* license */
    getMachineId, getLicenseInfo, getBalance,
    isLicensed, activatePackage, addRevenue,
    generateLicenseKey,
    /* daily reset */
    checkAndResetDailyRound, stampActiveDate,
  };

})();

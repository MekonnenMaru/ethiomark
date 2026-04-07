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
   * Returns { amount, new_balance } on success, throws on failure.
   */
  async function activatePackage(keyStr) {
    keyStr = (keyStr || '').trim();
    if (!keyStr.startsWith('EM-')) throw new Error('Invalid key format (must start with EM-)');
    let payload;
    try { payload = JSON.parse(atob(keyStr.slice(3))); } catch(e) { throw new Error('Key is corrupted or invalid'); }
    const { mid, sn, amt, iat, sig } = payload;
    if (!mid || !sn || !amt || !iat || !sig) throw new Error('Key is incomplete');
    /* machine check */
    const myMid = await getMachineId();
    if (mid.toUpperCase() !== myMid.toUpperCase())
      throw new Error('This key belongs to machine ' + mid + ' but yours is ' + myMid);
    /* signature check */
    const expected = await _hmac([mid, sn, amt, iat].join('|'));
    if (sig !== expected) throw new Error('Key signature is invalid — key may be forged');
    /* duplicate check */
    const lic = await getLicenseInfo();
    if ((lic.packages || []).some(p => p.sn === sn))
      throw new Error('Serial ' + sn + ' has already been activated on this machine');
    /* activate */
    const packages       = [...(lic.packages || []), { sn, amount: amt, activated_at: new Date().toISOString() }];
    const total_deposited = (lic.total_deposited || 0) + Number(amt);
    await EthiomarkDB.dbSaveLicense({ ...lic, packages, total_deposited });
    return { amount: Number(amt), new_balance: total_deposited - (lic.total_revenue || 0) };
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
   * Admin-only: generate a license key for a given machine.
   * Used in keygen.html — never call this from the cashier UI.
   */
  async function generateLicenseKey(machineId, serial, amount) {
    machineId = machineId.trim().toUpperCase();
    serial    = serial.trim();
    amount    = Number(amount);
    const iat = new Date().toISOString().split('T')[0];
    const sig = await _hmac([machineId, serial, amount, iat].join('|'));
    const payload = { mid: machineId, sn: serial, amt: amount, iat, sig };
    return 'EM-' + btoa(JSON.stringify(payload));
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
  };

})();

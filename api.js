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
  };

})();

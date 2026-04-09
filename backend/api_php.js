/*  ═══════════════════════════════════════════════════════════════
    backend/api_php.js  —  Ethiomark Bingo  |  PHP/MySQL driver
    ───────────────────────────────────────────────────────────────
    Drop-in replacement for api.js when using the PHP/MySQL backend.

    TO SWITCH TO PHP/MySQL MODE:
      In each HTML file change ONE script tag:
        <script src="db.js"></script>        ← remove this line
        <script src="api.js"></script>       ← change to ↓
        <script src="backend/api_php.js"></script>

    Files to update:
      index.html, login.html, reg_new_game.html, report.html

    (keygen.html has its own inline HMAC — no api.js needed there)

    TO SWITCH BACK TO IndexedDB:
      Reverse the change above (restore db.js + api.js lines).

    REQUIREMENTS:
      • Apache + MySQL running in XAMPP
      • All project files inside htdocs/ethiomark/
      • backend/api.php in the same project root
      • cards_data.js still loaded before this file (cards stay
        bundled in JS — no MySQL roundtrip for card lookups)
  ═══════════════════════════════════════════════════════════════ */

window.API = (() => {

  /* ── HTTP helper ────────────────────────────────────────────── */
  /* All calls go to backend/api.php — path is relative to the    */
  /* HTML page (document root), not to this JS file's location.   */
  const PHP = 'backend/api.php';

  async function _call(action, body) {
    try {
      const opts = (body !== undefined)
        ? {
            method : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body   : JSON.stringify(body),
          }
        : { method: 'GET' };

      const r    = await fetch(PHP + '?action=' + action, opts);
      const text = await r.text();

      let json;
      try { json = JSON.parse(text); }
      catch {
        throw new Error('api.php returned non-JSON for "' + action + '": ' + text.slice(0, 200));
      }

      if (json && typeof json === 'object' && json.error) throw new Error(json.error);
      return json;
    } catch (err) {
      console.error('[api_php] ' + action + ' failed:', err);
      throw err;
    }
  }

  /* ────────────────────────────────────────────────────────────
     INITIALISATION
     Connects to MySQL and auto-creates all tables on first run.
  ──────────────────────────────────────────────────────────── */
  async function init() {
    return _call('init');
  }

  /* ────────────────────────────────────────────────────────────
     CARD CATALOGUE  (bundled in cards_data.js — no DB needed)
  ──────────────────────────────────────────────────────────── */
  async function seedCards(onProgress) {
    if (typeof onProgress === 'function') onProgress(100);
    return true;
  }

  async function getCard(cardNumber) {
    if (typeof BINGO_CARDS === 'undefined') return null;
    const row = BINGO_CARDS.find(c => c[0] === Number(cardNumber));
    if (!row) return null;
    return { card_number: row[0], B: row[1], I: row[2], N: row[3], G: row[4], O: row[5] };
  }

  async function getCardsBatch(cardNumbers) {
    const result = {};
    for (const n of cardNumbers) result[n] = await getCard(n);
    return result;
  }

  async function getAllCardIds() {
    if (typeof BINGO_CARDS === 'undefined') return [];
    return BINGO_CARDS.map(c => c[0]);
  }

  /* ────────────────────────────────────────────────────────────
     GAME STATE
  ──────────────────────────────────────────────────────────── */
  async function getGameState()      { return _call('getGameState'); }
  async function saveGameState(s)    { return _call('saveGameState',  { state: s }); }
  async function clearGameState()    { return _call('clearGameState'); }

  /* ────────────────────────────────────────────────────────────
     APP SETTINGS
  ──────────────────────────────────────────────────────────── */
  async function getSettings()       { return _call('getSettings'); }
  async function saveSettings(s)     { return _call('saveSettings',  { settings: s }); }

  /* ────────────────────────────────────────────────────────────
     GAME HISTORY
  ──────────────────────────────────────────────────────────── */
  async function getHistory()        { return _call('getHistory'); }
  async function addHistory(entry)   { return _call('addHistory',   { entry }); }

  /* ────────────────────────────────────────────────────────────
     AUTHENTICATION
     seedCashiers: called on login page load — inserts default
     cashier accounts if they don't exist yet (INSERT IGNORE).
  ──────────────────────────────────────────────────────────── */
  async function seedCashiers(list)  { return _call('seedCashiers', { list }); }
  async function getCashier(id)      { return _call('getCashier',   { id }); }

  async function verifyCredentials(id, passwordHash) {
    return _call('verifyCredentials', { id, password_hash: passwordHash });
  }

  /* ────────────────────────────────────────────────────────────
     SESSION  (localStorage — same behaviour as IDB mode)
     Stores the logged-in cashier ID client-side.
     PHP doesn't need a server session for single-machine XAMPP use.
  ──────────────────────────────────────────────────────────── */
  function getSession()   { return localStorage.getItem('em_cashier_id') || null; }
  function setSession(id) { localStorage.setItem('em_cashier_id', id); }
  function clearSession() { localStorage.removeItem('em_cashier_id'); }

  /* ────────────────────────────────────────────────────────────
     LICENSE / PACKAGE SYSTEM
     • Key validation + HMAC run client-side (no server secret exposed)
     • Activation and balance tracking stored in MySQL via api.php
  ──────────────────────────────────────────────────────────── */
  const _LS = 'EMbingo!X9pQ#2025-EthioMark-Lic3ns3-S3cr3t@Key';

  async function _hmac(data) {
    const enc = new TextEncoder();
    const k   = await crypto.subtle.importKey(
      'raw', enc.encode(_LS), { name: 'HMAC', hash: 'SHA-256' }, false, ['sign']
    );
    const s = await crypto.subtle.sign('HMAC', k, enc.encode(data));
    return Array.from(new Uint8Array(s)).map(b => b.toString(16).padStart(2, '0')).join('');
  }

  async function getMachineId() {
    const r = await _call('getMachineId');
    return r.machine_id;
  }

  async function getLicenseInfo() { return _call('getLicenseInfo'); }
  async function getBalance()     { return _call('getBalance'); }

  async function isLicensed() {
    const b = await getBalance();
    return b.available > 0;
  }

  async function activatePackage(keyStr) {
    keyStr = (keyStr || '').replace(/\s/g, '').toUpperCase();
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
      const fmt = s => s.slice(0, 4) + '-' + s.slice(4);
      throw new Error('Key is for machine ' + fmt(mid) + ' — yours is ' + fmt(myMid));
    }

    const expectedSig = (await _hmac([mid, sn, amt].join('|'))).substring(0, 8).toUpperCase();
    if (sigIn !== expectedSig)
      throw new Error('Key signature is invalid — key may be forged or typed incorrectly');

    return _call('activatePackage', { mid, sn, amt });
  }

  async function addRevenue(amount) {
    return _call('addRevenue', { amount: Number(amount) });
  }

  async function generateLicenseKey(machineId, serial, amount) {
    machineId = machineId.replace(/-/g, '').trim().toUpperCase();
    serial    = String(serial).trim();
    amount    = Number(amount);
    const sig = (await _hmac([machineId, serial, amount].join('|'))).substring(0, 8).toUpperCase();
    return 'EM-' + machineId + '-' + amount + '-' + serial + '-' + sig;
  }

  /* ────────────────────────────────────────────────────────────
     DAILY ROUND RESET
  ──────────────────────────────────────────────────────────── */
  async function checkAndResetDailyRound() { return _call('checkAndResetDailyRound'); }
  async function stampActiveDate()         { return _call('stampActiveDate'); }

  /* ── public interface — identical to api.js ── */
  return {
    init,
    seedCards,
    getCard, getCardsBatch, getAllCardIds,
    getGameState, saveGameState, clearGameState,
    getSettings, saveSettings,
    getHistory, addHistory,
    seedCashiers, getCashier, verifyCredentials,
    getSession, setSession, clearSession,
    getMachineId, getLicenseInfo, getBalance,
    isLicensed, activatePackage, addRevenue,
    generateLicenseKey,
    checkAndResetDailyRound, stampActiveDate,
  };

})();

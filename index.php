<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reforger Captain</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Login Screen -->
<div id="login-screen" class="screen">
    <div class="card login-card">
        <h1>Reforger Captain</h1>
        <form id="login-form">
            <input type="password" id="login-pass" placeholder="Enter server password" required autofocus>
            <button type="submit" class="btn btn-primary">Enter</button>
        </form>
        <div id="login-msg" class="msg hidden"></div>
    </div>
</div>

<!-- Main App -->
<div id="app" class="screen hidden">
    <header>
        <h1>Reforger Captain</h1>
        <div id="server-status-badge" class="badge badge-off">OFFLINE</div>
    </header>

    <nav class="tabs">
        <button class="tab active" data-tab="server">Server</button>
        <button class="tab" data-tab="rcon">RCON</button>
        <button class="tab" data-tab="logs">Logs</button>
        <button class="tab" data-tab="settings">Settings</button>
    </nav>

    <!-- Server Tab -->
    <section id="tab-server" class="tab-content active">
        <div class="card">
            <h2>Scenario</h2>
            <div class="form-group">
                <label for="scenario-select">Available Scenarios</label>
                <select id="scenario-select">
                    <option value="">-- Select a scenario --</option>
                </select>
            </div>
            <div class="form-group">
                <label for="scenario-custom">Or enter Scenario ID manually</label>
                <input type="text" id="scenario-custom" placeholder="{ECC61978EDCC2B5A}Missions/23_Campaign.conf">
            </div>
            <p class="hint">Current: <code id="current-scenario">--</code></p>
        </div>

        <div class="card">
            <h2>Mods</h2>
            <div class="mod-grid">
                <div class="mod-column">
                    <h3>Installed Mods</h3>
                    <div id="installed-mods" class="mod-list"></div>
                </div>
                <div class="mod-column">
                    <h3>Add Mod</h3>
                    <div id="available-mods" class="mod-list"></div>
                    <div class="form-group">
                        <label for="mod-custom-id">Custom Workshop ID</label>
                        <div class="input-row">
                            <input type="text" id="mod-custom-id" placeholder="e.g. 2824703552">
                            <button id="add-custom-mod" class="btn btn-small">Add</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <h2>Crossplay</h2>
            <p class="hint">Supported platforms (informational — crossplay requires backend registration).</p>
            <div class="crossplay-options">
                <label class="checkbox-label">
                    <input type="checkbox" id="platform-pc" value="PLATFORM_PC" checked> PC
                </label>
                <label class="checkbox-label">
                    <input type="checkbox" id="platform-xbl" value="PLATFORM_XBL"> Xbox
                </label>
                <label class="checkbox-label">
                    <input type="checkbox" id="platform-psn" value="PLATFORM_PSN"> PlayStation
                </label>
            </div>
        </div>

        <div class="actions">
            <button id="save-btn" class="btn btn-primary">Save & Restart</button>
            <div id="save-msg" class="msg hidden"></div>
        </div>
    </section>

    <!-- RCON Tab -->
    <section id="tab-rcon" class="tab-content">
        <div class="card">
            <h2>Players</h2>
            <div id="players-list" class="players-list">
                <p class="hint">Click "Refresh" to load player list.</p>
            </div>
            <div class="players-actions">
                <button id="refresh-players" class="btn btn-small">Refresh Players</button>
                <div id="player-action-btns" class="hidden">
                    <button id="kick-selected" class="btn btn-small btn-danger">Kick Selected</button>
                    <button id="ban-selected" class="btn btn-small btn-danger">Ban Selected</button>
                </div>
            </div>
        </div>

        <div class="card">
            <h2>RCON Console</h2>
            <div id="rcon-log" class="rcon-log">
                <div class="rcon-welcome">Type a command or use the quick actions below.</div>
            </div>
            <div class="rcon-input-row">
                <input type="text" id="rcon-input" placeholder="Enter RCON command...">
                <button id="rcon-send" class="btn btn-primary">Send</button>
            </div>
        </div>

        <div class="card">
            <h2>Quick Actions</h2>
            <div class="quick-actions">
                <div class="action-group">
                    <h3>Server</h3>
                    <div class="btn-row">
                        <button class="btn btn-warning" onclick="rconQuick('#restart')">Restart Mission</button>
                        <button class="btn btn-danger" onclick="rconQuick('#shutdown')">Shutdown</button>
                    </div>
                </div>
                <div class="action-group">
                    <h3>Bans</h3>
                    <button class="btn btn-small" onclick="rconQuick('#ban list')" style="margin-bottom:8px">Ban List</button>
                    <div class="input-row">
                        <input type="text" id="unban-input" placeholder="Identity ID to unban">
                        <button class="btn btn-small btn-danger" onclick="rconQuick('#ban remove ' + document.getElementById('unban-input').value)">Unban</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Logs Tab -->
    <section id="tab-logs" class="tab-content">
        <div class="card">
            <h2>Server Logs</h2>
            <div class="logs-controls">
                <label for="log-lines">Lines:</label>
                <select id="log-lines">
                    <option value="50">50</option>
                    <option value="100" selected>100</option>
                    <option value="200">200</option>
                    <option value="500">500</option>
                </select>
                <button id="refresh-logs" class="btn btn-small">Refresh</button>
                <label class="auto-refresh-label">
                    <input type="checkbox" id="auto-refresh"> Auto-refresh (10s)
                </label>
            </div>
            <div id="logs-output" class="logs-output">
                <p class="hint">Click "Refresh" to load server logs.</p>
            </div>
        </div>
    </section>

    <!-- Settings Tab -->
    <section id="tab-settings" class="tab-content">
        <div class="card">
            <h2>Authentication</h2>
            <p class="hint">Password is the same as your Arma server's RCON password.</p>
        </div>

        <div class="card">
            <h2>RCON Settings</h2>
            <p class="hint" id="rcon-settings-hint">Read from the server config. Edit <code>docker-compose.yml</code> to change.</p>
            <form id="rcon-settings-form">
                <div class="settings-grid">
                    <div class="form-group">
                        <label for="rcon-host">RCON Host</label>
                        <input type="text" id="rcon-host">
                    </div>
                    <div class="form-group">
                        <label for="rcon-port">RCON Port</label>
                        <input type="number" id="rcon-port">
                    </div>
                </div>
                <div class="form-group">
                    <label for="rcon-pass">RCON Password (leave empty to keep current)</label>
                    <input type="password" id="rcon-pass" placeholder="Leave empty to keep current">
                </div>
                <button type="submit" class="btn btn-primary">Update RCON Connection</button>
            </form>
            <div id="rcon-settings-msg" class="msg hidden"></div>
        </div>

        <div class="card">
            <h2>Authelia</h2>
            <p class="hint">When behind Authelia, the app trusts the <code>Remote-User</code> header automatically.</p>
        </div>

        <div class="card">
            <h2>Session</h2>
            <button id="logout-btn" class="btn btn-danger">Logout</button>
        </div>
    </section>
</div>

<script>
const API = 'api.php';
let appState = { config: {}, adminConfig: {}, running: false, mode: 'full' };

// --- Init ---
document.addEventListener('DOMContentLoaded', async () => {
    const res = await fetch(API + '?action=status');
    const data = await res.json();
    if (data.error === 'unauthorized') {
        showLogin();
    } else if (data.ok) {
        appState = data;
        showApp();
    }
});

function showLogin() {
    document.getElementById('login-screen').classList.remove('hidden');
    document.getElementById('app').classList.add('hidden');
    document.getElementById('login-pass').focus();
}

function showApp() {
    document.getElementById('login-screen').classList.add('hidden');
    document.getElementById('app').classList.remove('hidden');

    // Hide tabs based on mode
    const isRconOnly = appState.mode === 'rcon-only';
    document.querySelector('[data-tab="server"]').classList.toggle('hidden', isRconOnly);
    document.querySelector('[data-tab="logs"]').classList.toggle('hidden', isRconOnly);

    // In RCON-only mode, default to RCON tab
    if (isRconOnly) {
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        document.querySelector('[data-tab="rcon"]').classList.add('active');
        document.getElementById('tab-rcon').classList.add('active');
    }

    renderAll();
    if (isRconOnly) loadRconSettings();
}

// --- Login ---
document.getElementById('login-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const pass = document.getElementById('login-pass').value;
    const msg = document.getElementById('login-msg');

    try {
        const res = await fetch(API + '?action=login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ password: pass })
        });
        const data = await res.json();
        if (data.ok) {
            // Load status after login
            const statusRes = await fetch(API + '?action=status');
            const statusData = await statusRes.json();
            if (statusData.ok) {
                appState = statusData;
                showApp();
            }
        } else {
            msg.textContent = data.error || 'Wrong password';
            msg.className = 'msg msg-error';
            msg.classList.remove('hidden');
            document.getElementById('login-pass').value = '';
            document.getElementById('login-pass').focus();
        }
    } catch (e) {
        msg.textContent = 'Connection failed';
        msg.className = 'msg msg-error';
        msg.classList.remove('hidden');
    }
});

// --- Logout ---
document.getElementById('logout-btn').addEventListener('click', async () => {
    await fetch(API + '?action=logout', { method: 'POST' });
    showLogin();
});

// --- RCON Settings ---
document.getElementById('rcon-settings-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const msg = document.getElementById('rcon-settings-msg');
    const host = document.getElementById('rcon-host').value;
    const port = document.getElementById('rcon-port').value;
    const pass = document.getElementById('rcon-pass').value;

    const body = { host, port: parseInt(port) };
    if (pass) body.password = pass;

    const res = await fetch(API + '?action=update-rcon', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body)
    });
    const data = await res.json();
    msg.textContent = data.message || (data.ok ? 'Updated!' : 'Failed');
    msg.className = 'msg ' + (data.ok ? 'msg-ok' : 'msg-error');
    msg.classList.remove('hidden');
    document.getElementById('rcon-pass').value = '';

    // Update local state
    appState.rconHost = host;
    appState.rconPort = parseInt(port);
});

async function loadRconSettings() {
    try {
        const res = await fetch(API + '?action=rcon-settings');
        const data = await res.json();
        if (data.ok) {
            document.getElementById('rcon-host').value = data.host;
            document.getElementById('rcon-port').value = data.port;
        }
    } catch (e) {}
}

// --- Tabs ---
document.querySelectorAll('.tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        tab.classList.add('active');
        document.getElementById('tab-' + tab.dataset.tab).classList.add('active');
    });
});

// --- Render All ---
async function loadMissions(currentScenario) {
    const sel = document.getElementById('scenario-select');
    sel.innerHTML = '<option value="">Loading...</option>';

    try {
        const res = await fetch(API + '?action=missions');
        const data = await res.json();
        sel.innerHTML = '<option value="">-- Select a scenario --</option>';
        if (data.ok && data.missions) {
            data.missions.forEach(m => {
                const opt = document.createElement('option');
                opt.value = m.scenarioId;
                opt.textContent = m.name;
                if (m.scenarioId === currentScenario) opt.selected = true;
                sel.appendChild(opt);
            });
        }
    } catch (e) {
        sel.innerHTML = '<option value="">Failed to load scenarios</option>';
    }
}

function renderAll() {
    const cfg = appState.config.game || {};
    document.getElementById('current-scenario').textContent = cfg.scenarioId || 'N/A';

    const badge = document.getElementById('server-status-badge');
    badge.textContent = appState.running ? 'ONLINE' : 'OFFLINE';
    badge.className = appState.running ? 'badge badge-online' : 'badge badge-off';

    // Scenarios — load dynamically from server
    loadMissions(cfg.scenarioId);
    document.getElementById('scenario-custom').value = cfg.scenarioId || '';

    // Installed mods
    const installedEl = document.getElementById('installed-mods');
    installedEl.innerHTML = '';
    const installed = cfg.mods || [];
    installed.forEach(m => {
        const modId = m.modId || m;
        const modDef = (appState.adminConfig.mods || []).find(am => am.modId == modId);
        const name = modDef ? modDef.name : modId;
        installedEl.appendChild(createModChip(name, modId, true));
    });

    // Available mods
    const availEl = document.getElementById('available-mods');
    availEl.innerHTML = '';
    (appState.adminConfig.mods || []).forEach(m => {
        const isIn = installed.some(im => (im.modId || im) == m.modId);
        if (!isIn) {
            availEl.appendChild(createModChip(m.name, m.modId, false));
        }
    });

    // Crossplay
    const platforms = cfg.supportedPlatforms || ['PLATFORM_PC'];
    document.getElementById('platform-pc').checked = platforms.includes('PLATFORM_PC');
    document.getElementById('platform-xbl').checked = platforms.includes('PLATFORM_XBL');
    document.getElementById('platform-psn').checked = platforms.includes('PLATFORM_PSN');

    // Settings — load RCON settings
    loadRconSettings();
}

function createModChip(name, modId, isInstalled) {
    const div = document.createElement('div');
    div.className = 'mod-chip' + (isInstalled ? ' installed' : '');
    div.innerHTML = `
        <span class="mod-name" title="${modId}">${name}</span>
        <button class="mod-btn">${isInstalled ? 'x' : '+'}</button>
    `;
    div.querySelector('.mod-btn').addEventListener('click', () => {
        if (isInstalled) removeMod(modId);
        else addMod(modId);
    });
    return div;
}

// --- Mod Management ---
function getInstalledModIds() {
    return (appState.config.game?.mods || []).map(m => m.modId || m);
}

function addMod(modId) {
    const ids = getInstalledModIds();
    ids.push(modId);
    appState.config.game.mods = ids.map(id => ({ modId: id }));
    renderAll();
}

function removeMod(modId) {
    const mods = getInstalledModIds().filter(id => id != modId);
    appState.config.game.mods = mods.map(id => ({ modId: id }));
    renderAll();
}

document.getElementById('add-custom-mod').addEventListener('click', () => {
    const input = document.getElementById('mod-custom-id');
    const id = input.value.trim();
    if (id) { addMod(id); input.value = ''; }
});

// --- Save & Restart ---
document.getElementById('save-btn').addEventListener('click', async () => {
    const msg = document.getElementById('save-msg');
    const btn = document.getElementById('save-btn');
    btn.disabled = true;
    btn.textContent = 'Saving...';

    const scenario = document.getElementById('scenario-custom').value || document.getElementById('scenario-select').value;

    // Collect platforms
    const platforms = [];
    document.querySelectorAll('.crossplay-options input:checked').forEach(cb => {
        platforms.push(cb.value);
    });

    const res = await fetch(API + '?action=save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ scenarioId: scenario, mods: getInstalledModIds(), platforms })
    });
    const data = await res.json();
    msg.textContent = data.message || (data.ok ? 'Done!' : 'Failed');
    msg.className = 'msg ' + (data.ok ? 'msg-ok' : 'msg-error');
    msg.classList.remove('hidden');
    btn.disabled = false;
    btn.textContent = 'Save & Restart';

    setTimeout(async () => {
        const s = await fetch(API + '?action=status');
        appState = await s.json();
        renderAll();
    }, 3000);
});

// --- RCON ---
document.getElementById('rcon-send').addEventListener('click', sendRcon);
document.getElementById('rcon-input').addEventListener('keydown', e => {
    if (e.key === 'Enter') sendRcon();
});

async function sendRcon() {
    const input = document.getElementById('rcon-input');
    const cmd = input.value.trim();
    if (!cmd) return;
    input.value = '';
    rconQuick(cmd);
}

async function rconQuick(cmd) {
    const log = document.getElementById('rcon-log');
    log.innerHTML += `<div class="rcon-cmd">> ${escHtml(cmd)}</div>`;

    try {
        const res = await fetch(API + '?action=rcon', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ command: cmd })
        });
        const data = await res.json();
        if (data.ok) {
            log.innerHTML += `<div class="rcon-output">${escHtml(data.output || '(no output)')}</div>`;
        } else {
            log.innerHTML += `<div class="rcon-error">Error: ${escHtml(data.error)}</div>`;
        }
    } catch (e) {
        log.innerHTML += `<div class="rcon-error">Request failed: ${escHtml(e.message)}</div>`;
    }

    log.scrollTop = log.scrollHeight;
}

// --- Players ---
let selectedPlayer = null;

document.getElementById('refresh-players').addEventListener('click', loadPlayers);
document.getElementById('kick-selected').addEventListener('click', () => {
    if (selectedPlayer) rconQuick('kick ' + selectedPlayer);
});
document.getElementById('ban-selected').addEventListener('click', () => {
    if (selectedPlayer) rconQuick('ban ' + selectedPlayer);
});

async function loadPlayers() {
    const el = document.getElementById('players-list');
    el.innerHTML = '<p class="hint">Loading...</p>';
    selectedPlayer = null;
    document.getElementById('player-action-btns').classList.add('hidden');

    try {
        const res = await fetch(API + '?action=rcon', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ command: 'players' })
        });
        const data = await res.json();
        if (data.ok && data.output) {
            const players = parsePlayers(data.output);
            if (players.length === 0) {
                el.innerHTML = '<p class="hint">No players connected.</p>';
            } else {
                el.innerHTML = '';
                players.forEach(p => {
                    const row = document.createElement('div');
                    row.className = 'player-row';
                    row.innerHTML = `
                        <span class="player-name">${escHtml(p.name)}</span>
                        <span class="player-id">${escHtml(p.id)}</span>
                    `;
                    row.addEventListener('click', () => {
                        document.querySelectorAll('.player-row').forEach(r => r.classList.remove('selected'));
                        row.classList.add('selected');
                        selectedPlayer = p.id || p.name;
                        document.getElementById('player-action-btns').classList.remove('hidden');
                    });
                    el.appendChild(row);
                });
            }
        } else {
            el.innerHTML = '<p class="hint">' + escHtml(data.error || 'No response') + '</p>';
        }
    } catch (e) {
        el.innerHTML = '<p class="hint">Failed: ' + escHtml(e.message) + '</p>';
    }
}

function parsePlayers(output) {
    const players = [];
    const lines = output.split('\n');
    for (const line of lines) {
        // Arma Reforger players output format:
        // "PlayerName" (id=12345, address=x.x.x.x:port)
        // or: #    ID    Name    IP    Ping    ...
        const match = line.match(/"([^"]+)".*?id=(\d+)/i);
        if (match) {
            players.push({ name: match[1], id: match[2] });
            continue;
        }
        // Tab-separated: ID, Name, ...
        const parts = line.split(/\t+/);
        if (parts.length >= 2 && /^\d+$/.test(parts[0].trim())) {
            players.push({ name: parts[1].trim(), id: parts[0].trim() });
        }
    }
    return players;
}

// --- Logs ---
let autoRefreshTimer = null;

document.getElementById('refresh-logs').addEventListener('click', loadLogs);
document.getElementById('auto-refresh').addEventListener('change', (e) => {
    if (e.target.checked) {
        loadLogs();
        autoRefreshTimer = setInterval(loadLogs, 10000);
    } else {
        clearInterval(autoRefreshTimer);
        autoRefreshTimer = null;
    }
});
document.getElementById('log-lines').addEventListener('change', loadLogs);

async function loadLogs() {
    const el = document.getElementById('logs-output');
    const lines = document.getElementById('log-lines').value;

    try {
        const res = await fetch(API + '?action=logs&lines=' + lines);
        const data = await res.json();
        if (data.ok) {
            el.textContent = data.output || '(empty)';
            el.scrollTop = el.scrollHeight;
        } else {
            el.textContent = 'Failed to load logs';
        }
    } catch (e) {
        el.textContent = 'Error: ' + e.message;
    }
}

// --- Helpers ---
function escHtml(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}
</script>

</body>
</html>

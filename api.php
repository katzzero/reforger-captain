<?php
session_start();
header('Content-Type: application/json');

$mode = getenv('MODE') ?: 'full';
$configFile = '/reforger/config/server.json';
$adminConfigFile = '/app/admin-config.json';
$rconHost = getenv('RCON_HOST') ?: 'host.docker.internal';
$rconPort = (int)(getenv('RCON_PORT') ?: 29999);
$serverContainer = getenv('SERVER_CONTAINER') ?: 'arma-reforger';

// --- Helpers ---

function isRconOnly(): bool {
    return ($GLOBALS['mode'] ?? 'full') === 'rcon-only';
}

function getServerConfig(): array {
    if (isRconOnly()) return [];
    if (file_exists('/reforger/config/server.json')) {
        return json_decode(file_get_contents('/reforger/config/server.json'), true) ?: [];
    }
    return [];
}

function getRconPassword(): string {
    // Session override (for RCON-only mode runtime changes)
    if (!empty($_SESSION['rcon_password'])) {
        return $_SESSION['rcon_password'];
    }
    // Env var (always available)
    $envPass = getenv('RCON_PASSWORD');
    if (!empty($envPass)) return $envPass;
    // Server config (full mode only)
    $cfg = getServerConfig();
    return $cfg['rcon']['password'] ?? '';
}

function isAuthenticated(): bool {
    if (isset($_SERVER['HTTP_REMOTE_USER'])) return true;
    return !empty($_SESSION['logged_in']);
}

function requireAuth() {
    if (!isAuthenticated()) {
        http_response_code(401);
        echo json_encode(['error' => 'unauthorized']);
        exit;
    }
}

// --- Routing ---

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

// Login — check password against RCON password
if ($action === 'login' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $pass = $input['password'] ?? '';
    $rconPass = getRconPassword();

    if (!empty($rconPass) && $pass === $rconPass) {
        $_SESSION['logged_in'] = true;
        echo json_encode(['ok' => true]);
    } else {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Wrong password']);
    }
    exit;
}

// Logout
if ($action === 'logout' && $method === 'POST') {
    session_destroy();
    echo json_encode(['ok' => true]);
    exit;
}

// Update RCON settings (runtime)
if ($action === 'update-rcon' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (isset($input['host'])) $GLOBALS['rconHost'] = $input['host'];
    if (isset($input['port'])) $GLOBALS['rconPort'] = (int)$input['port'];
    if (isset($input['password'])) {
        // Store in session for this session only
        $_SESSION['rcon_password'] = $input['password'];
    }
    echo json_encode(['ok' => true, 'message' => 'RCON settings updated']);
    exit;
}

// Get RCON settings
if ($action === 'rcon-settings' && $method === 'GET') {
    echo json_encode([
        'ok' => true,
        'host' => $GLOBALS['rconHost'],
        'port' => $GLOBALS['rconPort'],
        'mode' => $mode,
    ]);
    exit;
}

// All other endpoints require auth
requireAuth();

// Get server status and config
if ($action === 'status' && $method === 'GET') {
    $config = getServerConfig();

    $adminConfig = [];
    if (!isRconOnly() && file_exists($adminConfigFile)) {
        $adminConfig = json_decode(file_get_contents($adminConfigFile), true) ?: [];
    }

    $running = false;
    if (!isRconOnly()) {
        exec("docker inspect -f '{{.State.Running}}' $serverContainer 2>/dev/null", $output, $ret);
        if ($ret === 0 && isset($output[0])) {
            $running = trim($output[0]) === 'true';
        }
    } else {
        // In RCON-only mode, check if RCON responds
        $test = rconSend('players');
        $running = $test['ok'];
    }

    echo json_encode([
        'ok' => true,
        'mode' => $mode,
        'config' => $config,
        'adminConfig' => $adminConfig,
        'running' => $running,
    ]);
    exit;
}

// Save server config and restart
if ($action === 'save' && $method === 'POST') {
    if (isRconOnly()) {
        http_response_code(403);
        echo json_encode(['error' => 'Not available in RCON-only mode']);
        exit;
    }
    $input = json_decode(file_get_contents('php://input'), true);

    $current = getServerConfig();

    if (isset($input['scenarioId'])) {
        $current['game']['scenarioId'] = $input['scenarioId'];
    }

    if (isset($input['mods'])) {
        $current['game']['mods'] = array_map(function($m) {
            if (is_string($m)) return (object)['modId' => $m];
            return (object)$m;
        }, $input['mods']);
    }

    if (isset($input['platforms'])) {
        $current['game']['supportedPlatforms'] = $input['platforms'];
    }

    file_put_contents($configFile, json_encode($current, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    exec("docker restart $serverContainer 2>&1", $output, $ret);
    $restartMsg = implode("\n", $output);

    echo json_encode([
        'ok' => $ret === 0,
        'message' => $ret === 0 ? 'Config saved, server restarting...' : 'Config saved but restart failed',
        'restartOutput' => $restartMsg,
    ]);
    exit;
}

// RCON command
if ($action === 'rcon' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $command = $input['command'] ?? '';

    if (empty($command)) {
        http_response_code(400);
        echo json_encode(['error' => 'No command provided']);
        exit;
    }

    $result = rconSend($command);
    echo json_encode($result);
    exit;
}

// Available missions from server
if ($action === 'missions' && $method === 'GET') {
    $result = rconSend('missions');
    if (!$result['ok']) {
        echo json_encode(['ok' => false, 'error' => $result['error'] ?? 'RCON failed']);
        exit;
    }
    $lines = explode("\n", $result['output']);
    $missions = [];
    foreach ($lines as $line) {
        $line = trim($line);
        // Match lines like {ECC61978EDCC2B5A}Missions/23_Campaign.conf
        if (preg_match('/^\{([A-F0-9]+)\}(.+\.conf)$/', $line, $m)) {
            // Derive a readable name from the path
            $path = $m[2];
            $basename = basename($path, '.conf');
            $name = str_replace('_', ' ', $basename);
            $name = preg_replace('/^(\d+)_/', '$1. ', $name);
            $missions[] = [
                'scenarioId' => $m[0],
                'name' => $name,
            ];
        }
    }
    echo json_encode(['ok' => true, 'missions' => $missions]);
    exit;
}

// Server logs
if ($action === 'logs' && $method === 'GET') {
    if (isRconOnly()) {
        http_response_code(403);
        echo json_encode(['error' => 'Not available in RCON-only mode']);
        exit;
    }
    $lines = isset($_GET['lines']) ? min((int)$_GET['lines'], 500) : 100;
    $cmd = "docker logs --tail $lines -t $serverContainer 2>&1";
    exec($cmd, $output, $ret);
    echo json_encode([
        'ok' => $ret === 0,
        'output' => implode("\n", $output),
    ]);
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Unknown action']);


// --- RCON Client (BattlEye RCON Protocol v2) ---

function rconSend(string $command): array {
    $rconPass = getRconPassword();
    if (empty($rconPass)) {
        return ['ok' => false, 'error' => 'No RCON password configured in server.json'];
    }

    $socket = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    if (!$socket) {
        return ['ok' => false, 'error' => 'Failed to create UDP socket'];
    }

    socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 5, 'usec' => 0]);
    socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => 5, 'usec' => 0]);

    // Login
    $loginPayload = "\x00" . $rconPass;
    $loginPkt = bePacket($loginPayload);
    @socket_sendto($socket, $loginPkt, strlen($loginPkt), 0, $GLOBALS['rconHost'], $GLOBALS['rconPort']);

    // Read login response + any server messages
    $deadline = time() + 5;
    $loggedIn = false;
    while (time() < $deadline) {
        $pkt = beRecv($socket);
        if ($pkt === false) break;
        $body = $pkt['body'];
        $type = ord($body[0]);

        if ($type === 0x00) {
            // Login response
            if (ord($body[1] ?? "\x00") === 0x01) {
                $loggedIn = true;
            } else {
                socket_close($socket);
                return ['ok' => false, 'error' => 'RCON authentication failed'];
            }
        } elseif ($type === 0x02) {
            // Server message during login — acknowledge it
            $msgSeq = ord($body[1] ?? "\x00");
            $ackPkt = bePacket("\x02" . chr($msgSeq));
            @socket_sendto($socket, $ackPkt, strlen($ackPkt), 0, $GLOBALS['rconHost'], $GLOBALS['rconPort']);
        }

        if ($loggedIn) break;
    }

    if (!$loggedIn) {
        socket_close($socket);
        return ['ok' => false, 'error' => 'RCON authentication failed (no response)'];
    }

    // Command
    $seq = 0;
    $cmdPayload = "\x01" . chr($seq) . $command;
    $cmdPkt = bePacket($cmdPayload);
    @socket_sendto($socket, $cmdPkt, strlen($cmdPkt), 0, $GLOBALS['rconHost'], $GLOBALS['rconPort']);

    // Collect responses + acknowledge server messages
    $output = '';
    $deadline = time() + 5;
    while (time() < $deadline) {
        $pkt = beRecv($socket);
        if ($pkt === false) break;
        $body = $pkt['body'];
        $type = ord($body[0]);

        if ($type === 0x01) {
            // Command response
            $respData = substr($body, 2);
            // Multi-packet header: 0x00 | total_packets | packet_index
            if (strlen($respData) >= 3 && ord($respData[0]) === 0x00) {
                $respData = substr($respData, 3);
            }
            $output .= $respData;
        } elseif ($type === 0x02) {
            // Server message — acknowledge it, include in output
            $msgSeq = ord($body[1] ?? "\x00");
            $ackPkt = bePacket("\x02" . chr($msgSeq));
            @socket_sendto($socket, $ackPkt, strlen($ackPkt), 0, $GLOBALS['rconHost'], $GLOBALS['rconPort']);
            $msgText = substr($body, 2);
            if (!empty($msgText) && strpos($msgText, 'Logged In!') === false) {
                $output .= $msgText . "\n";
            }
        }
    }

    socket_close($socket);

    if (empty($output)) {
        return ['ok' => true, 'output' => '(no response)'];
    }

    return ['ok' => true, 'output' => trim($output)];
}

function bePacket(string $payload): string {
    $crc = hash('crc32b', $payload, false);
    // CRC32 as 4 raw bytes (big-endian)
    $crcBytes = hex2bin($crc);
    return 'BE' . $crcBytes . "\xff" . $payload;
}

function beRecv($socket) {
    $data = '';
    @socket_recvfrom($socket, $data, 4096, 0, $from, $fromPort);
    if (strlen($data) < 8) return false;
    if ($data[0] !== 'B' || $data[1] !== 'E') return false;
    if (ord($data[6]) !== 0xFF) return false;

    $body = substr($data, 7);
    return ['body' => $body];
}

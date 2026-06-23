# Reforger Captain

Web-based admin panel for managing Arma Reforger dedicated servers. Change scenarios, install mods, and send RCON commands from a single page.

## Features

- **Server Management** — Change scenario, add/remove mods, save & restart
- **RCON Console** — Send commands directly to the server (say, kick, ban, shutdown, restart)
- **Quick Actions** — One-click buttons for common RCON commands
- **Admin Setup** — First-launch setup page to create admin credentials
- **Authelia Support** — Works behind Authelia reverse proxy (trusts `Remote-User` header)
- **Docker-native** — Runs in its own container, manages the game server via Docker socket

## Requirements

- Docker with Compose
- An Arma Reforger dedicated server running in Docker (e.g., [acemod/arma-reforger](https://github.com/AceModTeam/arma-reforger-docker))

## Quick Start

1. Clone this repo:
   ```bash
   git clone https://github.com/katzzero/reforger-captain.git
   cd reforger-captain
   ```

2. Create your `.env` file:
   ```bash
   cp .env.example .env
   ```
   Edit `.env` and set:
   - `SERVER_CONFIG_PATH` — path to your Arma Reforger `server.json`
   - `RCON_PASSWORD` — must match `rcon.password` in `server.json`

3. Start:
   ```bash
   docker compose up -d
   ```

4. Open `http://your-server:8080` and log in with your RCON password.

## Tabs

### Server
- View current scenario and installed mods
- Select predefined scenarios or enter a custom Scenario ID
- Add mods from the predefined list or by Workshop ID
- Save changes and restart the server

### RCON
- Type any RCON command and get the response
- Quick actions: Say, Kick, Ban, Restart Mission, Shutdown
- Scrollable command log

### Settings
- Change admin password
- View RCON connection settings
- Remove admin account (resets to setup screen)

## Configuration

### admin-config.json

Predefined scenarios and mods shown in the UI. Edit this file to add your own:

```json
{
  "scenarios": [
    {"name": "23 Campaign", "scenarioId": "{ECC61978EDCC2B5A}Missions/23_Campaign.conf"}
  ],
  "mods": [
    {"name": "RHS: Status Quo", "modId": "2824703552"}
  ]
}
```

### Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `SERVER_CONFIG_PATH` | *required* | Path to your Arma Reforger `server.json` |
| `RCON_PASSWORD` | *required* | RCON password (must match `server.json`) |
| `RCON_HOST` | `host.docker.internal` | RCON server address |
| `RCON_PORT` | `29999` | RCON server port |
| `SERVER_CONTAINER` | `arma-reforger` | Docker container name of the game server |

## Networking

The admin panel communicates with the Arma server through:
- **Config file** — Mounted volume to read/write `server.json`
- **Docker socket** — To restart the game server container
- **RCON** — TCP connection via `host.docker.internal` to send commands

## Security Notes

- The admin page should not be exposed to the internet
- For production use, place behind a reverse proxy with TLS
- The Docker socket mount gives full Docker access — keep the admin panel trusted

## License

MIT

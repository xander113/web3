/**
 * Lightweight WebSocket client for Laravel Reverb.
 * Implements the Pusher wire protocol natively — no pusher-js required.
 * Supports public channels and private channels (authenticated via /broadcasting/auth).
 */

class ReverbChannel {
    constructor(name) {
        this._name = name;
        this._listeners = {};
    }

    listen(event, callback) {
        // Normalise: strip leading dot if present
        const key = event.startsWith('.') ? event.slice(1) : event;
        if (!this._listeners[key]) this._listeners[key] = [];
        this._listeners[key].push(callback);
        return this;
    }

    _dispatch(rawEvent, data) {
        // Laravel broadcasts as "App\Events\ClassName" or ".ClassName"
        const key = rawEvent.includes('\\')
            ? rawEvent.split('\\').pop()
            : rawEvent.replace(/^\./, '');
        (this._listeners[key] || []).forEach(cb => cb(data));
    }
}

export class ReverbClient {
    constructor({ key, host, port, scheme = 'http', authEndpoint = '/broadcasting/auth' }) {
        this._key = key;
        this._host = host;
        this._port = port;
        this._scheme = scheme;
        this._authEndpoint = authEndpoint;
        this._socketId = null;
        this._ws = null;
        this._channels = {};
        this._reconnectDelay = 1000;
        this._connect();
    }

    // ── Public API (matches the subset used across the app) ────────────────

    channel(name) {
        return this._getOrCreate(name, false);
    }

    private(name) {
        return this._getOrCreate(`private-${name}`, true);
    }

    leaveChannel(name) {
        this._leave(name);
    }

    leave(name) {
        this._leave(name);
        this._leave(`private-${name}`);
    }

    // ── Internals ──────────────────────────────────────────────────────────

    _getOrCreate(channelName, isPrivate) {
        if (!this._channels[channelName]) {
            this._channels[channelName] = {
                channel: new ReverbChannel(channelName),
                isPrivate,
                subscribed: false,
            };
            // If already connected, subscribe immediately; otherwise it will
            // be deferred to the connection_established handler.
            if (this._socketId) this._subscribe(channelName);
        }
        return this._channels[channelName].channel;
    }

    _leave(channelName) {
        if (!this._channels[channelName]) return;
        if (this._ws && this._ws.readyState === WebSocket.OPEN) {
            this._ws.send(JSON.stringify({
                event: 'pusher:unsubscribe',
                data: { channel: channelName },
            }));
        }
        delete this._channels[channelName];
    }

    _connect() {
        const wsScheme = this._scheme === 'https' ? 'wss' : 'ws';
        const url = `${wsScheme}://${this._host}:${this._port}/app/${this._key}?protocol=7&client=graphictoria&version=1.0`;

        this._ws = new WebSocket(url);

        this._ws.onmessage = (event) => {
            let payload;
            try { payload = JSON.parse(event.data); } catch { return; }
            this._handle(payload);
        };

        this._ws.onclose = () => {
            this._socketId = null;
            Object.values(this._channels).forEach(entry => { entry.subscribed = false; });
            // Exponential back-off capped at 30 s
            setTimeout(() => this._connect(), Math.min(this._reconnectDelay *= 2, 30000));
        };

        this._ws.onopen = () => {
            this._reconnectDelay = 1000; // reset on successful connection
        };
    }

    _handle(payload) {
        switch (payload.event) {
            case 'pusher:connection_established': {
                const info = JSON.parse(payload.data);
                this._socketId = info.socket_id;
                // Subscribe to all pending channels
                Object.keys(this._channels).forEach(name => this._subscribe(name));
                break;
            }
            case 'pusher:error':
                console.warn('[Reverb] error:', payload.data);
                break;
            case 'pusher_internal:subscription_succeeded':
                if (payload.channel && this._channels[payload.channel]) {
                    this._channels[payload.channel].subscribed = true;
                }
                break;
            default: {
                if (payload.channel && this._channels[payload.channel]) {
                    let data = payload.data;
                    if (typeof data === 'string') {
                        try { data = JSON.parse(data); } catch { /* leave as string */ }
                    }
                    this._channels[payload.channel].channel._dispatch(payload.event, data);
                }
            }
        }
    }

    async _subscribe(channelName) {
        if (!this._socketId || !this._channels[channelName]) return;

        const entry = this._channels[channelName];
        if (entry.subscribed) return;

        if (entry.isPrivate) {
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
                const res = await fetch(this._authEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: `channel_name=${encodeURIComponent(channelName)}&socket_id=${encodeURIComponent(this._socketId)}`,
                });
                if (!res.ok) {
                    console.error(`[Reverb] Auth failed for ${channelName}:`, res.status);
                    return;
                }
                const { auth } = await res.json();
                this._send('pusher:subscribe', { channel: channelName, auth });
            } catch (e) {
                console.error('[Reverb] Auth error:', e);
            }
        } else {
            this._send('pusher:subscribe', { channel: channelName });
        }
    }

    _send(event, data) {
        if (this._ws?.readyState === WebSocket.OPEN) {
            this._ws.send(JSON.stringify({ event, data }));
        }
    }
}

# Production Deployment Guide: Fibonaughty

This guide outlines the infrastructure requirements, configuration checklists, system service managers, and hosting providers recommended to put **Fibonaughty**—the collaborative Planning Poker application—into production.

---

## 🏗️ 1. Core Architectural Requirements

To run **Fibonaughty** in a high-concurrency production environment, the infrastructure must handle three core components:
1. **HTTP Web Server (PHP-FPM + Nginx)**: For standard page requests, Livewire AJAX calls, and social logins.
2. **WebSocket Daemon (Laravel Reverb)**: A long-running PHP process handling real-time card votes, task sync, and participant presence.
3. **Database & Key-Value Cache (PostgreSQL/MySQL + Redis)**:
   - While SQLite is highly efficient for local testing, **PostgreSQL** or **MySQL** is recommended for production to safely handle transactional multi-session consensus locks and high write loads.
   - **Redis** is recommended for application caching and serves as the horizontal scaling bridge for Reverb.

---

## 🛠️ 2. Production Environment Checklist (`.env`)

In your production environment, set up your `.env` configuration as follows:

```env
APP_NAME=Fibonaughty
APP_ENV=production
APP_DEBUG=false
APP_URL=https://fibonaughty.com

# Database Connection (PostgreSQL Recommended)
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=fibonaughty_prod
DB_USERNAME=forge
DB_PASSWORD=your-secure-database-password

# Session & Cache Drivers
SESSION_DRIVER=database
CACHE_STORE=redis
QUEUE_CONNECTION=database

# Redis Configuration (For Caching and Reverb scaling)
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Real-Time Broadcasting (Reverb)
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=your-reverb-app-id
REVERB_APP_KEY=your-reverb-app-key
REVERB_APP_SECRET=your-reverb-app-secret
REVERB_HOST="fibonaughty.com"
REVERB_PORT=443
REVERB_SCHEME=https

# Vite Environment Map
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"

# Socialite OAuth Credentials (MUST match production URLs)
GITHUB_CLIENT_ID=your-github-prod-id
GITHUB_CLIENT_SECRET=your-github-prod-secret
GITHUB_REDIRECT_URI="https://fibonaughty.com/auth/github/callback"

GOOGLE_CLIENT_ID=your-google-prod-id
GOOGLE_CLIENT_SECRET=your-google-prod-secret
GOOGLE_REDIRECT_URI="https://fibonaughty.com/auth/google/callback"

APPLE_CLIENT_ID=your-apple-prod-services-id
APPLE_CLIENT_SECRET=your-apple-prod-signed-jwt
APPLE_REDIRECT_URI="https://fibonaughty.com/auth/apple/callback"
```

---

## ⚙️ 3. Web Server Configuration (Nginx SSL & WSS Reverse Proxy)

Since Reverb runs as a standalone daemon on an internal port (typically `8080`), you must configure **Nginx** to terminate SSL (`https://`) and securely proxy WebSocket connections (`wss://`) under the same domain.

Below is an optimized Nginx server block configuration:

```nginx
map $http_upgrade $connection_upgrade {
    default upgrade;
    ''      close;
}

server {
    listen 80;
    listen [::]:80;
    server_name fibonaughty.com;
    return 301 https://fibonaughty.com$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name fibonaughty.com;
    root /home/forge/fibonaughty/public;

    index index.php;
    charset utf-8;

    # SSL Certificates
    ssl_certificate /etc/letsencrypt/live/fibonaughty.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/fibonaughty.com/privkey.pem;

    # Standard Laravel Application Routing
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    # PHP-FPM Configuration
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Laravel Reverb WebSockets Reverse Proxy Route
    location /app {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection $connection_upgrade;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Scheme $scheme;
        proxy_set_header X-Forwarded-Host $host;
        proxy_set_header X-Forwarded-Port $server_port;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    # Deny access to sensitive files
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## 🤖 4. Process Daemonization (Supervisor)

In production, Laravel Reverb and background Queue Workers must run continuously as background daemons. If they crash or the server reboots, they must automatically restart. Use **Supervisor** to manage these processes.

### A. Reverb Daemon Configuration (`/etc/supervisor/conf.d/reverb.conf`)
```ini
[program:reverb]
process_name=%(program_name)s_%(process_num)02d
command=php /home/forge/fibonaughty/artisan reverb:start --host=127.0.0.1 --port=8080
autostart=true
autorestart=true
user=forge
numprocs=1
redirect_stderr=true
stdout_logfile=/home/forge/fibonaughty/storage/logs/reverb.log
stopwaitsecs=60
```

### B. Queue Worker Configuration (`/etc/supervisor/conf.d/worker.conf`)
```ini
[program:fibonaughty-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /home/forge/fibonaughty/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=forge
numprocs=2
redirect_stderr=true
stdout_logfile=/home/forge/fibonaughty/storage/logs/worker.log
stopwaitsecs=3600
```

Once configured, reload Supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
```

---

## 🚀 5. Recommended Hosting Providers

Here is a comparative analysis of the best hosting platforms suited for **Fibonaughty**:

| Provider / Method | Pricing Model | Deployment Complexity | Best Suited For | Key Advantages |
| :--- | :--- | :--- | :--- | :--- |
| **Laravel Forge + DigitalOcean** | VPS Cost (~$6-$12/mo) + Forge ($19/mo) | **Low** (Fully Automated) | Mainstream production & startups | Automatic SSL, Nginx, Redis, Supervisor, and Reverb setup. Zero-downtime deployment. |
| **Fly.io** | Pay-as-you-go (~$5-$15/mo) | **Medium** (Docker/Fly CLI) | High-speed global real-time collaboration | Highly optimized global edge routing. Excellent for latency-sensitive WebSockets. |
| **Render** | Resource-based (~$14/mo total) | **Low** (GitHub Auto-Deploy) | Developers seeking PaaS simplicity | Git-push auto deployment, managed PostgreSQL and Redis, easy background worker scaling. |
| **Self-Managed VPS (DigitalOcean / AWS)** | Fixed cost (~$6-$20/mo) | **High** (Manual Server Admin) | Complete control & low budget | No monthly management fee, maximum control over PHP, Supervisor, and Nginx configurations. |

### 🏆 Our Recommendation: **Laravel Forge + DigitalOcean**
For any PHP/Laravel team, **Laravel Forge** is the industry gold standard. It completely handles system administration:
- Connects to your GitHub repository and automatically deploys on push.
- Provisions Let's Encrypt SSL certificates automatically.
- Automatically handles **Supervisor** processes for both your queue workers and **Laravel Reverb**.
- Sets up database servers (PostgreSQL) and caching layers (Redis) with single-click actions.
- Automatically handles asset compilation (`npm run build`) during deployment hooks.

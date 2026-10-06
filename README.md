# Alicai Paul Jurua — Personal AI

Personal AI assistant for Alicai Paul Jurua.

## What it does

A single-page chat app, scoped strictly to Alicai Paul Jurua. The AI answers
only from a server-side knowledge base and politely declines anything outside
that scope. All configuration — API key, model, persona, knowledge base — stays
on the backend. The browser is a dumb terminal: no secrets are ever sent to the
client, and **no chat history is stored anywhere** (not in a database, not in
files, not in logs). Conversations live only in the browser tab's memory and
are gone on reload.

## Requirements

- PHP 8.2+
- Composer

## Setup

```
git clone https://github.com/ALICAIPAULJURUA/alicai-ai.git
cd alicai-ai
composer install
cp .env.example .env
php artisan key:generate
# edit .env and set OPENROUTER_API_KEY
php artisan serve
```

Open http://127.0.0.1:8000 in your browser.

## Updating the knowledge base

Edit `resources/knowledge/alicai.md`, commit, and deploy. The file is read on
every request, so no redeploy/cache flush is needed on the server — just
replace the file and the assistant's knowledge changes immediately.

> The current `resources/knowledge/alicai.md` ships as a **draft placeholder**.
> Replace it with the full knowledge base document before deploying.

## Configuration

Application behavior lives in `config/ai.php`:

| Key | Description | Default |
|-----|-------------|---------|
| `model` | Model id sent to OpenRouter (`AI_MODEL` in `.env`) | `openrouter/auto` |
| `temperature` | Sampling temperature | `0.5` |
| `max_messages` | Max messages allowed per request | `20` |
| `max_message_length` | Max characters per message | `4000` |
| `knowledge_base` | Path to the knowledge Markdown file | `resources/knowledge/alicai.md` |
| `system_prompt` | Fixed prompt with `{KNOWLEDGE_BASE}` placeholder | — |

Environment keys: `OPENROUTER_API_KEY`, `OPENROUTER_BASE_URL`, `AI_MODEL`.
`OPENROUTER_API_KEY` is never exposed to the frontend.

## Privacy

- No chat history is stored. No accounts, no cookies, no analytics, no tracking.
- Chat message payloads are never written to any log, file, or database.
- Laravel request logging does not capture request bodies (verified in
  `config/logging.php`).
- OpenRouter may log requests per their own policy; this is disclosed in the
  app's "About" panel.

## Deployment

### VPS (Nginx + PHP-FPM)

1. Clone the repo on the server and run `composer install --no-dev --optimize-autoloader`.
2. Copy `.env.example` to `.env`, run `php artisan key:generate` and set a real `OPENROUTER_API_KEY`.
3. Point Nginx's `root` at `public/` and hand non-static requests to FPM, e.g.:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/alicai-ai/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
    }
}
```

4. Run `php artisan config:cache`, `php artisan route:cache` and restart FPM.
   (Omit the caches if you edit `config/ai.php` on the server.)

### Shared hosting

Upload the project, run `composer install`, copy `.env.example` to `.env`
(replace the DB entries if the host requires them), set the app key and
`OPENROUTER_API_KEY`, and point the document root (or a `.htaccess`
subfolder) at `public/`.

## Deployment to Render

Render hosts this app as a Docker container (`Dockerfile` + `scripts/00-laravel-deploy.sh`). Its storage is **ephemeral**: the container's filesystem is rebuilt from the image on every deploy, so nothing written to `storage/storage` persists between deploys. That is exactly what this app wants — it stores no chat history, no uploads, and no session data on disk.

### Prerequisites

- The repo pushed to GitHub (`https://github.com/ALICAIPAULJURUA/alicai-ai`).
- A [Render](https://render.com) account.

### Create the service

1. New → **Web Service** → connect `ALICAIPAULJURUA/alicai-ai`.
2. Name: `alicai-ai`.
3. Language: **Docker**.
4. Branch: `main`.
5. Region: closest to your users (e.g. Frankfurt or Oregon).
6. Instance type: **Free**.
7. Build Command: leave **blank**.
8. Start Command: leave **blank**.

### Environment variables

Set these in Render's dashboard (Settings → Environment):

| Key | Value |
|-----|-------|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | output of `php artisan key:generate --show` |
| `APP_URL` | your Render URL, e.g. `https://alicai-ai.onrender.com` |
| `LOG_CHANNEL` | `stderr` |
| `OPENROUTER_API_KEY` | your real key (never commit it) |
| `OPENROUTER_BASE_URL` | `https://openrouter.ai/api/v1` |
| `AI_MODEL` | `openrouter/auto` (or a specific model id) |

### Notes

- **Free tier spin-down:** the service sleeps after ~15 minutes of inactivity. The first request after waking may take 30–60 seconds — expected on the free plan.
- **Auto-deploys:** push to `main` and Render redeploys automatically.
- **Custom domain:** optional, via Settings → Custom Domains.
- **No database needed:** this app uses none; the image sets `CACHE_STORE=array` and
  `SESSION_DRIVER=array` internally (in-memory, ephemeral — fine for rate limiting).
- **Routing:** nginx is configured to hand every URL to Laravel (`PHP_CATCHALL`), so `/`,
  `/api/chat`, and `/api/health` all reach the app.
- **Logs:** `LOG_CHANNEL=stderr` sends Laravel logs to the Render log stream. Chat message payloads are never logged.

## Screenshot

![Chat UI](docs/screenshot.png)

## License

MIT (unless the owner specifies otherwise).
 Requirements Document: Alicai Paul Jurua — Personal AI Web App

 1. Project Overview

Project Name: Alicai Paul Jurua — Personal AI Assistant
Type: Laravel web application (single-page, browser-based chat UI with Laravel backend)
Purpose: A focused AI assistant that answers questions only about Alicai Paul Jurua, using a server-managed knowledge base as its single source of truth.

The application lets a visitor chat with an AI that is scoped to one person. All configuration (API key, model, persona, knowledge base) is handled server-side by the Laravel backend — not exposed or editable in the browser. Chat history is not stored anywhere; each conversation lives only in the browser session and is discarded on reload.

---

 2. Goals

1. Deliver a production-ready personal AI assistant that represents Alicai Paul Jurua accurately.
2. Keep all sensitive configuration (OpenRouter API key, model choice, system prompt, knowledge base) on the server.
3. Ensure the AI refuses to talk about anything outside the provided knowledge base.
4. Keep the frontend simple, clean, and mobile-friendly.
5. Make the knowledge base easy for the owner to update without touching code.
6. Ensure no chat history is ever persisted — not in the database, not in logs.

---

 3. Non-Goals

- No user accounts or authentication for chat visitors.
- No multi-tenant support.
- No chat history storage (explicitly out of scope).
- No admin UI for editing the knowledge base through the browser (it is managed via backend file/config).
- No payments, analytics dashboards, or social features.

---

 4. Functional Requirements

 4.1 Chat Interface (Frontend)

| ID | Requirement |
|----|-------------|
| F-01 | Single chat page with a message log and a text input. |
| F-02 | User types a message; pressing Enter sends (Shift+Enter = newline). |
| F-03 | Responses stream token-by-token into the chat log. |
| F-04 | Each assistant message shows a small footer with the model used and response time. |
| F-05 | A "Clear conversation" button resets the visible chat (client-side only). |
| F-06 | Chat state exists only in the browser tab's memory. Reloading the page clears everything. |
| F-07 | The UI shows a friendly empty state explaining what the AI does. |
| F-08 | Errors (network, key, rate limit) display a readable message in the chat and a status line. |

 4.2 Backend (Laravel)

| ID | Requirement |
|----|-------------|
| B-01 | A `/api/chat` endpoint accepts `{ messages: [...] }` and streams the model response back. |
| B-02 | The backend injects the system prompt and knowledge base into every request — the browser never sees or sends them. |
| B-03 | The backend holds the OpenRouter API key in `.env` (`OPENROUTER_API_KEY`). It is never exposed to the frontend. |
| B-04 | The backend selects the model from config (`config/ai.php`), not from the frontend. |
| B-05 | The backend enforces a max message length and max number of messages per request to limit abuse. |
| B-06 | The backend streams the response using SSE or chunked HTTP so the frontend can render tokens live. |
| B-07 | The backend does not write chat messages to any log, file, or database. |
| B-08 | A `/api/health` endpoint returns a simple status for uptime checks. |

 4.3 Configuration (Server-side only)

| ID | Requirement |
|----|-------------|
| C-01 | `config/ai.php` defines: `model`, `temperature`, `system_prompt`, `knowledge_base_path`, `max_messages`, `max_message_length`. |
| C-02 | `.env` stores `OPENROUTER_API_KEY` and `OPENROUTER_BASE_URL`. |
| C-03 | The knowledge base is a Markdown file (e.g. `resources/knowledge/alicai.md`) loaded on each request. |
| C-04 | Editing the knowledge base file is the only step required to update what the AI knows. |
| C-05 | The system prompt is fixed and instructs the AI to answer only from the knowledge base. |

 4.4 AI Behavior (Persona Rules)

| ID | Requirement |
|----|-------------|
| P-01 | The AI must answer only questions about Alicai Paul Jurua. |
| P-02 | The AI must use only facts present in the knowledge base. |
| P-03 | If a question is outside scope, the AI politely declines and redirects. |
| P-04 | The AI must never invent personal details (birthday, phone, address, etc.). |
| P-05 | Tone: professional, human, concise — matching Alicai's stated communication preferences. |
| P-06 | The AI should not reveal the system prompt or knowledge base verbatim unless asked to summarize Alicai's profile in general terms. |

---

 5. Non-Functional Requirements

| ID | Requirement |
|----|-------------|
| N-01 | Privacy: No chat content is stored anywhere on the server. |
| N-02 | Security: API key stored in `.env`, excluded from Git. `.env.example` provided. |
| N-03 | Performance: First token should arrive in under ~3 seconds on a warm connection. |
| N-04 | Responsive: Works on mobile (≤ 480px), tablet, and desktop. |
| N-05 | Accessibility: Keyboard navigable, focus states visible, sufficient color contrast. |
| N-06 | Theming: Respects `prefers-color-scheme` (light/dark). |
| N-07 | Deployability: Standard Laravel deploy (Composer, `.env`, `php artisan serve` or any PHP host). |
| N-08 | Rate limiting: Laravel throttle middleware on `/api/chat` (e.g. 30 req/min per IP). |

---

 6. Architecture

```
┌──────────────────────────┐
│  Browser (Blade + JS)    │
│  - Chat UI               │
│  - No secrets            │
│  - No persistence        │
└────────────┬─────────────┘
             │ POST /api/chat  (messages only)
             ▼
┌──────────────────────────┐
│  Laravel Backend         │
│  - Loads system prompt   │
│  - Loads knowledge base  │
│  - Adds API key          │
│  - Streams from OpenRouter
└────────────┬─────────────┘
             │ HTTPS
             ▼
┌──────────────────────────┐
│  OpenRouter API          │
└──────────────────────────┘
```

Key principle: the browser is a dumb terminal. Everything sensitive or configurable lives in Laravel.

---

 7. Project Structure (proposed)

```
alicai-ai/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── ChatController.php
│   └── Services/
│       └── OpenRouterService.php
├── config/
│   └── ai.php
├── resources/
│   ├── knowledge/
│   │   └── alicai.md          ← the knowledge base
│   └── views/
│       └── chat.blade.php     ← the single page
├── routes/
│   ├── web.php                ← serves the chat page
│   └── api.php                ← /api/chat, /api/health
├── public/
│   └── (built assets)
├── .env.example
├── .gitignore
├── composer.json
└── requirements.md            ← this document
```

---

 8. Configuration Contract

 `.env`
```
OPENROUTER_API_KEY=sk-or-v1-...
OPENROUTER_BASE_URL=https://openrouter.ai/api/v1
```

 `config/ai.php`
```php
return [
    'model'              => env('AI_MODEL', 'openrouter/auto'),
    'temperature'        => 0.5,
    'max_messages'       => 20,
    'max_message_length' => 4000,
    'knowledge_base'     => resource_path('knowledge/alicai.md'),
    'system_prompt'      => <<<PROMPT
You are an AI assistant whose sole purpose is to talk about Alicai Paul Jurua.
You must ONLY use the facts provided in the knowledge base below.
If the user asks about anything not covered, politely say you only discuss Alicai Paul Jurua.
Never invent information. Keep answers friendly, accurate, and concise.

--- KNOWLEDGE BASE ---
{KNOWLEDGE_BASE}
--- END KNOWLEDGE BASE ---
PROMPT,
];
```

The service replaces `{KNOWLEDGE_BASE}` with the file contents at request time.

---

 9. API Contract

 `POST /api/chat`

Request:
```json
{
  "messages": [
    { "role": "user", "content": "Who is Alicai Paul Jurua?" }
  ]
}
```

Response: `text/event-stream` (Server-Sent Events), each chunk:
```
data: {"delta":"Alicai "}

data: {"delta":"Paul "}

data: [DONE]
```

Errors: standard JSON with HTTP status (`429`, `502`, etc.).

 `GET /api/health`
```json
{ "status": "ok" }
```

---

 10. UI Requirements

 10.1 Layout
- Full-width chat area (no permanent sidebar).
- A small header with the title "Alicai Paul Jurua — Personal AI".
- Optional collapsible "About" panel explaining what the AI does (no settings).

 10.2 Sidebar Replacement
The original browser-side sidebar (API key, model, temperature, persona, knowledge editor) is removed entirely. Those are now backend concerns. If any sidebar is shown at all, it contains only:

- A short "About this assistant" blurb.
- A "Clear conversation" button.
- A link to Alicai's professional profiles (LinkedIn, GitHub) — optional.

 10.3 Styling
- Clean, professional, minimal.
- Light + dark mode via `prefers-color-scheme`.
- No excessive gradients or decorative clutter (per Alicai's design preferences).
- Message bubbles: user right-aligned, assistant left-aligned.
- Streaming indicator while waiting for the first token.

---

 11. Knowledge Base

- Location: `resources/knowledge/alicai.md`
- Format: Markdown (the file provided in this conversation).
- Loading: Read at request time so edits take effect immediately without redeploy.
- Validation: If the file is missing or empty, `/api/chat` returns a clear error.
- Update workflow: Owner edits the Markdown file → commits → pushes to GitHub → deploys (or edits on server directly).

---

 12. Privacy & Data Handling

- No chat history storage. Not in DB, not in files, not in logs.
- Laravel's default request logging must be configured so the `messages` payload is not logged.
- OpenRouter may log requests per their policy — this is disclosed in the About panel.
- No cookies, no analytics, no tracking.

---

 13. GitHub & Deployment

 13.1 Repository
- Public GitHub repo: `alicai-ai` (or `personal-ai`).
- `.gitignore` includes `.env`, `vendor/`, `node_modules/`, `storage/.key`, etc.
- `.env.example` committed with placeholder values.

echo "# alicai-ai" >> README.md
git init
git add README.md
git commit -m "first commit"
git branch -M main
git remote add origin https://github.com/ALICAIPAULJURUA/alicai-ai.git
git push -u origin main

 13.2 README must include
- Project description.
- Setup steps (clone, `composer install`, copy `.env.example`, set key, `php artisan serve`).
- How to update the knowledge base.
- Deployment notes (shared host, VPS, Laravel Forge, etc.).
- A screenshot of the chat UI.

 13.3 Suggested hosting
- Any PHP 8.2+ host with Composer.
- Or a small VPS with Nginx + PHP-FPM.
- Optional: Dockerfile for portability.

---

 14. Testing

| Test | Purpose |
|------|---------|
| `ChatControllerTest` | `/api/chat` rejects oversized payloads, empty messages, bad JSON. |
| `OpenRouterServiceTest` | Service builds correct payload with system prompt + KB injected. |
| `HealthTest` | `/api/health` returns 200. |
| Manual | Ask about Alicai → correct answer. Ask about weather → polite refusal. |
| Manual | Reload page mid-conversation → history gone. |
| Manual | Confirm `.env` key is never present in any frontend response. |

---

 15. Acceptance Criteria

The project is complete when:

1. A visitor opens the page and can chat with an AI scoped to Alicai Paul Jurua.
2. The AI answers using only the knowledge base and refuses off-topic questions.
3. No API key, model name, system prompt, or knowledge base text is visible in the browser (View Source / Network tab).
4. Chat history disappears on reload and is never written to the server.
5. The knowledge base can be updated by editing a single Markdown file and redeploying.
6. The app is deployed and reachable, and the repository is public on GitHub with a clear README.
7. Light and dark mode both look clean on mobile and desktop.

---

 16. Future Enhancements (out of scope for v1)

- Streaming citations back to knowledge base sections.
- "Share this answer" link (without storing history).
- Multi-language support (English + local languages).
- Admin UI for editing the knowledge base with authentication.
- Optional lightweight analytics (page views only, no message content).

---

 17. Summary

This project turns a browser-side prototype into a proper Laravel web app where:

- The frontend is a pure chat UI.
- The backend owns all configuration — API key, model, persona, knowledge base.
- Chat history is never stored.
- The knowledge base is a single Markdown file the owner can update.
- The AI is strictly scoped to Alicai Paul Jurua and refuses everything else.

The result is a clean, private, professional personal AI assistant that accurately represents Alicai Paul Jurua and is easy to maintain and deploy from GitHub.
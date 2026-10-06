<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <title>Alicai Paul Jurua — Personal AI</title>
    <style>
        :root {
            --bg: #f5f6f8;
            --panel: #ffffff;
            --panel-border: #dde0e4;
            --text: #1f2328;
            --muted: #656b74;
            --accent: #0f766e;
            --accent-contrast: #ffffff;
            --user-msg: #0f766e;
            --user-msg-text: #ffffff;
            --err: #b42318;
            --err-bg: #fef3f2;
            --err-border: #fecdca;
            --focus: #0f766e;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --bg: #111417;
                --panel: #1a1f24;
                --panel-border: #2c333a;
                --text: #e7eaee;
                --muted: #9aa2ab;
                --accent: #2dd4bf;
                --accent-contrast: #04211d;
                --user-msg: #2dd4bf;
                --user-msg-text: #04211d;
                --err: #fda29b;
                --err-bg: #2c1512;
                --err-border: #6b2a22;
                --focus: #2dd4bf;
            }
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.5;
        }

        .app {
            max-width: 860px;
            margin: 0 auto;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            padding: 0 16px;
        }

        header {
            padding: 20px 0 12px;
        }

        header h1 {
            margin: 0;
            font-size: 1.35rem;
            font-weight: 650;
        }

        header p {
            margin: 4px 0 0;
            color: var(--muted);
            font-size: 0.9rem;
        }

        details.about {
            border: 1px solid var(--panel-border);
            border-radius: 10px;
            background: var(--panel);
            overflow: hidden;
        }

        details.about summary {
            cursor: pointer;
            padding: 10px 14px;
            font-weight: 600;
            font-size: 0.9rem;
            list-style: none;
        }

        details.about summary::-webkit-details-marker { display: none; }

        details.about[open] summary { border-bottom: 1px solid var(--panel-border); }

        .about-body {
            padding: 12px 14px;
            font-size: 0.9rem;
            color: var(--text);
        }

        .about-body p { margin: 0 0 10px; }

        .about-body .privacy-note {
            color: var(--muted);
            font-size: 0.85rem;
        }

        .about-actions {
            margin-top: 12px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }

        .about-actions a { color: var(--accent); }

        button,
        textarea {
            font-family: inherit;
            font-size: 0.95rem;
        }

        button:focus-visible,
        textarea:focus-visible,
        a:focus-visible {
            outline: 2px solid var(--focus);
            outline-offset: 1px;
        }

        button.link-like {
            background: none;
            border: none;
            color: var(--accent);
            padding: 0;
            cursor: pointer;
            font-size: 0.9rem;
        }

        button.primary {
            background: var(--accent);
            color: var(--accent-contrast);
            border: none;
            border-radius: 10px;
            padding: 10px 18px;
            font-weight: 600;
            cursor: pointer;
        }

        button.primary:disabled {
            opacity: 0.55;
            cursor: not-allowed;
        }

        #chatlog {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 16px 0;
            overflow-y: auto;
            min-height: 0;
        }

        .empty-state {
            margin: auto;
            text-align: center;
            color: var(--muted);
            max-width: 420px;
            padding: 24px;
        }

        .msg {
            max-width: 82%;
            padding: 10px 14px;
            border-radius: 14px;
            font-size: 0.95rem;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .msg.user {
            align-self: flex-end;
            background: var(--user-msg);
            color: var(--user-msg-text);
            border-bottom-right-radius: 4px;
        }

        .msg.bot {
            align-self: flex-start;
            background: var(--panel);
            border: 1px solid var(--panel-border);
            color: var(--text);
            border-bottom-left-radius: 4px;
        }

        .msg.err {
            align-self: flex-start;
            background: var(--err-bg);
            border: 1px solid var(--err-border);
            color: var(--err);
        }

        .msg footer {
            display: block;
            margin-top: 6px;
            font-size: 0.75rem;
            color: inherit;
            opacity: 0.75;
        }

        .stream-indicator {
            align-self: flex-start;
            color: var(--muted);
            font-size: 0.85rem;
            padding: 4px 2px;
        }

        .stream-indicator .dot { animation: pulse 1.2s ease-in-out infinite; }

        @keyframes pulse { 50% { opacity: 0.3; } }

        .composer {
            padding: 12px 0 20px;
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }

        .composer-wrap {
            flex: 1;
            display: flex;
            align-items: flex-end;
            background: var(--panel);
            border: 1px solid var(--panel-border);
            border-radius: 14px;
            padding: 6px 10px;
        }

        textarea {
            flex: 1;
            border: none;
            background: transparent;
            color: var(--text);
            resize: none;
            max-height: 160px;
            min-height: 24px;
            line-height: 1.5;
            padding: 8px 0;
        }

        textarea:focus { outline: none; }

        #status {
            min-height: 1.4em;
            font-size: 0.8rem;
            color: var(--muted);
            padding: 0 4px;
        }

        @media (max-width: 480px) {
            .app { padding: 0 10px; }
            header h1 { font-size: 1.15rem; }
            .msg { max-width: 90%; }
        }
    </style>
</head>
<body>
<div class="app">
    <header>
        <h1>Alicai Paul Jurua — Personal AI</h1>
        <p>Answers about Alicai only, from a server-side knowledge base.</p>
    </header>

    <details class="about">
        <summary>About this assistant</summary>
        <div class="about-body">
            <p>This assistant is scoped to Alicai Paul Jurua. Every answer is generated
            from a knowledge base stored on the server, and questions outside that scope
            are politely declined. All configuration — API key, model, and persona — is
            server-side; nothing is exposed to your browser.</p>
            <p class="privacy-note">OpenRouter may log requests per their policy. This app stores nothing:
            no accounts, no cookies, no analytics, and no chat history.</p>
            <div class="about-actions">
                <button type="button" class="link-like" id="clear-btn">Clear conversation</button>
                <a href="https://www.linkedin.com/in/alicai-paul-jurua" target="_blank" rel="noopener">LinkedIn</a>
                <a href="https://github.com/ALICAIPAULJURUA" target="_blank" rel="noopener">GitHub</a>
            </div>
        </div>
    </details>

    <div id="chatlog" role="log" aria-live="polite" aria-label="Chat conversation"></div>

    <p id="status" role="status" aria-live="polite"></p>

    <div class="composer">
        <div class="composer-wrap">
            <textarea id="input" rows="1" placeholder="Ask me anything about Alicai Paul Jurua. I only talk about him." aria-label="Message"></textarea>
        </div>
        <button type="button" id="send-btn" class="primary">Send</button>
    </div>
</div>

<script>
(function () {
    var log = document.getElementById('chatlog');
    var input = document.getElementById('input');
    var sendBtn = document.getElementById('send-btn');
    var status = document.getElementById('status');
    var clearBtn = document.getElementById('clear-btn');

    var history = [];
    var busy = false;

    function renderEmpty() {
        if (log.children.length === 0) {
            var el = document.createElement('div');
            el.className = 'empty-state';
            el.textContent = 'Ask me anything about Alicai Paul Jurua. I only talk about him.';
            log.appendChild(el);
        }
    }

    function scrollBottom() {
        log.scrollTop = log.scrollHeight;
    }

    function addBubble(cls, text) {
        var el = document.createElement('div');
        el.className = 'msg ' + cls;
        el.textContent = text;
        log.appendChild(el);
        scrollBottom();
        return el;
    }

    function showStatus(text) {
        status.textContent = text || '';
    }

    function autosize() {
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 160) + 'px';
    }

    input.addEventListener('input', autosize);

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            send();
        }
    });

    sendBtn.addEventListener('click', send);

    clearBtn.addEventListener('click', function () {
        history = [];
        log.innerHTML = '';
        showStatus('');
        autosize();
        renderEmpty();
        input.focus();
    });

    function setBusy(value) {
        busy = value;
        sendBtn.disabled = value;
        sendBtn.textContent = value ? 'Streaming' : 'Send';
    }

    function send() {
        var text = input.value.trim();
        if (busy || text === '') return;

        history.push({ role: 'user', content: text });
        addBubble('user', text);
        input.value = '';
        autosize();
        renderEmpty();

        var assistant = addBubble('bot', '');
        var typing = document.createElement('div');
        typing.className = 'stream-indicator';
        typing.textContent = 'Thinking';
        var dot = document.createElement('span');
        dot.className = 'dot';
        dot.textContent = ' .';
        typing.appendChild(dot);
        log.appendChild(typing);
        scrollBottom();

        var startedAt = Date.now();
        var full = '';
        var model = null;
        setBusy(true);
        showStatus('Connecting…');

        fetch('/api/chat', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ messages: history })
        }).then(function (res) {
            if (!res.ok) {
                throw new Error('HTTP ' + res.status);
            }
            showStatus('');
            var reader = res.body.getReader();
            var decoder = new TextDecoder();
            var buffer = '';

            function pump() {
                return reader.read().then(function (result) {
                    if (result.done) {
                        finish(null);
                        return;
                    }
                    buffer += decoder.decode(result.value, { stream: true });
                    processBuffer();
                    return pump();
                });
            }

            function processBuffer() {
                var frames = buffer.split('\n\n');
                buffer = frames.pop();
                frames.forEach(handleFrame);
            }

            function handleFrame(frame) {
                frame.split('\n').forEach(function (line) {
                    if (line.indexOf(':') === 0) return; // SSE comment
                    if (line.indexOf('data:') !== 0) return;
                    var payload = line.slice(5).trim();
                    if (payload === '[DONE]') {
                        finish(null);
                        return;
                    }
                    if (payload === '') return;
                    try {
                        var json = JSON.parse(payload);
                    } catch (e) {
                        return;
                    }
                    if (json.error) {
                        finish(json.error);
                        return;
                    }
                    if (json.model) {
                        model = json.model;
                    }
                    var delta = (json.choices && json.choices[0] && json.choices[0].delta && json.choices[0].delta.content) || '';
                    if (delta) {
                        full += delta;
                        assistant.textContent = full;
                        if (typing.parentNode) typing.parentNode.removeChild(typing);
                        scrollBottom();
                    }
                });
            }

            function finish(err) {
                if (typing.parentNode) typing.parentNode.removeChild(typing);
                setBusy(false);
                if (err) {
                    var msg = (typeof err === 'string' ? err : err.message || 'Something went wrong');
                    assistant.className = 'msg bot err';
                    assistant.textContent = 'Error: ' + msg;
                    if (history.length && history[history.length - 1].role === 'user') {
                        history.pop();
                    }
                    showStatus('Please try again.');
                    return;
                }
                var elapsed = ((Date.now() - startedAt) / 1000).toFixed(1);
                if (model) {
                    var footer = document.createElement('footer');
                    footer.textContent = model + ' · ' + elapsed + 's';
                    assistant.appendChild(footer);
                }
                history.push({ role: 'assistant', content: full });
                showStatus('');
            }

            return pump();
        }).catch(function (err) {
            if (typing.parentNode) typing.parentNode.removeChild(typing);
            setBusy(false);
            assistant.className = 'msg bot err';
            assistant.textContent = 'Error: ' + (err.message || 'Unable to reach the server.');
            if (history.length && history[history.length - 1].role === 'user') {
                history.pop();
            }
            showStatus('Please try again.');
        });
    }

    renderEmpty();
    input.focus();
})();
</script>
</body>
</html>
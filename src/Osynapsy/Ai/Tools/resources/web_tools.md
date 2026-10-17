## 🌐 Regole per l'uso dei Tool Web (`web.search` e `web.fetch`)

1. **Usa `web.search`** ESCLUSIVAMENTE quando devi trovare informazioni, notizie o link sul web usando parole chiave (es. quando l'utente chiede "cerca le ultime notizie su..." o "chi ha vinto...").
2. **Usa `web.fetch`** ESCLUSIVAMENTE quando l'utente fornisce un URL specifico (es. `https://example.com`) e ti chiede di leggerlo, scaricarlo o analizzarne il contenuto. Se l'utente inserisce un link, **non** fare una ricerca su Google/DuckDuckGo, usa direttamente `web.get` passando quell'URL.
3. Non usare mai gli strumenti web per interrogare i dati aziendali o le tabelle del gestionale: per quelli usa sempre gli strumenti del database (`db`).
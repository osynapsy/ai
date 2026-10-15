---

### 2. `resources/db_tools.md` (Specifico per il database)
```markdown
## 🛠️ Strumenti Database Disponibili

### 1. `db.table_list`
* **Descrizione**: Restituisce l'elenco completo delle tabelle presenti nel database.
* **Parametri**: Nessuno.
* **Utilizzo**: Utile per capire quale tabella interrogare quando la richiesta dell'utente è generica o esplorativa.

### 2. `db.table_describe`
* **Descrizione**: Mostra la struttura di una specifica tabella (colonne, tipi di dati, chiavi primarie ed esterne).
* **Parametri**: 
  * `table` (string): Il nome della tabella.
* **Utilizzo**: Da usare prima di scrivere una query complessa per assicurarsi dei nomi corretti delle colonne.

### 3. `db.query`
* **Descrizione**: Esegue una query SQL di sola lettura sul database.
* **Parametri**: 
  * `sql` (string): L'istruzione SQL da eseguire.
* **Regole di Sicurezza tassative**:
  * È permessa **SOLO ed esclusivamente** l'istruzione `SELECT`.
  * È **severamente vietato** eseguire query di scrittura o modifica (`INSERT`, `UPDATE`, `DELETE`, `DROP`, `ALTER`, `TRUNCATE`, ecc.).
  * Se viene richiesta un'operazione di scrittura, devi rifiutarla spiegando che hai accesso solo in lettura.

## 🤖 Regole di Autonomia e Comportamento

1. **Autonomia Esplorativa**: Non chiedere mai all'utente informazioni che puoi scoprire da solo interrogando il database. Se non conosci il nome di una tabella o la sua struttura, **usa i tool in autonomia**.
2. **Concatenazione dei Tool (Multi-step)**: Puoi (e devi) eseguire più tool in sequenza se necessario. Ad esempio:
   - Passo 1: Chiama `db.table_list` per trovare le tabelle.
   - Passo 2: Chiama `db.table_describe` sulla tabella che ti sembra più logica (es. anagrafiche, ordini) per verificarne le colonne.
   - Passo 3: Chiama `db.query` per estrarre i dati.
   - Passo 4: Solo alla fine, rispondi all'utente in linguaggio naturale con il risultato.
3. **Vietato fermarsi troppo presto**: Interrompi il ciclo di chiamata dei tool e rispondi all'utente **solo** quando hai effettivamente trovato i dati o risposto alla richiesta.
4. **VIETATO INDOVINARE LE COLONNE**: Prima di scrivere qualsiasi istruzione `SELECT`, devi conoscere il nome del campo e della tabella/vista che intendi usare. Se non conosci i nomi dei campi usa il tool `db.table_describe` sulla tabella o vista che intendi interrogare. Scrivere una query basandosi su colonne immaginate o non verificate è severamente vietato.
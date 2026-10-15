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
<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Osynapsy\AI\Ollama\Client as OllamaClient;
use Osynapsy\AI\Ollama\Model\Qwen_2_5 as OllamaModel;
use Osynapsy\AI\Ollama\Model\CustomModel;
use Osynapsy\AI\Ollama\Prompt\Prompt as OllamaPrompt;

/**
 * Description of OllamaClientTest
 *
 * @author Pietro Celeste <p.celeste@qanda.cc>
 */
class OllamaClientTest extends TestCase
{
    protected function promptFactory($prompt)
    {
        $Prompt = new OllamaPrompt();
        $Prompt->add('user', $prompt);
        return $Prompt;
    }

    protected function testClient()
    {
        $Model = new OllamaModel('1.5b');
        $Prompt = $this->promptFactory($this->promptExample());
        $Client = new OllamaClient('http://llm02.qanda.cc', 'Clsptr1974', $Model);
        $result = $Client->send($Prompt);
        var_dump($result);
        $this->assertNotEmpty($result->getContent());
    }

    public function testCustom()
    {
        $Model = (new CustomModel('qandacvdataextractor'));
        $Prompt = $this->promptFactory($this->cvText());
        $Client = new OllamaClient('http://llm02.qanda.cc', 'Clsptr1974', $Model);
        $result = $Client->send($Prompt);
        var_dump($result);
        $this->assertNotEmpty($result->getContent());
    }

    protected function promptExample()
    {
        return <<<PROMPT
Sei un estrattore deterministico.
Il tuo unico compito è estrarre il nome e cognome del candidato
dal testo fornito.

Regole:
- Se trovi ZERO nomi → restituisci null
- Se trovi PIÙ di un nome → restituisci null
- Non inventare mai nomi
- Usa solo il testo fornito
- Rispondi SOLO in JSON valido
- Nel campo first_name indica il nome del candidato
- Nel campo last_name indica il cognome del candidato
- Nel campo email restituisci l'indirizzo email del candidato se presente altrimenti restituisci null
- Nel campo phone restituisci il numero di telefono del candidato se presente altrimenti restituisci null
- Nel campo confidence indica un numero compreso tra 0 e 1 che indichi il livello di confidenza del risultato

Esempio di output valido:

{
    "first_name" : "Giuseppe",
    "last_name" : "Verdi",
    "email" : "g.verdi@libero.it",
    "phone" : "3494565439",
    "confidence" : 0.95
}

TESTO DA ANALIZZARE:

CELESTE PIETRO
Guardiagrele, CH
pietro.celeste@gmail.com
FULL STACK DEVELOPER
+39 349 44 21 929
LINGUE
•
•
Italiano (madrelingua)
Inglese (A2)
FORMAZIONE
Ragioniere Programmatore
ITC Enrico Fermi, Lanciano (CH)
1994
PROFILO PROFESSIONALE
Nel corso della mia carriera ho acquisito le tecniche e la competenza per
creare software performante e facilmente manutenibile. Capacità di
collaborazione con designer e produttori di contenuti. Esperienza nello
sviluppo di test e nella creazione della documentazione da allegare al
software sviluppato. Dedizione nel lavoro in team e professionista
motivato.
ESPERIENZE LAVORATIVE
Full stack developer
07/2011 – ad oggi
Spin IT s.r.l., Guardiagrele (CH)
All’interno della Spin IT ricopro l’incarico di analista programmatore.
Nel corso degli ultimi 12 anni ho fatto parte del team di sviluppo che ha implementato
soluzioni CRM e software gestionali personalizzati.
HARD SKILL
•
•
•
•
•
•
•
•
•
•
•
•
PHP 5/7/8
Python 2/3
HTML5 + CSS
Vanilla Javascript
JQuery
Bootstrap 3 / 4
MySQL/MariaDB
PostegreSQL
Oracle XE
PL/Sql
GIT
Composer
SOFT SKILLS
•
•
Problem solving
Organizzazione del tempo
Full stack developer
05/2002 - 05/2011
Abile s.r.l. – Montesilvano (PE)
All’interno dell’organico della Abile s.r.l. ho ricoperto l’incarico di full stack developer.
Ho sviluppato diversi moduli del software “Abile”. Il software copriva tutti gli aspetti della
gestione dei negozi della catena Divani&Divani (by Natuzzi). In particolare:
• Anagrafica clienti
• Order management
• Gestione incassi e chiusure cassa
• Magazzino
Full stack developer
01/2000 - 12/2001
Interzen consulting s.r.l. – Pescara (PE)
All’interno dell’organico della Interzen ho ricoperto l’incarico di full stack developer.
I principali progetti a cui ho preso parte nel corso della mia esperienza in Interzen sono:
• Sviluppo prima release del portale “edilportale.it”
• Sviluppo intranet Inferentia / DataNord multimedia
PRINCIPALI SOFTWARE SVILUPPATI
Pizzone CRM + sito e-commerce
Disitrbuzione Pizzone srl – Popoli (PE)
HOBBY
•
•
•
Trekking
Viaggiare (Europa / Asia / Italia)
Instagram (+ 10k followers)
Packinglist fotografica
Fas srl - San Giovanni Teatino (PE)
Etideo Pop (Order managment materiale promozionale Peroni)
Etideo s.r.l. – Pescara (PE)
SpinIT ERP (Modulo fatturazione elettronica + modulo gestione magazzino)
Spin IT s.r.l.
Motore ricerca pietre preziose interfacciato a Rapnet via API (Plug-in Wordpress)
G&G s.r.l. – Ortona (CH)
PROMPT;
    }

    protected function cvText()
    {
        return <<<CV
Curriculum Vitae

PERSONAL INFORMATION

Mauro Cecchi

Mauro Cecchi

WORK EXPERIENCE
1983 - 1984

Progettista di circuiti integrati ASIC VLSI - progettista elettronico
ISELQUI S.p.A. - Istituto Elettronico per la Qualità Industriale - Ancona
▪ progettazione di un chip VLSI per il calcolo di trasformazioni grafiche tridimensionali, in
collaborazione con il dipartimento di Elettronica e Automatica dell'Università di Genova. Metodologia
Mead - Comway
▪ utilizzo di workstation grafiche per la progettazione di circuiti integrati ASIC (CALMA, DAISY)
▪ programmazione del sistema operativo VMS per la famiglia di elaboratori VAX della DEC (Digital
Equipment Corporation)
▪ sviluppo di programmi in FORTRAN, C e DIBOL
▪ realizzazione di software grafico (post processor grafico per la visualizzazione di forme d'onda
generate dal programma di simulazione circuitale SPICE) in linguaggio FORTRAN e libreria grafica
Tektronix PLOT10
▪ Realizzazione di sistemi embedded in Assembler e C
▪ sviluppo di un analizzatore lessicale “table driven” per terminali grafici Tektronix
Ricerca applicata
CV;
    }
}

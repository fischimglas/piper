# Piper Design

## Übersicht

Piper ist ein leichtgewichtiges PHP-DAG-Framework zur Orchestrierung von KI- und API-Workflows. Es unterstützt Nodes,
Pipes (lineare Sequenzen von Nodes) und Graphen als First-Class Citizens mit eigenen Typen und Interfaces sowie weitere Kompositionsmechanismen
wie `combine`. Im Fokus stehen explizite Abhängigkeiten über Objekt-Referenzen (keine Strings), deklarative, fliessende DSL und flexible Workflow-Strukturen.

## Ablauf

Pipes, Graphen und Kombinatoren verbinden Nodes zu komplexen Workflows. Pipes sind sequenzielle Abläufe, Graphen
orchestrieren Nodes, Pipes und andere Graphen (alle als eigene, typisierte Objekte), und `combine` ermöglicht parallele Zusammenführungen.
Nodes, Pipes und Graphen werden ausschliesslich über Objekt-Referenzen miteinander verbunden – keine Verknüpfung per String-IDs.
Das Framework erkennt automatisch Zyklen im Abhängigkeitsgraphen (DAG) und verhindert deren Ausführung.
Die Ausführung ist explizit typisiert und unterstützt asynchrone Verarbeitungsschritte.

### Hierarchie

Graph > Pipe > Node  
Combine (Parallel-Kombinator) kann Pipes, Nodes und Graphen zusammenführen

---

## Kernkonzepte
### DataBag

**Beschreibung:** Ein DataBag ist eine strukturierte, typisierte Datenablage, die während der Ausführung von Workflows als universeller Kontext dient. Sie ermöglicht das Speichern, Lesen und Übergeben von Daten zwischen Nodes, Pipes und Graphen, ohne auf globale Variablen oder externe Speicher zurückzugreifen.

- DataBags dienen ausschliesslich dem Transport und der Speicherung von Kontextdaten innerhalb eines Workflows.
- Jeder ausführbare Workflow-Bestandteil kann mit einem DataBag ausgeführt werden, um Kontextdaten zu transportieren.
- DataBags können beliebige Schlüssel/Werte aufnehmen, sind typisiert und können im gesamten Workflow weitergereicht werden.
- DataBags unterstützen das Cloning für isolierte Ausführungen.
- DataBags sind insbesondere für komplexe Workflows und Nebenläufigkeit relevant, aber **implementieren selbst kein Caching**.

#### DataBagInterface

```php
interface DataBagInterface
{
    public function set(string $key, mixed $value): static;
    public function get(string $key, mixed $default = null): mixed;
    public function has(string $key): bool;
    public function all(): array;
    public function remove(string $key): static;
    public function clear(): static;
    public function copy(): static; // vormals clone()
}
```

### Cache & Cache-Strategien

**Beschreibung:** Caching ermöglicht das Zwischenspeichern von Ergebnissen von Nodes, Pipes oder Graphen, um wiederholte, teure Berechnungen oder API-Aufrufe zu vermeiden. Die Caching-Strategie steuert, wann und wie Ergebnisse wiederverwendet werden.

- **Nur Nodes, Pipes und Graphen** (und Kombinatoren) können Caching aktivieren und konfigurieren. DataBags selbst implementieren kein Caching.
- Die Caching-Strategie wird über ein Enum `CacheStrategy` definiert.
- Optional kann eine Zeitspanne (`ttlSeconds`) angegeben werden.
- Die Methode `fresh()` erzwingt die Neuberechnung und ignoriert den Cache.

#### CacheStrategy-Enum

```php
enum CacheStrategy
{
    case DISABLED;      // Kein Caching
    case PER_RUN;       // Nur für die aktuelle Ausführung (im Memory)
    case PER_INPUT;     // Pro Input-Wert (z.B. Hash des Inputs)
    case GLOBAL;        // Globaler Cache, unabhängig vom Input
}
```

#### CacheInterface

```php
interface CacheInterface
{
    public function get(string $key): mixed;
    public function set(string $key, mixed $value, int $ttlSeconds = 0): static;
    public function has(string $key): bool;
    public function delete(string $key): static;
    public function clear(): static;
}
```

#### Cache-Key-Generierung

Die Generierung der Cache-Keys folgt festen, nachvollziehbaren Formaten, um Kollisionen zu vermeiden und die Wiederverwendbarkeit zu gewährleisten:

- **PER_RUN:**  
  `run:{runId}:{elementId}`
  - Beispiel: `run:abc123:nodeA`
- **PER_INPUT:**  
  `input:{elementId}:{inputHash}`
  - Beispiel: `input:nodeA:7fa1ab...`
- **GLOBAL:**  
  `global:{elementId}`
  - Beispiel: `global:myPipe`

Der `elementId` ist die eindeutige ID des Nodes/Pipes/Graphen. Der `inputHash` ist ein Hash des jeweiligen Inputs.

#### Cache Error-Handling

Beim Zugriff auf den Cache können Fehler auftreten (z.B. Verbindungsprobleme, ungültige Einträge, Timeouts). Das Error-Handling erfolgt nach folgenden Prinzipien:

- **Cache-Fehler führen niemals zum Abbruch des Workflows**.  
  Stattdessen wird der Cache als nicht vorhanden behandelt und das Ergebnis frisch berechnet.
- **Fehler werden geloggt**, sofern ein Logger vorhanden ist.
- **Cache-Implementierungen dürfen Exceptions werfen**, diese werden aber von der Ausführungsebene abgefangen und behandelt.

**Beispiel:**
```php
try {
    $cached = $cache->get($key);
} catch (\Throwable $e) {
    $logger?->error('Cache-Fehler: ' . $e->getMessage(), ['key' => $key]);
    // Cache wird ignoriert, frische Berechnung folgt
    $cached = null;
}
```


Nodes sind die kleinste Verarbeitungseinheit und repräsentieren einzelne Operationen wie Textverarbeitung,
Bildgenerierung, Datenbankzugriffe etc.

| Typ         | Beschreibung                                   | Adapter                                        | Spezialmethoden / Optionen                               |
|-------------|------------------------------------------------|------------------------------------------------|----------------------------------------------------------|
| text        | Texttransformationen (KI)                      | Google Gemini, OpenAI, Mistral, Claude, Ollama | `template()`, `prompt()`, `temperature()`, `maxTokens()` |
| image       | Bildgenerierung (Cover, Illustrationen)        | ImageArt                                       | `template()`, `style()`, `resolution()`                  |
| webSearch   | Web-Suchanfragen                               | Google Search                                  | `query()`, `filters()`, `maxResults()`                   |
| translate   | Übersetzung von Texten                         | DeepL                                          | `sourceLang()`, `targetLang()`, `formality()`            |
| textToVoice | Text-zu-Audio-Konvertierung                    | ElevenLabs                                     | `voice()`, `speed()`, `pitch()`                          |
| vector      | Vektor-Datenbankoperationen (Embedding, Index) | Pinecone                                       | `embeddingModel()`, `namespace()`, `topK()`              |
| DB          | SQL-Datenbank                                  | MySQL, SQLite, Elastic                         | `query()`, `parameters()`, `transaction()`               |
| Writer      | Dateien schreiben                              |                                                | `filePath()`, `mode()`, `encoding()`                     |
| Reader      | Dateien oder URLs lesen                        |                                                | `source()`, `format()`                                   |
| Decide      | Entscheiden                                    |                                                | `if()` `elseif()` `else()`                               |

#### Node-Konfiguration

- Jeder Node wird mit `node()` oder spezialisierten Factory-Methoden wie `text()`, `image()`, `transform()`
  erstellt.
- Nodes deklarieren ihren Output-Contract mit `yields(Cardinality, ContentType)`.
- Nodes können Templates, Prozesse, Filter oder Spezialverhalten definieren.
- **Templates werden ausschliesslich in Nodes verwendet** und interpolieren nur explizit als Input deklarierte Abhängigkeiten (z.B. `{{input}}`, `{{andereNode}}`), keine impliziten Referenzen.
- Abhängigkeiten werden via `dependsOn($nodeOrPipe, Strategy)` angegeben – stets mit Objekt-Referenzen auf Nodes, Pipes oder Graphen (keine String-IDs).

#### Node-Events

Nodes unterstützen Event-Handler, die an bestimmte Ereignisse im Lebenszyklus eines Nodes gebunden werden können.  
Folgende Events stehen als Konstanten zur Verfügung:

| Event-Konstante     | Beschreibung                                                                                                                                                   |
|---------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `Event::BEFORE_RUN` | Wird unmittelbar vor der Ausführung des Nodes ausgelöst.                                                                                                       |
| `Event::AFTER_RUN`  | Wird nach erfolgreicher Ausführung des Nodes ausgelöst.                                                                                                        |
| `Event::ON_ERROR`   | Wird ausgelöst, wenn während der Ausführung ein Fehler auftritt. Hier kann definiert werden, ob die Pipe fortgesetzt wird oder der Fehler weitergeworfen wird. |

Das `ON_ERROR`-Event ermöglicht es, Fehler individuell zu behandeln. Beispielsweise kann man entscheiden, ob die
Ausführung der Pipe trotz Fehler fortgesetzt wird oder ob der Fehler die gesamte Ausführung unterbricht.

##### Beispiel: Event-Registrierung bei einem Node

```php
$node = Foundry::text('exampleNode')
    ->template('Process this input: {{input}}')
    ->on(Event::BEFORE_RUN, function($context) {
        // Aktionen vor der Node-Ausführung, z.B. Logging
        echo "Node wird ausgeführt.\n";
    })
    ->on(Event::AFTER_RUN, function($context, $result) {
        // Aktionen nach der erfolgreichen Ausführung
        echo "Node-Ausführung abgeschlossen.\n";
    })
    ->on(Event::ON_ERROR, function($context, \Throwable $error, callable $continue, callable $abort) {
        // Fehlerbehandlung: Fehler loggen und entscheiden, ob Pipe fortgesetzt wird
        error_log("Fehler in Node: " . $error->getMessage());
        // Beispiel: Pipe trotz Fehler fortsetzen
        $continue();
        // Oder um Fehler weiterzuwerfen:
        // $abort();
    })
    ->yields(Cardinality::UNIT, ContentType::TEXT);
```

---

### Pipe

**Beschreibung:** Pipes sind lineare Sequenzen von Nodes, die einen Workflow-Schritt darstellen.

- Pipes werden mit `pipe('name')` erstellt.
- Nodes werden mit `pipe($node)` hinzugefügt.
- Der Output der Pipe entspricht dem Output des letzten Nodes.
- Pipes können eigene Inputs definieren via `input([...])`.
- Pipes können auch als Nodes in Graphen oder anderen Pipes verwendet werden.
- Pipes unterstützen `yields()` für den Output-Contract.
- Pipes erlauben unabhängige Node-Konfigurationen innerhalb.

---

### Graph

**Beschreibung:** Graphen orchestrieren Nodes, Pipes und Kombinatoren zu komplexen Workflows.

- Graphen werden mit `graph('name', [...inputs])` erstellt.
- Nodes, Pipes und Kombinatoren können mit `node()`, `pipe()` oder `combine()` hinzugefügt werden.
- Graphen unterstützen Logger via `withLogger($logger)`.
- Abhängigkeiten werden implizit via `dependsOn`.
- Graphen sind die oberste Workflow-Ebene und führen die Ausführung aus.

---

### Transform

**Beschreibung:** Spezialisierte Nodes, die Daten umformen oder filtern.

- Erstellt mit `transform('name')`.
- Definiert eine oder mehrere Prozessfunktionen via `process(fn($input) => $output)`.
- Kann LIST → UNIT transformieren, Inputs mergen oder filtern.
- Unterstützt `yields()` zur Definition des Outputs.
- Kann Abhängigkeiten via `dependsOn()` haben.

---

### Decide (Conditional Nodes)

**Beschreibung:** Nodes für bedingte Verzweigungen im Workflow.

- Erstellt mit `decide('name')`.
- Definiert Bedingungen mit `if(fn($input) => bool, $nodeOrPipe)`.
- Unterstützt `elseif(fn($input) => bool, $nodeOrPipe)` und `else($nodeOrPipe)`.
- Ermöglicht dynamische Auswahl des nächsten Nodes/Pipes basierend auf Input.

---

### Strategy

Definiert, wie der Input auf Abhängigkeiten angewendet wird.

| Wert     | Bedeutung            |
|----------|----------------------|
| WHOLE    | Ganzer Input         |
| PER_ITEM | Pro einzelne Einheit |

---

### Cardinality

Definiert die Kardinalität des Outputs eines Nodes.

| Wert | Bedeutung         |
|------|-------------------|
| UNIT | Einzelnes Element |
| LIST | Mehrere Elemente  |

---

### Content Types

Definiert den Typ des Outputs eines Nodes.

- TEXT (Text, HTML, Markdown, etc.)
- FILE (Blob, Image, Audio, Video)
- AUDIO (Audio-Daten)
- CODE (Quellcode, Javascript, XML, etc.)
- OBJECT (PHP Array / Object)

---

## Fluent API Funktionen im Überblick

### Graph

Der Graph yielt nichts und erhält auch keine Input-Daten. Er orchestriert nur die Nodes/Pipes.

| Funktion             | Beschreibung                                   |
|----------------------|------------------------------------------------|
| `withLogger(Logger)` | Fügt dem Graphen oder Pipe einen Logger hinzu. |
| `node(Node)`         | Erstellt oder fügt einen Node im Graph hinzu.  |

### Pipe

Die Pipes beginnen mit Input und transformieren diesen mittels Adapter und Filter weiter.

| Funktion                           | Beschreibung                                                                                                |
|------------------------------------|-------------------------------------------------------------------------------------------------------------|
| `input(ANY)`                       | Definiert Eingabedaten für Pipes oder Graphen.                                                              |
| `withLogger(Logger)`               | Fügt dem Graphen oder Pipe einen Logger hinzu.                                                              |
| `pipe(Pipe)`                       | Erstellt oder fügt eine Node in einer Pipe hinzu.                                                           |
| `transform(fn() \| Transformer)`   | Erstellt einen Transform-Node zur Datenumformung. diese kann filter wie map, filter, reduce enthalten       |
| `decide(Decider)`                  | Erstellt einen Conditional-Node zur bedingten Steuerung des Workflows. enthält `if()`, `elseif()`, `else()` |
| `yields(Cardinality, ContentType)` | Definiert den Output-Contract (Cardinality, ContentType).                                                   |

### DataBag & Caching: Übersicht der Funktionen

#### DataBag-Nutzung

Alle ausführbaren Workflow-Komponenten (Nodes, Pipes, Graphen) unterstützen:

| Funktion                                 | Beschreibung                                                                                       |
|------------------------------------------|----------------------------------------------------------------------------------------------------|
| `withDataBag(DataBagInterface $dataBag)` | Übergibt einen expliziten DataBag als Kontext für die Ausführung.                                  |

#### Cache-Nutzung (nur für Nodes, Pipes, Graphen, Kombinatoren)

Nur Nodes, Pipes, Graphen und Kombinatoren unterstützen:

| Funktion                                                    | Beschreibung                                                                                       |
|-------------------------------------------------------------|----------------------------------------------------------------------------------------------------|
| `cache(CacheStrategy $strategy, int $ttlSeconds = 0)`       | Aktiviert und konfiguriert das Caching für dieses Element.                                         |
| `fresh()`                                                   | Erzwingt die Neuberechnung und ignoriert ggf. vorhandene Cache-Einträge.                           |

#### Node: Spezialmethoden

Eine Node ist eine einzelne Verarbeitungseinheit, die einen klar definierten Output hat.

| Funktion                           | Beschreibung                                              |
|------------------------------------|-----------------------------------------------------------|
| `withLogger(Logger)`               | Fügt dem Graphen oder Pipe einen Logger hinzu.            |
| `dependsOn(Pipe\|Node, Strategy)`  | Definiert Abhängigkeiten mit Strategie.                   |
| `yields(Cardinality, ContentType)` | Definiert den Output-Contract (Cardinality, ContentType). |
| `withAdapter(AdapterInterface)`    | Setzt einen spezifischen Adapter für die Node.            |
| `getAdapter(): ?AdapterInterface`  | Gibt den aktuell konfigurierten Adapter zurück.           |


---

### TemplateEngine

**Beschreibung:**  
Die TemplateEngine ist für die Interpolation von Platzhaltern in Strings zuständig, insbesondere in Nodes mit Templates (z.B. KI-Text-Nodes). Sie ersetzt Platzhalter wie `{{key}}` oder `{{key.subkey}}` durch Werte aus dem Input, DataBag oder aus Abhängigkeiten. Die Engine unterstützt verschachtelte Arrays/Objekte und optionalen Strict-Modus.

- Templates werden ausschliesslich in Nodes verwendet.
- Platzhalter-Syntax: `{{ key }}` oder `{{ key.subkey }}`.
- Werte werden aus Input, DataBag und expliziten Abhängigkeiten aufgelöst.
- Bei fehlenden Werten bleibt der Platzhalter erhalten oder es wird (im Strict-Modus) eine Exception geworfen.
- Nicht-String-Werte werden als JSON interpoliert.

#### API

```php
TemplateEngine::render(string $template, array|object|null $vars, bool $strict = false): string
```

#### Beispiel

```php
$template = 'Hallo {{user.name}}, dein Token: {{token}}';
$vars = ['user' => ['name' => 'Anna'], 'token' => 123];
$result = TemplateEngine::render($template, $vars);
// Ergebnis: 'Hallo Anna, dein Token: 123'
```

---

## Beispiele
### DataBag-Nutzung

```php
$dataBag = new DataBag();
$dataBag->set('userId', 123)
        ->set('session', 'abc123');

$nodeA = Foundry::text('nodeA')
    ->template('User: {{userId}}, Session: {{session}}')
    ->yields(Cardinality::UNIT, ContentType::TEXT);

$result = $nodeA
    ->withDataBag($dataBag)
    ->run();
```

### Caching-Strategien (für Nodes, Pipes, Graphen)

```php
$nodeB = Foundry::text('nodeB')
    ->template('Expensive operation')
    ->yields(Cardinality::UNIT, ContentType::TEXT)
    ->cache(CacheStrategy::PER_INPUT, 3600); // Pro Input für 1h cachen

$pipe = Foundry::pipe('myPipe')
    ->pipe($nodeB)
    ->cache(CacheStrategy::GLOBAL); // Globaler Cache für die gesamte Pipe

$result = $pipe->run(['input' => 'Some input']);

$freshResult = $pipe->fresh()->run(['input' => 'Some input']); // Ignoriert Cache, erzwingt Neuberechnung
```

```php
// 1. Einfacher Text-Node
$nodeA = Foundry::text('nodeA')
    ->template('Summarize this: {{input}}')
    ->yields(Cardinality::UNIT, ContentType::TEXT);

// 2. Transform-Node: Wörter splitten und filtern
$splitWords = Foundry::transform('splitWords')
    ->process(fn(string $input) => explode(' ', $input))
    ->process(fn(array $items) => array_filter($items, fn($i) => strlen($i) > 3))
    ->yields(Cardinality::LIST, ContentType::TEXT);

// 3. Conditional/Decide Node mit mehreren Bedingungen
$summaryDecision = Foundry::decide('summaryDecision')
    ->if(fn($input) => count($input) > 5, $nodeA)
    ->elseif(fn($input) => count($input) > 2, $splitWords)
    ->otherwise($nodeA); // vormals else()

// 4. Text-Node mit Abhängigkeiten und Template-Interpolation
$nodeB = Foundry::text('nodeB')
    ->template('Analyze summary: {{nodeA}} and words: {{splitWords}}')
    ->yields(Cardinality::UNIT, ContentType::TEXT)
    ->dependsOn($nodeA, Strategy::PER_ITEM)
    ->dependsOn($splitWords, Strategy::WHOLE);

// 5. Such-Node (Search)
$searchNode = Foundry::webSearch('searchResults')
    ->dependsOn($nodeB, Strategy::WHOLE)
    ->yields(Cardinality::LIST, ContentType::TEXT);

// 6. Reader-Node zum Lesen von Inhalten
$readNode = Foundry::read('fileContent')
    ->dependsOn($nodeB)
    ->yields(Cardinality::UNIT, ContentType::TEXT);

// 7. Komplexere Transform-Node mit mehreren Abhängigkeiten
$zipChapters = Foundry::transform('zipChapters')
    ->process(fn(array $chapters, array $texts) => array_map(
        fn($title, $text) => ['title' => $title, 'text' => $text],
        $chapters,
        $texts
    ))
    ->yields(Cardinality::LIST, ContentType::OBJECT)
    ->dependsOn($chaptersNode, Strategy::WHOLE)
    ->dependsOn($chapterTextsNode, Strategy::WHOLE);

// 8. TextToVoice Node für Audio-Ausgabe
$ttsNode = Foundry::textToVoice('audioBook')
    ->dependsOn($nodeB, Strategy::PER_ITEM)
    ->yields(Cardinality::UNIT, ContentType::AUDIO);

// --- Pipe erstellen ---
$pipe = Foundry::pipe('myPipe')
    ->input(['input' => 'Bern, Switzerland'])
    ->pipe($nodeA)
    ->pipe($splitWords)
    ->pipe($summaryDecision)
    ->yields(Cardinality::LIST, ContentType::TEXT);

// --- Combine: Parallele Verarbeitung mehrerer Pipes/Nodes ---
$combined = Foundry::combine('combinedResults')
    ->add($pipe)
    ->add($nodeB)
    ->add($searchNode);

// --- Graph erstellen ---
$graph = Foundry::graph('myGraph', ['input' => 'Some input'])
    ->withLogger($logger)
    ->node($pipe)        // Pipe als Node
    ->node($nodeB)
    ->node($searchNode)
    ->node($readNode)
    ->node($ttsNode);

// --- Ausführen ---
$results = $graph->run();

// Ergebnisse ausgeben
print_r([
    'nodeA' => $nodeA->getResult(),
    'splitWords' => $splitWords->getResult(),
    'summaryDecision' => $summaryDecision->getResult(),
    'nodeB' => $nodeB->getResult(),
    'searchResults' => $searchNode->getResult(),
    'fileContent' => $readNode->getResult(),
    'audioBook' => $ttsNode->getResult(),
    'combinedResults' => $combined->getResult(),
]);
```

---

## Designprinzipien

- **Nodes**, **Pipes** und **Graphen** sind jeweils First-Class Citizens mit eigenen, expliziten Typen und Interfaces und können als Objekte beliebig miteinander kombiniert werden.
- **Abhängigkeiten** werden ausschliesslich über Objekt-Referenzen definiert (Nodes, Pipes oder Graphen); String-IDs werden nicht verwendet.
- Das Framework erkennt und verhindert Zyklen im Abhängigkeitsgraphen (DAG), um fehlerhafte Workflows zu vermeiden.
- **Pipes** gruppieren Nodes sequenziell; der Output ist der Output des letzten Nodes.
- **Graphen** orchestrieren Nodes, Pipes und Kombinatoren zu komplexen Workflows und können selbst als Abhängigkeitsziele dienen.
- **Strategien** steuern, wie Input-Daten auf Abhängigkeiten angewendet werden (ganz oder pro Element).
- **Output-Contract** (Cardinality + ContentType) definiert den Output-Typ jedes Nodes.
- **Transform- und Decide-Nodes** erlauben flexible Datenumformung und bedingte Ablaufsteuerung.
- **Logger** können an Graphen oder Pipes angebunden werden, um Ausführung zu überwachen.
- **Templates** werden ausschliesslich in Nodes verwendet, interpolieren nur explizit deklarierte Inputs und erlauben keine impliziten Referenzen.
- **Verzweigungen** und **bedingte Ausführung** werden über `decide`-Nodes realisiert.
- **Asynchrone Ausführung** wird unterstützt, wo Adapter oder Node-Implementierungen dies ermöglichen.
- Die gesamte API ist explizit typisiert für sichere und nachvollziehbare Workflows.

## Interfaces

```php
<?php

// --- DataBag Interface ---
interface DataBagInterface
{
    public function set(string $key, mixed $value): static;
    public function get(string $key, mixed $default = null): mixed;
    public function has(string $key): bool;
    public function all(): array;
    public function remove(string $key): static;
    public function clear(): static;
    public function copy(): static; // vormals clone()
}

// --- Adapter Interface ---
interface AdapterInterface
{
    /**
     * Verarbeitet Input-Daten und gibt das Ergebnis zurück.
     * Wird von Node-Implementierungen verwendet, um externe APIs anzusprechen.
     */
    public function process(mixed $input): mixed;
}

// --- Cache Interface ---
interface CacheInterface
{
    public function get(string $key): mixed;
    public function set(string $key, mixed $value, int $ttlSeconds = 0): static;
    public function has(string $key): bool;
    public function delete(string $key): static;
    public function clear(): static;
}

// --- CacheStrategy Enum ---
enum CacheStrategy
{
    case DISABLED;
    case PER_RUN;
    case PER_INPUT;
    case GLOBAL;
}

// --- Executable Interface ---
/**
 * Basisschnittstelle für alle ausführbaren Workflow-Elemente (Node, Pipe, Graph, Combine, Transform).
 * Nur Nodes, Pipes, Graphen und Kombinatoren unterstützen Caching-Methoden.
 */
interface ExecutableInterface
{
    /**
     * Führt das Element aus. Optionaler Input.
     */
    public function run(mixed $input = null): mixed;

    /**
     * Deklariert den Output-Contract (Cardinality + ContentType).
     */
    public function yields(Cardinality $cardinality, ContentType $type): static;

    /**
     * Setzt einen Logger zur Überwachung.
     */
    public function withLogger(\Psr\Log\LoggerInterface $logger): static;

    /**
     * Liefert eine eindeutige ID für das Element.
     */
    public function getId(): string;

    /**
     * Übergibt einen DataBag als Kontext für die Ausführung.
     */
    public function withDataBag(DataBagInterface $dataBag): static;

    // Die folgenden Methoden sind nur für Nodes, Pipes, Graphen und Kombinatoren implementiert, nicht für DataBag:
    /**
     * Aktiviert und konfiguriert das Caching für dieses Element.
     */
    public function cache(CacheStrategy $strategy, int $ttlSeconds = 0): static;

    /**
     * Erzwingt die Neuberechnung und ignoriert den Cache.
     */
    public function fresh(): static;
}

// --- Node Interface ---
interface NodeInterface extends ExecutableInterface
{
    /**
     * Definiert eine Abhängigkeit zu einem anderen Workflow-Element mit Strategie.
     */
    public function dependsOn(ExecutableInterface $nodeOrPipeOrGraph, StrategyInterface $strategy): static;
}

// --- Pipe Interface ---
interface PipeInterface extends ExecutableInterface
{
    /**
     * Definiert Input-Daten für die Pipe.
     */
    public function input(array $data): static;

    /**
     * Fügt ein Element (Node, Pipe) zur Pipe hinzu.
     */
    public function pipe(ExecutableInterface $nodeOrPipe): static;
}

// --- Graph Interface ---
interface GraphInterface extends ExecutableInterface
{
    /**
     * Fügt ein Element (Node, Pipe, Graph, Combine) zum Graphen hinzu.
     */
    public function node(ExecutableInterface $nodeOrPipeOrGraph): static;
}

// --- Combine Interface ---
/**
 * Kombinator für parallele Ausführung von Executables.
 * Die hinzugefügten Executables werden parallel ausgeführt.
 * Die Ergebnisse können kombiniert, gemerged, gezippt oder gesammelt werden.
 */
interface CombineInterface extends ExecutableInterface
{
    /**
     * Fügt ein Executable zur parallelen Ausführung hinzu.
     * @param ExecutableInterface $executable
     * @return static
     */
    public function add(ExecutableInterface $executable): static;

    /**
     * Kombiniert die Ergebnisse mit einer Funktion.
     * @param callable $combineFn
     * @return static
     */
    public function combineWith(callable $combineFn): static;

    /**
     * Mergt die Ergebnisse aller Executables zu einer gemeinsamen Struktur (z.B. via array_merge).
     * @return static
     */
    public function merge(): static;

    /**
     * Zipped die Ergebnisse der Executables (z.B. wie array_map(null, ...)).
     * @return static
     */
    public function zip(): static;

    /**
     * Sammelt die Ergebnisse in einer Collection (z.B. als assoziatives Array).
     * @return static
     */
    public function collect(): static;
}

// --- Transform Interface ---
interface TransformInterface extends ExecutableInterface
{
    /**
     * Transformation per Mapping-Funktion.
     */
    public function map(callable $fn): static;

    /**
     * Transformation per Filter-Funktion.
     */
    public function filter(callable $fn): static;

    /**
     * Transformation per Reduce-Funktion.
     */
    public function reduce(callable $fn, mixed $initial): static;
}

// --- Decider Interface ---
interface DeciderInterface extends NodeInterface
{
    public function if(callable $condition, ExecutableInterface $target): static;
    public function elseif(callable $condition, ExecutableInterface $target): static;
    public function otherwise(ExecutableInterface $target): static; // vormals else()
}

// --- Strategy Interface ---
interface StrategyInterface
{
    public function apply(mixed $input, callable $fn): mixed;
}

// --- Filter Interface ---
interface FilterInterface
{
    public function apply(mixed $input): mixed;
}

// --- Cardinality & ContentType enums ---
enum Cardinality { case UNIT; case LIST; }
enum ContentType { case TEXT; case IMAGE; case AUDIO; case OBJECT; case FILE; case VECTOR; }

// --- Logger Interface ---
// Die eigene LoggerInterface entfällt, stattdessen PSR-3:
// use Psr\Log\LoggerInterface;
```

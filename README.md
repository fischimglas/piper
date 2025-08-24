# Piper – PHP DAG Framework für KI- und API-Workflows

**Status: PROTOTYP / In Entwicklung – API und Design können sich ändern.**

Piper ist ein modernes, leichtgewichtiges PHP-Framework zur Orchestrierung von **KI- und API-Workflows** auf Basis eines **gerichteten azyklischen Graphen (DAG)**. Es bietet explizite, typisierte Bausteine für Nodes, Pipes, Graphen und Kombinatoren, unterstützt deklarative Workflows und ermöglicht komplexe, modulare Datenverarbeitung – von einfachen KI-Textoperationen bis zu verzweigten, parallelen Pipelines.

---

## Inhaltsverzeichnis

- [Kernkonzepte](#kernkonzepte)
  - [Node](#node)
  - [Pipe](#pipe)
  - [Graph](#graph)
  - [Combine (Kombinator)](#combine-kombinator)
  - [Transform](#transform)
  - [Decide (Bedingte Ausführung)](#decide-bedingte-ausführung)
  - [DataBag (Kontextdaten)](#databag-kontextdaten)
  - [Caching](#caching)
- [API-Überblick](#api-überblick)
- [Beispiele](#beispiele)
- [Designprinzipien](#designprinzipien)
- [Interfaces](#interfaces)
- [Status](#status)

---

## Kernkonzepte

### Node

Eine **Node** ist die kleinste Verarbeitungseinheit (z.B. KI-Text, Bildgenerierung, Websuche, Datenbankabfrage). Sie erhält Input, verarbeitet ihn (ggf. mit Adapter/Filter), und gibt einen klar typisierten Output zurück. Nodes können explizite Abhängigkeiten zu anderen Nodes, Pipes oder Graphen besitzen.

### Pipe

Eine **Pipe** ist eine lineare Sequenz von Nodes. Sie transformiert Input Schritt für Schritt und gibt das Ergebnis des letzten Nodes als Output zurück. Pipes können als eigenständige Workflows oder als Teil von Graphen verwendet werden.

### Graph

Ein **Graph** orchestriert Nodes, Pipes und Kombinatoren zu komplexen Workflows. Er bildet die oberste Ebene, erkennt und verhindert Zyklen, und steuert die Ausführung.

### Combine (Kombinator)

Mit **Combine** können mehrere Nodes, Pipes oder Graphen parallel ausgeführt und deren Ergebnisse kombiniert, gemerged oder gezippt werden.

### Transform

**Transform**-Nodes dienen der Datenumformung (z.B. Mapping, Filtering, Reduktion) und können beliebige Prozessfunktionen enthalten.

### Decide (Bedingte Ausführung)

Mit **Decide**-Nodes lassen sich Workflows verzweigen: Bedingungen bestimmen, welcher Node/Pipe als nächstes ausgeführt wird (`if`, `elseif`, `otherwise`).

### DataBag (Kontextdaten)

Der **DataBag** ist eine typisierte, strukturierte Datenablage für Kontextdaten, die während der Ausführung zwischen Nodes, Pipes und Graphen transportiert werden.

### Caching

Nodes, Pipes, Graphen und Kombinatoren unterstützen konfigurierbare Caching-Strategien (`CacheStrategy`), um teure Berechnungen oder API-Aufrufe zu vermeiden.

---

## API-Überblick

### Fluent API

- **Graphen:**  
  `Foundry::graph('name', [...inputs])`
- **Pipes:**  
  `Foundry::pipe('name')->pipe($node)->pipe($otherNode)`
- **Nodes:**  
  `Foundry::text('nodeName')->template('...')->yields(Cardinality::UNIT, ContentType::TEXT)`
- **Transform:**  
  `Foundry::transform('name')->process(fn($input) => ...)`
- **Decide:**  
  `Foundry::decide('name')->if(fn($input) => ..., $node)->otherwise($otherNode)`
- **Combine:**  
  `Foundry::combine('name')->add($pipe)->add($node)->merge()`
- **DataBag:**  
  `->withDataBag($dataBag)`
- **Caching:**  
  `->cache(CacheStrategy::PER_INPUT, 3600)`, `->fresh()`
- **Logger:**  
  `->withLogger($logger)`

### Output-Contract

Jede Node/Pipe/Graph deklariert ihren Output explizit mit  
`->yields(Cardinality::UNIT|LIST, ContentType::TEXT|IMAGE|AUDIO|OBJECT|FILE|VECTOR)`

### Abhängigkeiten

Abhängigkeiten werden **immer** über Objekt-Referenzen gesetzt:  
`->dependsOn($nodeOrPipe, Strategy::WHOLE|PER_ITEM)`

---

## Beispiele

### 1. Einfache Text-Node

```php
$nodeA = Foundry::text('nodeA')
    ->template('Summarize this: {{input}}')
    ->yields(Cardinality::UNIT, ContentType::TEXT);

$result = $nodeA->run(['input' => 'Bern, Switzerland']);
```

### 2. Pipe mit mehreren Verarbeitungsschritten

```php
$pipe = Foundry::pipe('myPipe')
    ->input(['input' => 'Bern, Switzerland'])
    ->pipe(
        Foundry::text('nodeA')
            ->template('Summarize this: {{input}}')
            ->yields(Cardinality::UNIT, ContentType::TEXT)
    )
    ->pipe(
        Foundry::transform('splitWords')
            ->process(fn(string $input) => explode(' ', $input))
            ->yields(Cardinality::LIST, ContentType::TEXT)
    )
    ->yields(Cardinality::LIST, ContentType::TEXT);

$result = $pipe->run();
```

### 3. Bedingte Ausführung (Decide)

```php
$summaryDecision = Foundry::decide('summaryDecision')
    ->if(fn($input) => count($input) > 5, $nodeA)
    ->elseif(fn($input) => count($input) > 2, $splitWords)
    ->otherwise($nodeA);

$result = $summaryDecision->run(['input' => [...]]); 
```

### 4. Parallele Ausführung mit Combine

```php
$combined = Foundry::combine('combinedResults')
    ->add($pipe)
    ->add($nodeB)
    ->merge();

$result = $combined->run();
```

### 5. Komplexer Graph

```php
$graph = Foundry::graph('myGraph', ['input' => 'Some input'])
    ->withLogger($logger)
    ->node($pipe)
    ->node($nodeB)
    ->node($searchNode)
    ->node($ttsNode);

$results = $graph->run();
```

### 6. DataBag-Nutzung

```php
$dataBag = new DataBag();
$dataBag->set('userId', 123);

$result = $nodeA->withDataBag($dataBag)->run();
```

### 7. Caching

```php
$nodeB = Foundry::text('nodeB')
    ->template('Expensive operation')
    ->yields(Cardinality::UNIT, ContentType::TEXT)
    ->cache(CacheStrategy::PER_INPUT, 3600);

$result = $nodeB->run(['input' => 'Some input']);
$freshResult = $nodeB->fresh()->run(['input' => 'Some input']);
```

---

## Designprinzipien

- **Explizite Typisierung:** Nodes, Pipes, Graphen und Kombinatoren sind eigene, explizite Objekte mit klaren Interfaces.
- **Objekt-Referenzen:** Abhängigkeiten werden ausschließlich über Objekt-Referenzen definiert, nie über String-IDs.
- **Zyklenerkennung:** Das Framework erkennt und verhindert Zyklen im DAG.
- **Deklarative, fluente API:** Workflows werden deklarativ und lesbar definiert.
- **Output-Contract:** Jeder Verarbeitungsschritt deklariert explizit Output-Kardinalität und -Typ.
- **Flexible Komposition:** Kombinieren, verzweigen, transformieren und parallelisieren von Workflows ist einfach möglich.
- **Kontextdaten:** DataBag transportiert Kontextdaten workflow-weit.
- **Caching:** Konfigurierbare Caching-Strategien für Nodes, Pipes, Graphen und Kombinatoren.
- **Logger:** PSR-3 Logger können für Monitoring und Fehlerbehandlung angebunden werden.
- **Events:** Nodes unterstützen Event-Handler für `BEFORE_RUN`, `AFTER_RUN`, `ON_ERROR`.

---

## Interfaces

```php
// ...siehe design.md für vollständige Interface-Definitionen...
interface DataBagInterface { /* ... */ }
interface CacheInterface { /* ... */ }
enum CacheStrategy { DISABLED, PER_RUN, PER_INPUT, GLOBAL }
interface ExecutableInterface { /* ... */ }
interface NodeInterface extends ExecutableInterface { /* ... */ }
interface PipeInterface extends ExecutableInterface { /* ... */ }
interface GraphInterface extends ExecutableInterface { /* ... */ }
interface CombineInterface extends ExecutableInterface { /* ... */ }
interface TransformInterface extends ExecutableInterface { /* ... */ }
interface DeciderInterface extends NodeInterface { /* ... */ }
interface StrategyInterface { /* ... */ }
interface FilterInterface { /* ... */ }
enum Cardinality { UNIT, LIST }
enum ContentType { TEXT, IMAGE, AUDIO, OBJECT, FILE, VECTOR }
// Logger: PSR-3 LoggerInterface
```

---

## Status

Piper befindet sich in aktiver Entwicklung. Das Design und die API können sich ändern. Feedback und Beiträge sind willkommen!

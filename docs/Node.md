# Entwicklerdokumentation: Node

## Übersicht

Eine **Node** ist die kleinste, ausführbare Verarbeitungseinheit im Piper-Framework. Sie kapselt eine Operation (z.B. KI-Text, Bildgenerierung, Datenbankabfrage, Transformation) mit klar definierten Inputs, Outputs und Abhängigkeiten. Nodes sind explizite Objekte mit eigenem Interface und können beliebig kombiniert, sequenziert oder in Graphen orchestriert werden.

## Kernfunktionen

- **Verarbeitungseinheit:** Führt eine einzelne Operation aus (z.B. Text-Template, API-Call, Datenumformung).
- **Abhängigkeiten:** Kann von anderen Nodes, Pipes oder Graphen abhängen (explizit über Objekt-Referenzen).
- **Output-Contract:** Deklariert explizit den Output-Typ und die Kardinalität (`yields()`).
- **Templates:** Unterstützt Template-Interpolation für Inputs und Abhängigkeiten.
- **Events:** Unterstützt Event-Handler für Lebenszyklusereignisse (`BEFORE_RUN`, `AFTER_RUN`, `ON_ERROR`).
- **Caching:** Unterstützt konfigurierbare Caching-Strategien.
- **Kontextdaten:** Kann mit einem DataBag ausgeführt werden.

## Interface

```php
interface NodeInterface extends ExecutableInterface
{
    public function dependsOn(ExecutableInterface $nodeOrPipeOrGraph, StrategyInterface $strategy): static;
    // ...weitere Methoden siehe ExecutableInterface...
}
```

## Erstellung und Konfiguration

Nodes werden typischerweise über Factory-Methoden erstellt, z.B.:

```php
$node = Foundry::text('summarizer')
    ->template('Summarize: {{input}}')
    ->yields(Cardinality::UNIT, ContentType::TEXT);
```

### Abhängigkeiten

Abhängigkeiten werden **immer** über Objekt-Referenzen gesetzt, nie über Strings:

```php
$nodeB = Foundry::text('analyzer')
    ->template('Analyze: {{summarizer}}')
    ->dependsOn($node, Strategy::WHOLE)
    ->yields(Cardinality::UNIT, ContentType::TEXT);
```

### Output-Contract

Jede Node muss ihren Output deklarieren:

```php
$node->yields(Cardinality::UNIT, ContentType::TEXT);
```

## Templates

Templates interpolieren deklarierte Inputs und Abhängigkeiten:

```php
$node = Foundry::text('report')
    ->template('Report for {{input}} and {{analyzer}}')
    ->dependsOn($analyzer, Strategy::WHOLE);
```

## Events

Nodes unterstützen Event-Handler für folgende Events:

| Event-Konstante     | Beschreibung                                                                                   |
|---------------------|-----------------------------------------------------------------------------------------------|
| `Event::BEFORE_RUN` | Vor Ausführung der Node                                                                       |
| `Event::AFTER_RUN`  | Nach erfolgreicher Ausführung                                                                 |
| `Event::ON_ERROR`   | Bei Fehler während der Ausführung (Fehlerbehandlung, Fortsetzen/Abbrechen steuerbar)          |

**Beispiel:**

```php
$node->on(Event::BEFORE_RUN, function($context) {
    // Vorbereitende Aktionen
});
$node->on(Event::AFTER_RUN, function($context, $result) {
    // Nachbearbeitung
});
$node->on(Event::ON_ERROR, function($context, \Throwable $error, $continue, $abort) {
    // Fehlerbehandlung
    $continue(); // oder $abort();
});
```

## Caching

Nodes unterstützen Caching-Strategien:

```php
$node->cache(CacheStrategy::PER_INPUT, 3600); // Pro Input für 1h cachen
$node->fresh(); // Ignoriert Cache, erzwingt Neuberechnung
```

## Kontextdaten (DataBag)

Nodes können mit einem DataBag ausgeführt werden:

```php
$dataBag = new DataBag();
$dataBag->set('userId', 123);
$node->withDataBag($dataBag)->run();
```

## Beispiel: Komplexe Node mit Abhängigkeiten und Events

```php
$summary = Foundry::text('summary')
    ->template('Summarize: {{input}}')
    ->yields(Cardinality::UNIT, ContentType::TEXT);

$analysis = Foundry::text('analysis')
    ->template('Analyze: {{summary}}')
    ->dependsOn($summary, Strategy::WHOLE)
    ->on(Event::AFTER_RUN, function($context, $result) {
        // Logging oder Monitoring
    })
    ->yields(Cardinality::UNIT, ContentType::TEXT);

$result = $analysis->run(['input' => 'Bern, Switzerland']);
```

## Best Practices

- **Abhängigkeiten immer explizit über Objekte setzen.**
- **Output-Contract deklarieren** für sichere Komposition.
- **Templates nur mit explizit deklarierten Inputs/Abhängigkeiten verwenden.**
- **Events nutzen** für Logging, Monitoring und Fehlerbehandlung.
- **Caching gezielt einsetzen** für teure Operationen.
- **DataBag verwenden** für Kontextdaten, nicht für globale Zustände.

## Hinweise

- Nodes sind wiederverwendbar und können in mehreren Pipes/Graphen eingesetzt werden.
- Die Ausführung ist typisiert und kann synchron oder asynchron erfolgen (je nach Adapter).
- Fehler in Nodes können über Events behandelt werden, ohne den gesamten Workflow zu unterbrechen.

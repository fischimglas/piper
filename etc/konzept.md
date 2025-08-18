# AI Prompt Sequencer – Vollständiges Architektur- und Design-Dokument

## 1. Ziel
Der AI Prompt Sequencer ist ein Framework, um AI-Aufgaben in **sequenziellen** oder **verzweigten Abläufen** zu orchestrieren.
Er unterstützt mehrere AI-Anbieter (z. B. OpenAI, Google) und verschiedene Prompt-Typen (Text, Arrays/Listen).

## 2. Grundprinzipien
- **Sequences**: Einzelne Arbeitsschritte mit AI-Clients und klar definierten Ein- und Ausgaben.
- **Sequence Trees**: Abhängigkeiten zwischen Sequenzen werden als Baumstruktur modelliert.
- **Parallele Ausführung**: Unabhängige Sequenzen können gleichzeitig laufen.
- **Strategien für Listen**: Unterschiedliche Modi (z. B. per Item, Batch).
- **Mehrere AI-Clients**: Jede Sequenz kann mit einem anderen Anbieter/Modell arbeiten.
- **Erweiterbarkeit**: Neue Strategien, Sequenztypen oder Clients können leicht hinzugefügt werden.

## 3. Hauptkomponenten
### 3.1 `Sequence`
- Abstrakte Basisklasse für alle Sequenzen.
- Enthält:
    - `id`: eindeutiger Bezeichner
    - `client`: AI-Client-Instanz
    - `dependencies`: Liste von Sequenzen, die vor dieser ausgeführt werden müssen
    - `strategy`: Ausführungsstrategie (z. B. pro Element)
    - `type`: Datentyp der Ausgabe (`text` oder `array`)
    - `template`: Optionales Prompt-Template
- Methoden:
    - `run(context)`: Führt den Prompt aus
    - `addDependency(sequence)`: Definiert eine Abhängigkeit

### 3.2 `AIClient`
- Schnittstelle für AI-Anbieter (OpenAI, Google, etc.)
- Methoden:
    - `sendPrompt(template, variables)`
    - Implementierungen: `OpenAIClient`, `GoogleAIClient`

### 3.3 `ExecutionStrategy`
- Strategy-Pattern für Ausführungslogik:
    - `PerItemStrategy`: Bearbeitet Arrays Element für Element
    - `BatchStrategy`: Bearbeitet alle Elemente gleichzeitig
    - `SingleShotStrategy`: Einzelner Prompt für alles
- Jede Strategie ist austauschbar

### 3.4 `SequenceRunner`
- Orchestriert die Ausführung
- Führt Sequenzen in richtiger Reihenfolge aus
- Erkennt parallele Pfade
- Wartet auf Abhängigkeiten

## 4. Abhängigkeitslogik
- Eine Sequenz kann mehrere Abhängigkeiten haben.
- Ein Task wird erst gestartet, wenn **alle** Abhängigkeiten abgeschlossen sind.
- Beispiel:
```
A → B
A → C
B + C → D
```
→ A läuft zuerst, dann B und C parallel, dann D.

## 5. Beispiel: Einfacher Einsatz
```php
$seqA = new TextSequence('A', $openAI, 'Prompt für A');
$seqB = new ArraySequence('B', $googleAI, 'Prompt für B');
$seqC = new TextSequence('C', $openAI, 'Prompt für C');
$seqD = new TextSequence('D', $openAI, 'Prompt für D');

$seqB->dependsOn($seqA);
$seqC->dependsOn($seqA);
$seqD->dependsOn($seqB, $seqC);

$runner = new SequenceRunner();
$runner->add($seqA, $seqB, $seqC, $seqD);
$runner->run();
```

## 6. Listen-Modi
- **PerItem**: Für jedes Element wird eine eigene Anfrage gesendet.
- **Batch**: Alle Elemente in einem Prompt.
- **Hybrid**: Gruppenweise Verarbeitung.

## 7. Mehrere AI-Clients
- Jede Sequenz erhält ihre eigene Client-Instanz mit Modellkonfiguration.
- Vorteil: Unterschiedliche Modelle/Anbieter pro Schritt.

## 8. Erweiterungen
- Caching von Zwischenergebnissen
- Fehler-Handling & Retry-Mechanismen
- Event Hooks (onStart, onFinish, onError)
- Live-Streaming von AI-Antworten
- Unterstützung für nicht-AI-Schritte (z. B. API-Aufrufe, Datenbank)

## 9. Strategische Entscheidungen
- **Datentypen in Sequenzen festgelegt** (Text oder Array) → klare Verarbeitung
- **Model pro Sequenz** → maximale Flexibilität
- **Strategie statt Flag** → sauberes Design
- **Tree- statt Linear-Execution** → komplexe Abhängigkeiten möglich

## 10. Beispiel: Komplexe Abhängigkeiten + verschiedene Clients
```php
$seqA = new TextSequence('A', new OpenAIClient('gpt-4'), 'Prompt A');
$seqB = new ArraySequence('B', new GoogleAIClient('gemini-pro'), 'Prompt B');
$seqC = new ArraySequence('C', new OpenAIClient('gpt-4o'), 'Prompt C');
$seqD = new TextSequence('D', new OpenAIClient('gpt-4'), 'Prompt D');

$seqB->dependsOn($seqA);
$seqC->dependsOn($seqA);
$seqD->dependsOn($seqB, $seqC);

$runner = new SequenceRunner();
$runner->add($seqA, $seqB, $seqC, $seqD);
$runner->run();
```

---
© 2025 Prompt Sequencer Design Draft v1.0

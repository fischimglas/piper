# Entwicklerdokumentation: Strategy

## Übersicht

Das Strategy-Konzept in Piper steuert, **wie** und **auf welcher Granularität** ein Verarbeitungsschritt (z.B. Node, Pipe, Transform) auf Input-Daten angewendet wird. Strategien sind insbesondere für Abhängigkeiten (`dependsOn`) und Transformationsschritte relevant.

## Kern-Strategien

Piper liefert folgende Standard-Strategien als Enum `Strategy`:

| Wert      | Bedeutung                                 |
|-----------|-------------------------------------------|
| WHOLE     | Wendet die Funktion auf den gesamten Input an. |
| PER_ITEM  | Wendet die Funktion auf jedes Element einer Liste an. |
| REDUCE    | Reduziert eine Liste auf einen Wert mittels Funktion. |

## Interface

```php
interface StrategyInterface
{
    public function apply(mixed $input, callable $fn): mixed;
}
```

## Implementierung (Enum)

```php
enum Strategy implements StrategyInterface
{
    case WHOLE;
    case PER_ITEM;
    case REDUCE;

    public function apply(mixed $input, callable $fn): mixed
    {
        return match ($this) {
            self::WHOLE => $fn($input),
            self::PER_ITEM => is_iterable($input)
                ? array_map(fn($item) => $fn($item), is_array($input) ? $input : iterator_to_array($input))
                : $fn($input),
            self::REDUCE => is_iterable($input)
                ? array_reduce(
                    is_array($input) ? $input : iterator_to_array($input),
                    fn($carry, $item) => $fn($item),
                    null
                )
                : $fn($input),
        };
    }
}
```

## Anwendungsbeispiele

### 1. WHOLE

```php
$node->dependsOn($otherNode, Strategy::WHOLE);
// $fn wird auf das gesamte Ergebnis von $otherNode angewendet
```

### 2. PER_ITEM

```php
$node->dependsOn($listNode, Strategy::PER_ITEM);
// $fn wird auf jedes Element der Liste von $listNode angewendet
```

### 3. REDUCE

```php
$node->dependsOn($listNode, Strategy::REDUCE);
// $fn reduziert die Liste von $listNode auf einen Wert
```

## Eigene Strategien implementieren

Eigene Strategien können durch Implementierung von `StrategyInterface` erstellt werden:

```php
class CustomStrategy implements StrategyInterface
{
    public function apply(mixed $input, callable $fn): mixed
    {
        // Eigene Logik
    }
}
```

## Hinweise

- Strategien werden ausschließlich über Objekt-Referenzen (Enum oder Klasse) gesetzt, niemals über Strings.
- Die Wahl der Strategie beeinflusst, wie Abhängigkeiten und Transformationsschritte ausgeführt werden.
- Die Standardstrategien decken die meisten Anwendungsfälle ab, können aber beliebig erweitert werden.

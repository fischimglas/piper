- pipe($sequence) links parent/child sequences (linear flow).
- Dependencies (cross-links, or even whole Pipes) are tracked as DAG edges.
- Pipe becomes essentially a workflow executor (not just a linear pipe).


1. Pipe

Currently:

- Holds $sequences (linear list) and $pipes (other pipes).
- pipe() just appends to $sequences.
- run() reduces $sequences in order, trying to apply dependencies via gatherDependencyResults.
- topologicalSort() tries to sort $pipes but is buggy ($this in closure).

Needed:

- pipe($sequence) must link last sequence → new sequence. That way we maintain explicit parent/child edges.
- Dependencies need to be integrated into the graph, not just gathered as arrays.
- run() must execute sequences in topological order (DAG). Right now, it only does left-to-right.
- $pipes (nested pipes) can be unified into the same graph model instead of special-casing.
- topologicalSort() must work across all sequences and dependencies, not only pipes.

Action Items:

- Replace $sequences linear array with a graph representation (adjacency list or just edges stored in each Sequence).
- Fix topologicalSort → use Sequence::getDependencies() and pipe() edges.
- Allow run() to execute whole DAG (topo order).

⸻

2. Sequence

Currently:

- Holds adapter, strategy, template, filters, dependencies, data.
- resolve() executes template → adapter → filter, logs result.
- Dependencies are just stored as Dependency objects.

Needed:

- Dependencies must reference other Sequences (or Pipes) directly, not just arbitrary objects.
- Right now, addDependency() stores Dependency objects, but these are a wrapper over Pipe. That is inconsistent (why
  should dependencies wrap Pipes if they’re really Sequence-to-Sequence links?).
- getDependencies() should return an array of child sequences (graph edges), not wrappers.
- resolve() should also receive resolved inputs from dependencies (gathered via Pipe::run).

Action Items:

- Redesign Dependency (or drop it entirely). Instead: Sequence::addDependency(Sequence $seq).
- Keep strategies but attach them at Sequence level, not inside Dependency.
- Ensure resolve() can hydrate inputs with both direct parent and dependency results.

⸻

3. Dependency

Currently:

- Wraps a Pipe and a Strategy.
- Adds alias + placeholder for getResult().
- But right now, it’s redundant: you’re creating an extra layer instead of storing Sequence-to-Sequence edges directly.

Needed:

- Decide: do we need a separate Dependency class?
- If dependencies are always “Sequence → Sequence”, you can drop Dependency entirely and just store edges directly.
- If dependencies can carry metadata (alias, strategy), then Dependency is useful. But it should wrap a Sequence, not a
  Pipe.

Action Items:

- Either remove Dependency and fold it into Sequence::dependencies.
- Or rework Dependency to hold Sequence $target instead of Pipe $pipe.

⸻

## Overview of Concrete Changes

1. Pipe

- pipe($sequence) → link lastSequence->addChild($sequence) instead of just pushing.
- run() → perform topological sort of sequences (based on both pipe-links and dependencies).
- $pipes (nested pipes) → unify with sequences as DAG nodes.

2. Sequence

- Change dependencies from Dependency[] → Sequence[] (or at least make Dependency reference Sequence, not Pipe).
- resolve() must gather dependency results as input.

3. Dependency (optional)

- Rework: replace Pipe property with Sequence.
- If strategy-per-dependency is important, keep it; otherwise, inline into Sequence.

4. General

- Implement a robust DAG executor (topological sort + cycle detection).
- Ensure getResult() always returns the computed value so it can be consumed by dependents.

## Migration Path 

- Step 1: Change Dependency to wrap Sequence instead of Pipe.
- Step 2: Adjust Sequence::addDependency() to accept sequences, not pipes.
- Step 3: Update Pipe::run() to do topo sort of all sequences (using dependencies + pipe-links).
- Step 4: Remove $pipes from Pipe once nesting is redundant.

⸻

## End result:

- Pipe = orchestrator for DAG execution.
- Sequence = node in DAG.
- Dependency = optional metadata around edges.
- Everything runs via topological sort, making complex flows possible but preserving pipe() syntax for linear cases.

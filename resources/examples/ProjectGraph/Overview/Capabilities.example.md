# Review Graph Capabilities

## Graph
- `ai:task` is the task-first entry point when available.
- `ai:review-graph:generate` builds or refreshes the graph when fresh graph context is needed.
- `ai:review-graph:stats` verifies graph health and freshness after an explicit refresh.
- `ai:review-graph:show` exports focused graph views.

## Introspection
- `ai:review-graph:query` answers dependency and usage questions.
- `ai:review-graph:capabilities` projects graph data into an AI-friendly manifest.

## Operations
- `ai:review-graph:impact` scopes the blast radius before edits.
- `ai:review-graph:watch` keeps the graph current during active work.

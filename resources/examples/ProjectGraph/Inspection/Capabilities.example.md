## Intelligence-first inspection

```bash
bin/semitexa ai:review-graph:module Demo --include-events --include-flows --format=json
bin/semitexa ai:review-graph:intelligence --hotspots
bin/semitexa ai:review-graph:context "review Demo module coupling" --format=json
```

Use `module` when you need one packageable view of a subsystem instead of several small queries.

Use `intelligence` and `context` when the right answer is explanatory or task-scoped rather than a raw edge list.

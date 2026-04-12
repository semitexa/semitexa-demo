## Prompt packaging

```bash
bin/semitexa ai:review-graph:impact Semitexa\\Demo\\Application\\Service\\DemoCatalogService --context
bin/semitexa ai:review-graph:impact Semitexa\\Demo\\Application\\Service\\DemoCatalogService --context --prompt=review
bin/semitexa ai:review-graph:impact Semitexa\\Demo\\Application\\Service\\DemoCatalogService --context --prompt=refactor
```

The point is not to produce a giant prompt. The point is to produce a smaller prompt that is structurally justified by the impacted graph.

bin/semitexa ai:review-graph:query --search=DemoCatalogService
bin/semitexa ai:review-graph:query --dependencies=Semitexa\\Demo\\Application\\Service\\DemoCatalogService
bin/semitexa ai:review-graph:query --usages=Semitexa\\Demo\\Application\\Service\\DemoCatalogService
bin/semitexa ai:review-graph:query --cross-module --from=Demo --to=Core
bin/semitexa ai:review-graph:module Demo --include-events --include-flows --format=json
bin/semitexa ai:review-graph:intelligence --hotspots

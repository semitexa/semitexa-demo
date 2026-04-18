bin/semitexa ai:task "describe the task you want to work on"
# Refresh the graph only if task-scoped graph answers are needed and may be stale.
bin/semitexa ai:review-graph:generate --json
bin/semitexa ai:review-graph:stats --json
bin/semitexa ai:review-graph:capabilities --markdown

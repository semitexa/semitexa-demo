bin/semitexa ai:task "trace checkout architecture"
# If semitexa-project-graph is enabled and the stored graph may be stale, refresh it.
bin/semitexa ai:review-graph:generate --json
bin/semitexa ai:review-graph:stats --json
# Then choose the narrowest structural surface that answers the task.
bin/semitexa ai:review-graph:context "trace checkout architecture" --format=json
bin/semitexa ai:review-graph:show --format=markdown --module=Demo

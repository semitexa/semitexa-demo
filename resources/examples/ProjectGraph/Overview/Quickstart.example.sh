bin/semitexa ai:task "trace checkout architecture"
# The next commands require semitexa-project-graph to be installed and enabled.
# Refresh only when the stored graph may be stale.
bin/semitexa ai:review-graph:generate --json
bin/semitexa ai:review-graph:stats --json
# Then choose the narrowest structural surface that answers the task.
bin/semitexa ai:review-graph:context "trace checkout architecture" --format=json
bin/semitexa ai:review-graph:show --format=markdown --module=Demo

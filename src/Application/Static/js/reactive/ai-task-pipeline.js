/**
 * reactive/ai-task-pipeline.js
 * Renders the AI pipeline stage visualization in the reactive AI task slot.
 * Each stage node lights up as stageResults are received.
 * Triggered on `semitexa:block:rendered` for handle `demo_reactive_ai`.
 */
(function () {
    'use strict';

    var STAGE_ORDER = ['parse', 'analyze', 'generate', 'format'];

    function revealStage(stageEl, result, delay) {
        setTimeout(function () {
            stageEl.classList.remove('pipeline-stage--pending');
            stageEl.classList.add('pipeline-stage--done');

            if (result) {
                var detail = stageEl.querySelector('[data-stage-detail]');
                if (detail) {
                    detail.textContent = result.tokens + ' tokens · ' + result.ms + 'ms';
                    detail.hidden = false;
                }
            }
        }, delay);
    }

    function initPipeline(root) {
        var stagesJson = root.querySelector('[data-stage-results-json]');
        if (!stagesJson) return;

        var stageResults = {};
        try {
            stageResults = JSON.parse(stagesJson.getAttribute('data-stage-results-json') || '{}');
        } catch (e) {
            return;
        }

        STAGE_ORDER.forEach(function (stageName, i) {
            var stageEl = root.querySelector('[data-pipeline-stage="' + stageName + '"]');
            if (!stageEl) return;

            var result = stageResults[stageName];
            if (result && result.status === 'done') {
                revealStage(stageEl, result, i * 150);
            }
        });

        // Status badge
        var statusBadge = root.querySelector('[data-pipeline-status]');
        var completedCount = STAGE_ORDER.filter(function (stageName) {
            var result = stageResults[stageName];
            return result && result.status === 'done';
        }).length;
        if (statusBadge && completedCount >= STAGE_ORDER.length) {
            statusBadge.textContent = 'Completed';
            statusBadge.className = 'badge badge--success';
        }
    }

    document.addEventListener('semitexa:block:rendered', function (e) {
        if (e.detail && e.detail.handle === 'demo_reactive_ai') {
            var slot = document.querySelector('[data-slot="reactive_ai_task"]');
            if (slot) initPipeline(slot);
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        var slot = document.querySelector('[data-slot="reactive_ai_task"]');
        if (slot) initPipeline(slot);
    });
}());

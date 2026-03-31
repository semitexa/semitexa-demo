<?php

declare(strict_types=1);

$component = ComponentRegistry::get('product-spotlight-card');

if (($component['script'] ?? null) !== null) {
    $collector->require('ssr:js:component-runtime');
    $collector->require($component['script']);
}

$html = $twig->render($component['template'], $props);
$html = ComponentEventBridge::annotateRoot($html, $component, $componentId);

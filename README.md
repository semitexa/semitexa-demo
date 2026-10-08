# Semitexa Demo

Official showcase module demonstrating Semitexa framework capabilities.

## Purpose

Provides working examples of framework features across all major packages. Each demo is a standard Semitexa page — `#[AsPublicPayload]` (or `#[AsProtectedPayload]`) on the request DTO, `#[AsPayloadHandler]` on the handler — and demonstrates best practices for building with Semitexa.

## Install

Not included by the installer. Add it from the project root:

```bash
bin/semitexa demo:install
bin/semitexa server:restart
bin/semitexa orm:sync
```

`demo:install` runs `composer require semitexa/demo` inside the app container. The demo pages live under `/demo/…` (for example `/demo/get-started/installation`). The demo home page is its `/` route; in a new project the starter Hello module (`src/modules/Hello`) takes precedence at `/` until you remove it. Remove the demo with `composer remove semitexa/demo` inside the container.

## Role in Semitexa

Depends on all major packages including Core, SSR, ORM, Auth, Authorization, RBAC, API, GraphQL, Tenancy, Locale, Cache, Mail, Scheduler, Workflow, Storage, Search, and Testing. Serves as a living reference for developers.

## Key Features

- UI components: ExpandableSection, CodeBlock, FeatureCard, ExplanationTooltip
- Full payload/handler/response examples for each package
- Cross-package integration demonstrations

## Notes

This module is intended for reference and learning. It is not required for production applications.

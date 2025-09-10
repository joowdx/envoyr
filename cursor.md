## Scalable Architecture & Tracking (ASCII)

### Goals

- Scalability: isolate bounded contexts, stateless app nodes, async work off the request path.
- Observability: consistent IDs, event logs, metrics, traces.
- Proper tracking: end-to-end document journey, transmittals, attachments, processing, and user actions.

### Scalable Logical Architecture

```
                     ┌───────────────────────────────────────────────────┐
                     │                   Clients                         │
                     │  Filament Admin, API Consumers, Web              │
                     └───────────────▲───────────────────────────────────┘
                                     │ HTTPS
                           ┌─────────┴──────────┐
                           │  Web/App Gateways  │  (Nginx/Load Balancer)
                           └─────────▲──────────┘
                                     │
                         ┌───────────┴───────────┐
                         │ Laravel App (Stateless)│  horizontally scalable
                         │ - Filament UI          │
                         │ - HTTP Controllers     │
                         │ - Domain Services      │
                         │ - Jobs/Events/Listeners│
                         └───────┬───────┬────────┘
                                 │       │
                 Async Jobs ─────┘       └────── Observability
                                 │                 (Logs, Metrics, Traces)
                                 ▼
                     ┌──────────────────────────┐
                     │      Queue Workers        │ (Horizon/Queue)
                     │ - File ops (purge, hash)  │
                     │ - Notifications/Emails    │
                     │ - Heavy transformations    │
                     └───────┬───────────────────┘
                             │
          ┌──────────────────┴──────────────────┐
          │                                     │
┌─────────▼─────────┐                 ┌─────────▼──────────┐
│  Relational DB     │                 │   Object Storage    │
│  (ULIDs, FK, idx)  │                 │ (S3/local, version) │
└────────────────────┘                 └─────────────────────┘
```

Key principles:
- Stateless app tier; cache sessions (database/session driver OK; Redis preferred for scale).
- Offload heavy work to queues to keep request latency low.
- Storage abstraction through `Storage` facade; versioned objects for immutability where needed.

### Domain Contexts

```
Core: Document, Transmittal, Attachment, Content
Directory: Office, Section, User
Catalog: Classification, Source, Tag, Status
Tracking: Process (document/transmittal/user/status stamps)
```

### End-to-End Tracking Model

```
Correlation IDs
  - document_id (ULID) is the primary correlation key
  - transmittal_id chains movements
Event Log (append-only)
  - processes: (document_id, transmittal_id, user_id, status, processed_at)
  - usage: audit trail, analytics, SLAs
State Views
  - Document.publish()/unpublish() → state on document
  - Transmittal.received_at → active/inactive
Derived Views (materialized or query)
  - activeTransmittal(document_id)
  - latestStatus(document_id)
  - movementHistory(document_id)
```

### ASCII ERD (Scalable Tracking Emphasis)

```
documents(id, code, title, classification_id, user_id, office_id, section_id, source_id, published_at, deleted_at)
  1 ──< transmittals(id, code, document_id, from_office_id, to_office_id, from_section_id?, to_section_id?, from_user_id?, to_user_id?, liaison_id?, received_at)
  1 ──< attachments(id, document_id, transmittal_id?)  1 ──< contents(id, attachment_id, sort, title, file, path, hash, context)
  1 ──< labels(id, document_id, tag_id) >── 1 tags(id, name)
  1 ──< processes(id, document_id, transmittal_id, user_id, status, processed_at)

Directory: offices(id, ...), sections(id, office_id, user_id?, head_name?, designation?)
Catalog: classifications(id, ...), sources(id, ...), statuses(id, title, classification_id, office_id)
Users: users(id, role, office_id, section_id, ...)
```

Indexes to add (if not present):
- documents: (office_id, created_at), (office_id, deleted_at), (published_at)
- transmittals: (to_office_id, received_at), (document_id, received_at), (from_office_id, created_at)
- processes: (document_id, processed_at), (transmittal_id, processed_at), (user_id, processed_at)

### Message Flows (Scalable)

```
Create Document
  UI → HTTP → Document::creating → code generated
  → Persist → Queue: post-create hooks (search indexing, notify)

Attach Files (Draft)
  UI uploads → Attachment(draft) → Content rows → Queue: virus scan/hash

Create Transmittal
  UI → HTTP → Transmittal::creating → code generated
  → Persist → Process(event: "transmittal.created")
  → Queue: notify receiver, update dashboards

Receive Transmittal
  UI → HTTP → set received_at
  → Process(event: "transmittal.received")

Publish/Unpublish Document
  UI → HTTP → toggle published_at
  → Process(event: "document.published"/"document.unpublished")
```

### Observability & Auditing

```
Logging
  - Structure logs with correlation: document_id, transmittal_id, user_id
  - Channel: daily/files + external aggregator (e.g., ELK)

Metrics (Prometheus friendly)
  - document_created_total, transmittal_created_total
  - transmittal_receive_duration_seconds (from created_at to received_at)
  - attachments_total, contents_total

Tracing
  - OpenTelemetry SDK → spans around controller, job, storage ops
```

### Horizontal Scale Considerations

```
- Queue workers autoscale; set concurrency per worker type
- Database: use read replicas for analytics; write-primary for OLTP
- Caching: Redis for rate limiting, caching computed views
- Idempotency keys on create endpoints (prevent duplicates)
- Large files: direct-to-storage uploads (pre-signed URLs) to offload app servers
```

### Data Retention & Lifecycle

```
- Soft deletes already in place (documents, offices, sections, users)
- Content purge on attachment delete (already implemented)
- Archival policy: move old transmittals/contents to cold storage
```

### Implementation Notes (fit with current codebase)

```
- Use events/listeners around create/update to write Process rows consistently
- Wrap file operations in queued jobs with retries and dead-lettering
- Enforce ULIDs for correlation in logs and metrics
- Add repository/services where domain logic grows beyond models
```



## Envoyr - Architecture, Flows, and ERD (ASCII)

### High-level Architecture

```
                        ┌────────────────────────────────────────────────────┐
                        │                        Users                        │
                        │  - Root/Admin/Liaison/Front Desk/Standard User      │
                        └───────────────▲─────────────────────────────────────┘
                                        │
                             HTTP(S) Requests (Filament)
                                        │
                        ┌───────────────┴────────────────┐
                        │        Laravel + Filament       │
                        │  - Routing (routes/web.php)     │
                        │  - Controllers (Http/...)       │
                        │  - Filament Resources (CRUD UI) │
                        │  - Middleware/Auth               │
                        └───────────────┬────────────────┘
                                        │
                          Application Services / Actions
                                        │
                        ┌───────────────┴────────────────┐
                        │             Models              │
                        │  Eloquent (ULIDs, SoftDeletes)  │
                        │  - User, Office, Section        │
                        │  - Document, Transmittal        │
                        │  - Attachment, Content          │
                        │  - Classification, Source, Tag  │
                        │  - Label (pivot)                │
                        └───────────────┬────────────────┘
                                        │
                                 Eloquent ORM
                                        │
                        ┌───────────────┴────────────────┐
                        │             Database            │
                        │  - MySQL/PostgreSQL (ULIDs)     │
                        │  - Migrations in database/      │
                        └─────────────────────────────────┘
```

Notes:
- Filament provides admin panel CRUD for entities (Documents, Transmittals, Offices, Sections, Users, etc.).
- Models encapsulate lifecycle logic (e.g., code generation, publish/unpublish, cleanup on delete).
- Attachments/Contents manage physical files via Storage; soft deletes applied where relevant.

### Core Domain Overview

```
Document
  - Generated code, title, flags (electronic, dissemination)
  - Belongs to: Classification, Source, User, Office, Section
  - Has: Labels (-> Tag), Attachments (direct draft), Transmittals, Processes

Transmittal
  - Generated code, purpose, remarks, pick_up, received_at
  - Belongs to: Document
  - Movement: from_office/section/user -> to_office/section/user; liaison
  - Has: Attachments (morph), Contents

Attachment
  - Belongs to: Document; optionally Transmittal
  - Has: Contents (files, metadata)

Content
  - Files (json), paths (json), hash, context (json), sort, title
  - Belongs to: Attachment

Label (pivot)
  - document_id <-> tag_id

User
  - Role enum, office_id, section_id, invitation lifecycle

Office / Section
  - Hierarchy: Office has many Sections; Section can have a head (User)

Classification / Source / Tag
  - Metadata catalogs referenced by Documents
```

### Request/Panel Flow (simplified)

```
User → Filament Panel → Resource/List → Action (Create/Edit)
      │                    │
      │                    └─> Validates input → Eloquent Model save()
      │
      └─> Policies/Middleware (auth, role) → Controller/Resource handlers
                                              │
                                              └─> Model events (booted creating/deleting etc.)
                                                    - Auto-generate codes (Document, Transmittal)
                                                    - Cascade/cleanup (Attachments → Contents)
```

### Document Lifecycle Flow

```
Create Document
  ↳ before create: code auto-generated
  ↳ status: Draft (published_at = NULL)

Publish Document
  ↳ call Document::publish() → sets published_at = now()

Unpublish Document
  ↳ call Document::unpublish() → sets published_at = NULL

Attachments (Draft)
  ↳ Document has one direct Attachment where transmittal_id IS NULL
  ↳ Contents hold files/metadata; deleting Attachment purges file Storage

Create Transmittal for Document
  ↳ code auto-generated
  ↳ links movement from_office/section/user → to_office/section/user
  ↳ can carry its own attachments and contents
  ↳ activeTransmittal(): latest where received_at IS NULL

Receive Transmittal
  ↳ set received_at → removed from active set
```

### ASCII ERD

```
┌──────────────────────┐               ┌──────────────────────┐
│      classifications │               │               sources │
│  id (ulid, PK)       │               │  id (ulid, PK)       │
│  name                │               │  name                │
│  description         │               │  description         │
└───────────┬──────────┘               └───────────┬──────────┘
            │                                      │
            │                                      │
            │ 1                                  1 │
            │                                      │
            ▼                                      ▼
┌────────────────────────────────────────────────────────────┐
│                       documents                             │
│  id (ulid, PK)                                             │
│  code (unique)                                             │
│  title                                                     │
│  electronic (bool), dissemination (bool)                   │
│  classification_id (FK → classifications.id)               │
│  user_id (FK → users.id)                                   │
│  office_id (FK → offices.id)                               │
│  section_id (FK → sections.id)                             │
│  source_id (FK → sources.id, nullable)                     │
│  published_at (nullable)                                   │
│  deleted_at (soft)                                         │
└───────┬───────────────┬───────────────────────┬────────────┘
        │               │                       │
        │               │                       │
        │               │                       │
        │               │                       │
   1 ───┘          1 ┌──┴──┐  M          M ┌────┴────┐  M
                      │users│<──────────────│  labels │──────────┐
                      └──┬──┘               └─────────┘          │
                         │ 1                                   M  │
                         │                                         │
                         ▼                                         │
                    ┌───────────┐                                  │
                    │ offices   │                                  │
                    │ id (PK)   │                                  │
                    └────┬──────┘                                  │
                         │ 1                                       │
                         ▼                                       1 ▼
                    ┌───────────┐                          ┌───────────┐
                    │ sections  │                          │   tags    │
                    │ id (PK)   │                          │ id (PK)   │
                    └────┬──────┘                          └───────────┘
                         │
                         │ 1
                         ▼
┌────────────────────────────────┐
│          transmittals          │
│ id (ulid, PK)                  │
│ code (unique)                  │
│ purpose, remarks, pick_up      │
│ document_id (FK → documents)   │
│ from_office_id (FK → offices)  │
│ to_office_id (FK → offices)    │
│ from_section_id (FK → sections, nullable) │
│ to_section_id (FK → sections, nullable)   │
│ from_user_id (FK → users, nullable)       │
│ to_user_id (FK → users, nullable)         │
│ liaison_id (FK → users, nullable)         │
│ received_at (nullable)                    │
└───────┬───────────────────────────────────┘
        │ 1
        ▼
┌──────────────────────┐        ┌──────────────────────┐
│     attachments      │   1    │       contents        │
│ id (ulid, PK)        │────────│ id (ulid, PK)        │
│ document_id (FK)     │        │ attachment_id (FK)    │
│ transmittal_id (FK?) │        │ sort, title           │
│                      │        │ file/path/context     │
└──────────┬───────────┘        │ hash (nullable)       │
           │                    └──────────────────────┘
           │ has-one draft attachment per document (transmittal_id IS NULL)
           │ many attachments via transmittals (hasManyThrough)

Legend:
 - 1/M next to lines indicates cardinality from the perspective of the parent box
 - Soft deletes: documents, offices, sections, users
```

### Roles and Access (from `UserRole` enum)

```
ROOT, ADMINISTRATOR, LIAISON, FRONT_DESK, USER
→ canAccessPanel(): all except pending invitation
```

### Operational Behaviors

```
- Code generation on create (Document, Transmittal)
- Cleanup on delete (Attachment → purge contents files)
- activeTransmittal(): latest where received_at IS NULL
- Document publish/unpublish toggles published_at
- Section head fields auto-derived from assigned head (User)
```



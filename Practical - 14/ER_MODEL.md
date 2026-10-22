# StudentHub ER Model (Practical 8)

```mermaid
erDiagram
    STUDENTS ||--o{ EVENT_REGISTRATIONS : registers
    EVENTS ||--o{ EVENT_REGISTRATIONS : receives
    STUDENTS {
      INT student_id PK
      VARCHAR enrollment_no UK
      VARCHAR full_name
      VARCHAR email UK
      VARCHAR course
      TINYINT semester
    }
    EVENTS {
      INT event_id PK
      VARCHAR event_name
      VARCHAR category
      DATE event_date
      VARCHAR venue
    }
    EVENT_REGISTRATIONS {
      INT registration_id PK
      INT student_id FK
      INT event_id FK
      TIMESTAMP registered_at
    }
```

Relationship: Student (1) -> (many) Registrations; Event (1) -> (many) Registrations.
The registrations junction table resolves the many-to-many relationship between students and events.

**Normalization (3NF):** Each table stores one entity/relation. All columns contain atomic values (1NF); no partial dependencies (2NF); non-key attributes do not depend on other non-key attributes (3NF). The junction table has a unique `(student_id,event_id)` pair to prevent duplicate registration. Foreign keys enforce referential integrity.

**Security:** PDO prepared statements for user-submitted registration, exception error handling, escaped HTML output, CSRF token, server-side integer validation and environment-variable credentials.

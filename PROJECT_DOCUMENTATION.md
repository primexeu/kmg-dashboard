# KMG Küchenabgleich - Complete Project Documentation

## 📋 Table of Contents

1. [Project Overview](#project-overview)
2. [Technology Stack](#technology-stack)
3. [System Architecture](#system-architecture)
4. [Database Schema](#database-schema)
5. [Models & Relationships](#models--relationships)
6. [API Endpoints](#api-endpoints)
7. [File Upload System](#file-upload-system)
8. [Python API Integration](#python-api-integration)
9. [PDF Generation](#pdf-generation)
10. [Key Features](#key-features)
11. [Data Flow](#data-flow)
12. [Migration Guide](#migration-guide)

---

## Project Overview

**KMG Küchenabgleich** is a comprehensive order confirmation matching system designed for KMG (a German retailer specializing in kitchens and furniture). The system automates the comparison between purchase orders and supplier order confirmations, providing:

- **Automated Matching**: AI-powered comparison of order confirmations against purchase orders
- **Mismatch Detection**: Identifies discrepancies between orders and confirmations
- **File Management**: Handles document uploads (PDFs, Excel, images)
- **Python API Integration**: External Python service for document processing and comparison
- **PDF Reports**: Professional PDF generation for comparison summaries
- **Admin Dashboard**: Filament-based admin panel for managing comparisons, orders, and matches

### Core Purpose

The system helps KMG agents:
- Track automated matches vs. manual interventions
- Review exceptions and mismatches
- Monitor processing pipeline tasks
- Manage invoices and documentation
- Generate professional comparison reports

---

## Technology Stack

### Backend
- **Framework**: Laravel 12.0
- **PHP Version**: 8.2+
- **Admin Panel**: Filament v3.0
- **PDF Generation**: barryvdh/laravel-dompdf v3.1

### Frontend
- **CSS Framework**: TailwindCSS v3.4.13
- **Asset Bundler**: Vite
- **JavaScript**: Vanilla JS (with some Blade components)

### Database
- **Database**: MySQL/MariaDB (or PostgreSQL)
- **ORM**: Eloquent (Laravel's ORM)

### External Services
- **Python API**: External service for document processing and comparison
- **File Storage**: Local filesystem (Laravel Storage)

---

## System Architecture

```
┌─────────────────┐
│  Python API     │ (External Service)
│  (Document      │
│   Processing)   │
└────────┬────────┘
         │
         │ HTTP POST /api/update
         │
┌────────▼────────────────────────┐
│   Laravel Application            │
│                                  │
│  ┌──────────────┐               │
│  │ API Routes   │               │
│  │ /api/update  │               │
│  │ /api/upload  │               │
│  └──────┬───────┘               │
│         │                        │
│  ┌──────▼───────┐               │
│  │ Controllers  │               │
│  └──────┬───────┘               │
│         │                        │
│  ┌──────▼───────┐               │
│  │ Models       │               │
│  │ (Eloquent)   │               │
│  └──────┬───────┘               │
│         │                        │
│  ┌──────▼───────┐               │
│  │ Database     │               │
│  │ (MySQL)      │               │
│  └──────────────┘               │
│                                  │
│  ┌──────────────┐               │
│  │ Filament     │               │
│  │ Admin Panel  │               │
│  └──────────────┘               │
└──────────────────────────────────┘
```

---

## Database Schema

### Core Tables

#### 1. `users`
**Purpose**: System administrators and agents

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `name` | string | User name |
| `email` | string | Email (unique) |
| `password` | string | Hashed password |
| `role` | string | User role (nullable) |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

#### 2. `orders`
**Purpose**: Purchase orders from ERP system

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `order_number` | string | Order identifier |
| `po_number` | string | PO number (unique) |
| `supplier_code` | string | Supplier code |
| `supplier` | string | Supplier name |
| `customer_name` | string | Customer name |
| `customer_email` | string | Customer email |
| `order_date` | date | Order date |
| `required_by` | date | Required delivery date |
| `expected_delivery_date` | date | Expected delivery |
| `closed_at` | datetime | When order closed |
| `cancelled_at` | datetime | When order cancelled |
| `currency` | string | Currency code |
| `subtotal_amount` | decimal(12,2) | Subtotal |
| `tax_amount` | decimal(12,2) | Tax amount |
| `total_amount` | decimal(12,2) | Total amount |
| `payment_terms` | string | Payment terms |
| `incoterm` | string | Incoterm |
| `shipping_method` | string | Shipping method |
| `tracking_number` | string | Tracking number |
| `billing_address` | json | Billing address |
| `delivery_address` | json | Delivery address |
| `status` | string | Order status |
| `source` | string | Source system |
| `channel` | string | Sales channel |
| `tags` | json | Tags array |
| `metadata` | json | Additional metadata |
| `notes` | text | Notes |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

#### 3. `order_confirmations`
**Purpose**: Supplier order confirmations (AB)

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `order_id` | bigint | Foreign key to orders |
| `supplier` | string | Supplier name |
| `received_at` | datetime | When received |
| `confidence` | decimal(5,2) | Confidence score (0-100) |
| `status` | string | Status |
| `payload` | json | Raw confirmation data |
| `document_id` | bigint | Foreign key to documents |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

**Relationships**:
- `belongsTo(Order)`
- `belongsTo(Document)`

#### 4. `order_matches`
**Purpose**: Successful matches between orders and confirmations

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `order_confirmation_id` | bigint | FK to order_confirmations (REQUIRED) |
| `order_id` | bigint | FK to orders (nullable) |
| `po_number` | string | PO number |
| `customer` | string | Customer name |
| `status` | string | Match status (pending, processed, failed) |
| `payload` | json | Match details |
| `author_id` | bigint | FK to users |
| `updated_by` | bigint | FK to users |
| `strategy` | string | Matching strategy |
| `score` | decimal(3,2) | Match score (0.00-1.00) |
| `result` | string | Result (matched, partial, unmatched) |
| `matched_at` | datetime | When matched |
| `reviewed_by` | bigint | FK to users |
| `notes` | text | Notes |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

**Relationships**:
- `belongsTo(OrderConfirmation)` - REQUIRED
- `belongsTo(Order)`
- `belongsTo(User, 'author_id')`
- `belongsTo(User, 'updated_by')`
- `belongsTo(User, 'reviewed_by')`

#### 5. `order_mismatches`
**Purpose**: Discrepancies requiring review

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `order_id` | bigint | FK to orders |
| `order_confirmation_id` | bigint | FK to order_confirmations |
| `order_match_id` | bigint | FK to order_matches (nullable) |
| `code` | string | Mismatch code |
| `severity` | string | Severity (low, medium, high) |
| `status` | string | Status (open, resolved, ignored) |
| `message` | text | Mismatch message |
| `details` | json | Detailed mismatch data |
| `email` | string | Email for notifications |
| `email_sent` | boolean | Email sent flag |
| `email_sent_at` | datetime | When email sent |
| `created_by` | bigint | FK to users |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

#### 6. `documents`
**Purpose**: Uploaded files (PDFs, Excel, images)

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `title` | string | Document title |
| `description` | text | JSON description (stores original_name, is_external, etc.) |
| `file_path` | string | File path (relative or absolute URL) |
| `uploaded_by` | bigint | FK to users (nullable) |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

**Note**: 
- `original_name` and `is_external` are stored in `description` JSON field
- `is_external` can be determined by checking if `file_path` starts with `http://` or `https://`

#### 7. `python_api_comparisons`
**Purpose**: Comparison results from Python API

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `order_number` | string | Order number |
| `ab_number` | string | AB (confirmation) number |
| `customer_number` | string | Customer number |
| `customer_name` | string | Customer name |
| `commission` | string | Commission |
| `order_header` | json | Order header data |
| `ab_header` | json | AB header data |
| `header_comparison` | json | Header comparison results |
| `items_comparison` | json | Items comparison array |
| `summary` | json | Summary statistics |
| `full_payload` | json | Complete payload from API |
| `overall_status` | string | Status (pending, matched, mismatched, needs_review) |
| `total_items` | integer | Total items |
| `matches` | integer | Number of matches |
| `mismatches` | integer | Number of mismatches |
| `review_required` | integer | Items requiring review |
| `missing_in_order` | integer | Missing in order |
| `missing_in_confirmation` | integer | Missing in confirmation |
| `processed_at` | datetime | When processed |
| `created_by` | bigint | FK to users |
| `updated_by` | bigint | FK to users |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

**Indexes**:
- `order_number`, `ab_number`
- `overall_status`
- `processed_at`

#### 8. `invoices`
**Purpose**: Invoice records

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `invoice_number` | string | Invoice number (unique) |
| `order_id` | bigint | FK to orders |
| `supplier` | string | Supplier name |
| `issued_at` | date | Issue date |
| `due_at` | date | Due date |
| `subtotal_amount` | decimal(12,2) | Subtotal |
| `tax_amount` | decimal(12,2) | Tax |
| `total_amount` | decimal(12,2) | Total |
| `currency` | string | Currency (default: EUR) |
| `status` | enum | Status (draft, pending, approved, paid, void) |
| `pdf_path` | string | PDF file path |
| `created_by` | bigint | FK to users |
| `updated_by` | bigint | FK to users |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

#### 9. `invoice_matches`
**Purpose**: Invoice matching results

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `invoice_id` | bigint | FK to invoices |
| `order_id` | bigint | FK to orders |
| `match_score` | decimal(3,2) | Match score |
| `status` | string | Status |
| `matched_at` | datetime | |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

#### 10. `invoice_mismatches`
**Purpose**: Invoice discrepancies

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `invoice_id` | bigint | FK to invoices |
| `invoice_match_id` | bigint | FK to invoice_matches |
| `code` | string | Mismatch code |
| `severity` | string | Severity |
| `message` | text | Message |
| `details` | json | Details |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

#### 11. `intake_logs`
**Purpose**: Log of incoming API requests

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `status` | string | Status (pending, processing, done, failed) |
| `type` | string | Request type |
| `body` | json | Request body |
| `error` | text | Error message (if failed) |
| `metadata` | json | Additional metadata |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

#### 12. `exceptions`
**Purpose**: System exceptions and errors

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `order_id` | bigint | FK to orders |
| `order_confirmation_id` | bigint | FK to order_confirmations |
| `code` | string | Exception code |
| `severity` | string | Severity |
| `status` | string | Status |
| `message` | text | Message |
| `created_by` | bigint | FK to users |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

---

## Models & Relationships

### Model Overview

```
User
├── hasMany(OrderMatch, 'author_id')
├── hasMany(OrderMatch, 'updated_by')
├── hasMany(OrderMatch, 'reviewed_by')
├── hasMany(PythonApiComparison, 'created_by')
├── hasMany(PythonApiComparison, 'updated_by')
└── hasMany(Document, 'uploaded_by')

Order
├── hasMany(OrderConfirmation)
├── hasMany(OrderMatch)
├── hasMany(OrderMismatch)
└── hasMany(Invoice)

OrderConfirmation
├── belongsTo(Order)
├── belongsTo(Document)
├── hasMany(OrderMatch)
└── hasMany(OrderMismatch)

OrderMatch
├── belongsTo(OrderConfirmation) [REQUIRED]
├── belongsTo(Order)
├── belongsTo(User, 'author_id')
├── belongsTo(User, 'updated_by')
└── belongsTo(User, 'reviewed_by')

OrderMismatch
├── belongsTo(Order)
├── belongsTo(OrderConfirmation)
└── belongsTo(OrderMatch)

Document
└── belongsTo(User, 'uploaded_by')

PythonApiComparison
├── belongsTo(User, 'created_by')
└── belongsTo(User, 'updated_by')
```

### Key Model Methods

#### PythonApiComparison::getUploadedFiles()
**Purpose**: Retrieves all files associated with a comparison from multiple sources.

**Returns**: Array of file information

**Sources checked**:
1. `full_payload['data']['files']` or similar locations
2. `full_payload['metadata']['order_pdf_path']` and `ab_pdf_path`
3. `Document` table by `comparison_id`, `order_number`, or `ab_number`

**File structure returned**:
```php
[
    'source' => 'api|document|document_url',
    'filename' => 'file.pdf',
    'url' => 'http://...',
    'path' => 'incoming/...',
    'type' => 'application/pdf',
    'size' => 123456,
    'document_id' => 123,
    'field' => 'order_pdf'
]
```

---

## API Endpoints

### Public API (`/api/*`)

#### 1. `POST /api/intake`
**Purpose**: Main intake endpoint for receiving data

**Middleware**: `intake.sig` (signature validation)

**Request Body**:
```json
{
  "idempotency_key": "unique-key",
  "source": "matcher-v1",
  "type": "order_confirmation|invoice|exception|order_match",
  "payload": {
    "order_number": "ORD-001",
    "supplier": "Supplier Name",
    "pdf_base64": "...", // Optional: base64 encoded PDF
    "pdf_url": "...",    // Optional: PDF URL
    "file_name": "...",  // Optional: file name
    "uploaded_files": [  // Optional: direct file uploads
      {
        "path": "incoming/...",
        "original_name": "file.pdf",
        "custom_name": "Order Confirmation",
        "size": 123456,
        "mime_type": "application/pdf"
      }
    ]
  }
}
```

**Response**: JSON with status

**Controller**: `App\Http\Controllers\Api\IntakeController`

#### 2. `POST /api/upload-files`
**Purpose**: Simple file upload endpoint

**Request**: `multipart/form-data`
```
files[]: [binary file data]
metadata[comparison_id]: "uuid"
metadata[order_number]: "ORD-001"
metadata[ab_number]: "AB-001"
metadata[type]: "order_confirmation"
```

**Response**:
```json
{
  "success": true,
  "message": "2 file(s) uploaded successfully",
  "documents": [
    {
      "document_id": 1,
      "path": "incoming/2025/11/uuid.pdf",
      "url": "http://localhost:8000/storage/incoming/...",
      "original_name": "file.pdf",
      "size": 123456,
      "mime_type": "application/pdf",
      "uploaded_at": "2025-11-03T20:43:36+00:00"
    }
  ],
  "count": 2
}
```

**Controller**: `App\Http\Controllers\Api\FileUploadController`

#### 3. `POST /api/update` (Alias: `/api/push-results`)
**Purpose**: Receive comparison results from Python API

**Request Body**:
```json
{
  "order_header": {
    "bestellung": "ORD-001",
    "kunden_nr": "12345",
    "kommission": "COM-001"
  },
  "ab_header": {
    "ab_nr": "AB-001",
    "kunde": "Customer Name"
  },
  "header_comparison": {
    "rows": [...],
    "overall": {
      "verdict": "Übereinstimmung"
    }
  },
  "items_comparison": [
    {
      "verdict": "Übereinstimmung",
      "pos_f1": "1",
      "pos_f2": "1",
      "reason": "..."
    }
  ],
  "summary": {
    "total_items": 10,
    "matches": 8,
    "mismatches": 2,
    "review_required": 0
  },
  "metadata": {
    "comparison_id": "uuid-123",
    "order_number": "ORD-001",
    "ab_number": "AB-001",
    "generated_at": "2025-11-03T20:00:00"
  }
}
```

**Response**:
```json
{
  "status": "created|updated",
  "comparison_id": 123,
  "type": "python_api_comparison"
}
```

**Controller**: `App\Http\Controllers\PushComparisonResultsController`

#### 4. `GET /api/fetch-results`
**Purpose**: Fetch results from Python API

**Controller**: `App\Http\Controllers\ComparisonController`

#### 5. `GET /api/check-results`
**Purpose**: Check for new results from Python API

**Controller**: `App\Http\Controllers\ComparisonController`

#### 6. `POST /api/compare-json` (Alias: `/api/test-json`)
**Purpose**: Test JSON comparison endpoint

**Controller**: `App\Http\Controllers\ComparisonController`

### Web Routes (`/admin/*`)

#### 1. `GET /admin/python-api-comparisons/{id}/download-summary`
**Purpose**: Download PDF summary of comparison

**Middleware**: `web`, `auth`

**Response**: PDF file download

**Controller**: `App\Http\Controllers\PythonApiComparisonController`

---

## File Upload System

### Storage Structure

Files are stored in: `storage/app/public/incoming/YYYY/MM/uuid.ext`

### File Upload Flow

```
1. Client uploads files via POST /api/upload-files
   ↓
2. Files validated (PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG, max 16MB)
   ↓
3. Files stored in storage/app/public/incoming/
   ↓
4. Document records created in database
   ↓
5. Metadata stored in description JSON field:
   {
     "original_name": "file.pdf",
     "comparison_id": "uuid",
     "order_number": "ORD-001",
     "ab_number": "AB-001",
     "type": "order_confirmation"
   }
   ↓
6. Response with document information returned
```

### File Linking

Files are linked to comparisons using:
1. **comparison_id**: Primary linking method (stored in Document.description)
2. **order_number**: Fallback linking
3. **ab_number**: Fallback linking

### File Access

Files are accessible via: `http://domain/storage/incoming/...`

**Storage Link**: Must run `php artisan storage:link` to create symlink

---

## Python API Integration

### Integration Flow

```
Phase 1: File Upload
┌─────────────┐
│ Python API  │
│ (Streamlit) │
└──────┬──────┘
       │
       │ POST /api/upload-files
       │ files[] + metadata[comparison_id]
       │
       ▼
┌─────────────┐
│   Laravel   │
│   Storage   │
└─────────────┘

Phase 2: Processing
┌─────────────┐
│ Python API  │
│ (app.py)    │
│ Processes   │
│ Documents   │
└──────┬──────┘
       │
       │ POST /api/update
       │ Results + metadata[comparison_id]
       │
       ▼
┌─────────────┐
│   Laravel   │
│   Database  │
└─────────────┘
```

### Critical Requirements

**IMPORTANT**: The Python API MUST include `comparison_id` in the results payload metadata:

```python
{
    "metadata": {
        "comparison_id": "uuid-123",  # MUST match Phase 1!
        "order_number": "ORD-001",
        "ab_number": "AB-001",
        "generated_at": "2025-11-03T20:00:00"
    }
}
```

### Python API Response Format

The Python API should send results to `/api/update` with this structure:

```json
{
    "order_header": {...},
    "ab_header": {...},
    "header_comparison": {
        "rows": [
            {
                "field": "bestellung",
                "order_value": "ORD-001",
                "confirmation_value": "ORD-001",
                "verdict": "Übereinstimmung",
                "reason": "..."
            }
        ],
        "overall": {
            "verdict": "Übereinstimmung"
        }
    },
    "items_comparison": [
        {
            "verdict": "Übereinstimmung",
            "pos_f1": "1",
            "pos_f2": "1",
            "typ_nr_f1": "TYP-001",
            "typ_nr_f2": "TYP-001",
            "reason": "..."
        }
    ],
    "summary": {
        "total_items": 10,
        "matches": 8,
        "mismatches": 2,
        "review_required": 0,
        "missing_in_order": 0,
        "missing_in_confirmation": 0
    },
    "metadata": {
        "comparison_id": "uuid-123",
        "order_number": "ORD-001",
        "ab_number": "AB-001",
        "generated_at": "2025-11-03T20:00:00"
    }
}
```

---

## PDF Generation

### PDF Generation Flow

```
User clicks "PDF" button
    ↓
GET /admin/python-api-comparisons/{id}/download-summary
    ↓
PythonApiComparisonController::downloadSummary()
    ↓
Gathers data from comparison record
    ↓
Renders PDF template (resources/views/pdf/comparison-summary.blade.php)
    ↓
DomPDF generates PDF
    ↓
Returns PDF download
```

### PDF Template

**Location**: `resources/views/pdf/comparison-summary.blade.php`

**Features**:
- Professional corporate design
- Company branding header
- Executive summary with statistics
- Visual progress bars
- Header comparison section
- Order and AB header information
- Items comparison table (top 10)
- Professional footer

### PDF Configuration

```php
$pdf = Pdf::loadView('pdf.comparison-summary', $data);
$pdf->setPaper('a4', 'portrait');
$pdf->setOption('isHtml5ParserEnabled', true);
$pdf->setOption('isRemoteEnabled', true);
return $pdf->download($filename);
```

---

## Key Features

### 1. Automated Matching
- Compares order confirmations against purchase orders
- Identifies matches, mismatches, and items requiring review
- Calculates match scores and confidence levels

### 2. File Management
- Supports multiple file types (PDF, DOC, DOCX, XLS, XLSX, images)
- Handles direct uploads, base64 encoding, and URLs
- Links files to comparisons using comparison_id

### 3. Admin Dashboard (Filament)
- Resource management for comparisons, orders, matches
- Custom comparison view page with statistics
- Visual widgets showing match/mismatch statistics
- File display at top of comparison pages

### 4. PDF Reports
- Professional PDF generation
- Corporate branding
- Comprehensive comparison summaries
- Downloadable reports

### 5. Python API Integration
- Two-phase upload process (files → results)
- Automatic linking of files to results
- Support for external document processing

---

## Data Flow

### Complete Comparison Flow

```
1. User uploads files via Python API (Streamlit)
   ├─ Files sent to POST /api/upload-files
   ├─ comparison_id generated (UUID)
   └─ Files stored + Document records created

2. Python API processes documents
   ├─ Extracts order and AB data
   ├─ Performs comparison
   └─ Generates comparison results

3. Python API sends results
   ├─ POST /api/update with results payload
   ├─ metadata.comparison_id MUST match Phase 1
   └─ PythonApiComparison record created

4. Laravel links files to results
   ├─ Searches Document table by comparison_id
   ├─ Falls back to order_number/ab_number
   └─ Files available in comparison view

5. User views comparison
   ├─ Files displayed at top
   ├─ Statistics and widgets shown
   ├─ Detailed comparison tables
   └─ Can download PDF summary
```

### Intake Processing Flow

```
1. POST /api/intake
   ├─ Validates signature (middleware)
   ├─ Validates request data
   ├─ Creates IntakeLog record
   └─ Dispatches ProcessIntake job

2. ProcessIntake Job
   ├─ Processes file uploads (if any)
   ├─ Creates Document records
   ├─ Routes to appropriate handler:
   │  ├─ ingestConfirmation() → OrderConfirmation
   │  ├─ ingestMatch() → OrderMatch
   │  ├─ ingestException() → Exception
   │  └─ ingestInvoice() → Invoice
   └─ Updates IntakeLog status
```

---

## Migration Guide

### For Porting to Another Language/Framework

#### 1. Database Schema

**Action**: Create equivalent tables in your database system

**Key Points**:
- Use JSON columns for flexible data (order_header, ab_header, payload, etc.)
- Ensure foreign key relationships are maintained
- Index frequently queried columns (order_number, ab_number, overall_status)
- `order_matches.order_confirmation_id` is REQUIRED (NOT NULL)

#### 2. Models

**Laravel Equivalent**: Eloquent Models

**Port to**:
- **Django**: Models with relationships
- **Node.js/Express**: Sequelize/Mongoose schemas
- **Rails**: ActiveRecord models
- **ASP.NET**: Entity Framework models

**Key Relationships**:
- Order → OrderConfirmation (one-to-many)
- OrderConfirmation → OrderMatch (one-to-many)
- OrderMatch → OrderConfirmation (belongs-to, REQUIRED)

#### 3. API Endpoints

**Port to**: REST API in your framework

**Critical Endpoints**:
- `POST /api/upload-files` - File upload
- `POST /api/update` - Results submission
- `POST /api/intake` - General intake (optional)

**Response Formats**: Keep JSON structure consistent

#### 4. File Storage

**Current**: Laravel Storage (`storage/app/public/incoming/`)

**Port to**:
- Local filesystem with similar structure
- Cloud storage (S3, Azure Blob, etc.)
- Database BLOB storage (not recommended for large files)

**Key Requirements**:
- Store file paths in database
- Support both local and external URLs
- Create public access URLs for files

#### 5. PDF Generation

**Current**: DomPDF (PHP)

**Port to**:
- **Python**: ReportLab, WeasyPrint, xhtml2pdf
- **Node.js**: Puppeteer, PDFKit, jsPDF
- **Ruby**: Prawn, Wicked PDF
- **Java**: iText, Apache PDFBox
- **C#**: iTextSharp, QuestPDF

**Template**: Port HTML/CSS template to your PDF library

#### 6. Admin Panel

**Current**: Filament (Laravel)

**Port to**:
- **Django**: Django Admin
- **Node.js**: AdminJS, React Admin
- **Rails**: Rails Admin, ActiveAdmin
- **ASP.NET**: Custom admin panel

**Key Features to Port**:
- Resource management (CRUD)
- Custom comparison view page
- File display
- Statistics widgets
- PDF download button

#### 7. Authentication

**Current**: Laravel Auth

**Port to**: Your framework's auth system

**Requirements**:
- User management
- Role-based access (optional)
- Session/token management

#### 8. Queue System

**Current**: Laravel Queue (ProcessIntake job)

**Port to**:
- **Python**: Celery
- **Node.js**: Bull, Agenda
- **Ruby**: Sidekiq, Delayed Job
- **Java**: Spring Boot + RabbitMQ
- **C#**: Hangfire

#### 9. Environment Variables

**Required Variables**:
```
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kmg_dashboard
DB_USERNAME=root
DB_PASSWORD=

PYTHON_API_URL=http://localhost:5000  # If applicable
```

#### 10. Key Business Logic

**PythonApiComparison::getUploadedFiles()**
- Port this method logic to your language
- Checks multiple sources for files
- Returns standardized file array

**ProcessIntake::ingestSingle()**
- Port file processing logic
- Port routing logic (match, invoice, exception handlers)
- Port Order/OrderConfirmation creation logic

**PushComparisonResultsController::processPythonApiResult()**
- Port comparison result processing
- Port status determination logic
- Port duplicate detection logic

---

## Important Notes

### Critical Requirements

1. **comparison_id Linking**: Files and results MUST share the same `comparison_id` for proper linking
2. **order_confirmation_id**: `OrderMatch` records REQUIRE `order_confirmation_id` (NOT NULL)
3. **File Storage**: Must create storage symlink for public file access
4. **JSON Fields**: Many fields use JSON for flexible data storage

### Data Structure Notes

- **JSON Fields**: Store complex data structures (arrays, objects)
- **Metadata**: Store additional information in JSON fields
- **Casts**: Important for automatic JSON encoding/decoding
- **Timestamps**: Automatic `created_at` and `updated_at` management

### Performance Considerations

- Index frequently queried columns
- Use pagination for large result sets
- Consider caching for frequently accessed data
- Optimize file storage (use CDN for production)

---

## Support & Maintenance

### Logging

- Laravel logs: `storage/logs/laravel.log`
- Check logs for API payloads and errors
- Debug file linking issues using logs

### Common Issues

1. **Files not showing**: Check `comparison_id` in both upload and results
2. **Database errors**: Verify foreign key constraints
3. **PDF generation**: Check DomPDF configuration
4. **File access**: Ensure storage symlink exists

---

## Version Information

- **Laravel**: 12.0
- **Filament**: 3.0
- **PHP**: 8.2+
- **DomPDF**: 3.1
- **TailwindCSS**: 3.4.13

---

**Last Updated**: November 2025

**Document Version**: 1.0

---

This documentation provides a complete overview of the KMG Küchenabgleich system. Use it as a reference when porting to another language or framework, or when onboarding new developers.


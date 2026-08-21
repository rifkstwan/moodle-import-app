# Moodle User Import Application

A robust and scalable user import application built with **PHP 8.3**, **PostgreSQL**, and **React**. The application enables importing users from CSV files via both a **Modern Web UI** and a **Command-Line Interface (CLI)**, sharing core business logic across both entry points.

---

## 📑 Table of Contents
- [Overview](#overview)
- [Architecture & Shared Logic](#architecture--shared-logic)
- [Requirements](#requirements)
- [Installation & Getting Started](#installation--getting-started)
- [Database Configuration](#database-configuration)
- [Web UI Usage](#web-ui-usage)
- [CLI Usage & Examples](#cli-usage--examples)
- [Automated Testing](#automated-testing)
- [Project Structure](#project-structure)
- [Design Decisions & Assumptions](#design-decisions--assumptions)

---

## 📌 Overview

The application follows the end-to-end import lifecycle defined in the specification:

```
[ Upload / Input ] ➔ [ Parse CSV ] ➔ [ Transform & Validate ] ➔ [ Preview ] ➔ [ Import to DB ]
```

### Core Features
- **CSV Parsing**: Handles arbitrary numbers of records with header offset support.
- **Name Transformation**: Automatically capitalises `name` and `surname` fields into Title Case (`john` ➔ `John`).
- **Email Normalisation**: Converts email addresses to lowercase (`USER@EXAMPLE.COM` ➔ `user@example.com`).
- **Validation**:
  - Validates RFC-compliant email address formats.
  - Ensures uniqueness against existing database records (case-insensitive).
  - Enforces required field constraints.
- **Preview & Dry-Run**: Inspect valid and invalid records before committing changes.
- **Dual Interfaces**: Full-featured React Web UI and comprehensive PHP CLI tool.

---

## 🏗️ Architecture & Shared Logic

To maintain high code quality and testability, business logic is strictly decoupled from the presentation and transport layers:

- **`ImportService`**: The core orchestrator shared by both the Web API (`index.php`) and CLI (`user_upload.php`).
- **`UserTransformer`**: Pure transformation logic (casing and string normalisation).
- **`UserValidator`**: Comprehensive record validation rules.
- **`UserRepository`**: Data access layer managing PostgreSQL interactions and transactions.

---

## 📋 Requirements

### Using Docker (Recommended)
- **Docker** 24.0+
- **Docker Compose** v2.0+

### Manual Setup (Without Docker)
- **PHP** 8.3+ with `pdo_pgsql`, `mbstring`, and `bcmath` extensions
- **Composer** 2.x
- **PostgreSQL** 15+
- **Node.js** 18+ & **npm** 9+

---

## 🚀 Installation & Getting Started

### 1. Clone the Repository
```bash
git clone <repository-url>
cd moodle-import-app
```

### 2. Start Services via Docker Compose
Run the entire stack (Database, PHP Backend, React Frontend) in detached mode:
```bash
docker compose up -d
```

Once running, the services are available at:
- **Web UI**: [http://localhost:3000](http://localhost:3000)
- **Backend API**: [http://localhost:8080](http://localhost:8080)
- **PostgreSQL**: `localhost:5432`

To view logs:
```bash
docker compose logs -f
```

To stop all services:
```bash
docker compose down
```

---

## 🗄️ Database Configuration

Database parameters are loaded from environment variables or the `.env` file located in the `backend/` directory:

| Variable | Default Value | Description |
| :--- | :--- | :--- |
| `DB_HOST` | `db` (Docker) / `localhost` | Database host name |
| `DB_PORT` | `5432` | PostgreSQL port |
| `DB_NAME` | `moodle_import_app` | Target database name |
| `DB_USER` | `moodle_import_app` | Database username |
| `DB_PASSWORD` | `moodle_import_app_pass` | Database password |

The database schema (`backend/sql/schema.sql`) automatically initialises the `users` table upon container startup:

```sql
CREATE TABLE users (
    id        BIGSERIAL PRIMARY KEY,
    name      VARCHAR(255) NOT NULL,
    surname   VARCHAR(255) NOT NULL,
    email     VARCHAR(255) NOT NULL UNIQUE
);
```

---

## 🌐 Web UI Usage

1. Open [http://localhost:3000](http://localhost:3000) in your web browser.
2. Drag and drop your `.csv` file into the upload zone or click to select from your filesystem.
3. The system will parse and validate the file, presenting a **Data Preview**:
   - Total records found, valid count, and invalid count.
   - A detailed breakdown table displaying each record, transformed values, status tags, and specific validation error tooltips.
4. Click **"Import Valid Records"** to persist valid entries to the PostgreSQL database.
5. The **Import Complete** screen confirms the number of records imported and records skipped.

---

## 💻 CLI Usage & Examples

The CLI script is located at `backend/cli/user_upload.php`.

### Available Options
```text
Usage: php user_upload.php [options]

Options:
  --file <filename>    CSV file to process
  --dry-run            Parse and validate without importing
  --create-table       Create/rebuild the users table
  --help               Display available options
```

### Command Examples (via Docker)

#### 1. Display Help
```bash
docker exec moodle_backend php cli/user_upload.php --help
```

#### 2. Rebuild the Database Table
```bash
docker exec moodle_backend php cli/user_upload.php --create-table
```

#### 3. Dry-Run (Validate without inserting)
```bash
docker exec moodle_backend php cli/user_upload.php --file users.csv --dry-run
```
*Output:*
```text
[DRY RUN] Processing users.csv...

Summary:
Total records found: 49
Valid records:       41
Invalid records:     8

Errors:
Row 42: Invalid email address format: invalid-email
Row 43: Invalid email address format: missing@
Row 44: Email address already exists: john.smith@example.com
Row 45: Email address already exists: john.smith@example.com
Row 46: Name is required.
Row 47: Surname is required.
Row 48: Email is required.
Row 49: Invalid email address format: bad@@example.com
```

#### 4. Execute Full Import
```bash
docker exec moodle_backend php cli/user_upload.php --file users.csv
```
*Output:*
```text
Processing users.csv...

Summary:
Total records found: 49
Valid records:       41
Invalid records:     8

Errors:
Row 42: Invalid email address format: invalid-email
Row 43: Invalid email address format: missing@
Row 44: Email address already exists: john.smith@example.com
Row 45: Email address already exists: john.smith@example.com
Row 46: Name is required.
Row 47: Surname is required.
Row 48: Email is required.
Row 49: Invalid email address format: bad@@example.com

Successfully imported 41 users.
```

---

## 🧪 Automated Testing

Unit tests cover the core transformation and validation logic independently:

```bash
docker exec moodle_backend php tests/internal_test.php
```

*Output:*
```text
--- RUNNING LOGIC TESTS ---
[PASS] UserTransformer logic
[PASS] UserValidator logic

--- ALL INTERNAL LOGIC TESTS PASSED ---
```

---

## 📁 Project Structure

```text
moodle-import-app/
├── backend/
│   ├── cli/
│   │   └── user_upload.php      # CLI entrypoint script
│   ├── config/                  # Configuration files
│   ├── public/
│   │   ├── index.php            # REST API entrypoint
│   │   └── router.php           # PHP built-in server router
│   ├── sql/
│   │   └── schema.sql           # PostgreSQL table definition
│   ├── src/
│   │   ├── Config/
│   │   │   └── Config.php       # Environment configuration loader
│   │   ├── Csv/
│   │   │   └── CsvParser.php    # CSV streaming and normalization
│   │   ├── Database/
│   │   │   ├── Database.php     # PDO connection wrapper
│   │   │   └── UserRepository.php # Database query operations
│   │   └── Import/
│   │       ├── ImportService.php   # Shared import workflow service
│   │       ├── UserTransformer.php # Name & email string transformations
│   │       └── UserValidator.php   # Record validation rules
│   ├── tests/
│   │   └── internal_test.php    # Test suite for transformer & validator
│   ├── Dockerfile               # Backend PHP 8.3 container specification
│   └── composer.json            # PHP dependencies & PSR-4 autoloading
├── frontend/
│   ├── src/
│   │   ├── api/
│   │   │   └── importApi.js     # Backend API integration layer
│   │   ├── components/
│   │   │   ├── FileUpload.jsx   # Drag-and-drop CSV upload component
│   │   │   ├── PreviewTable.jsx # Data preview and validation table
│   │   │   └── ImportResult.jsx # Final confirmation component
│   │   ├── App.jsx              # Main React container
│   │   ├── main.jsx             # React entrypoint
│   │   └── index.css            # Clean modern design system
│   ├── index.html               # HTML document template
│   └── package.json             # Frontend dependencies (React, Vite)
├── users.csv                    # Sample test data file
├── docker-compose.yml           # Multi-container orchestration
└── README.md                    # Project documentation
```

---

## 💡 Design Decisions & Assumptions

1. **Shared Domain Logic**: The import process follows Single Responsibility Principle (SRP) and Dependency Inversion. Both the CLI and HTTP controllers delegate entirely to `ImportService`, guaranteeing identical parsing, validation, and transformation rules across all access methods.
2. **Title-Case Capitalisation**: Implemented via `mb_convert_case(..., MB_CASE_TITLE, 'UTF-8')` to accurately handle multibyte and international character names.
3. **Case-Insensitive Email Uniqueness**: Email addresses are converted to lowercase prior to database checks and insertion, preventing accidental duplicates such as `John@Example.com` and `john@example.com`.
4. **Resilient Error Handling**: Invalid rows do not abort the import of valid records. Instead, errors are collected, mapped to their specific row numbers, and reported to the user.
5. **Modern Minimalist UI**: The Web UI uses clean typography, SVG vector icons, smooth micro-interactions, and accessible status indicators for a professional user experience.

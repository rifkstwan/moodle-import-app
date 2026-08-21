# 📡 REST API Documentation

The backend exposes a lightweight, CORS-enabled REST API for CSV parsing, validation previews, and database persistence.

**Base URL:** `http://localhost:8080/api`

---

## 1. Parse & Preview CSV

Parses an uploaded CSV file, applies title-casing transformations, and runs validation rules **without** modifying the database (*Dry-Run mode*).

- **URL:** `/api/parse`
- **Method:** `POST`
- **Content-Type:** `multipart/form-data`

### Request Parameters

| Field | Type | Required | Description |
| :--- | :--- | :--- | :--- |
| `file` | `File` (Binary) | Yes | The `.csv` file to parse and validate. |

### Successful Response (`200 OK`)

```json
{
  "total": 3,
  "valid": 2,
  "invalid": 1,
  "results": [
    {
      "row": ["John", "Smith", "john.smith@example.com"],
      "isValid": true,
      "errors": []
    },
    {
      "row": ["Jane", "Doe", "jane.doe@example.com"],
      "isValid": true,
      "errors": []
    },
    {
      "row": ["Bob", "Invalid", "bad-email"],
      "isValid": false,
      "errors": [
        "Invalid email address format: bad-email"
      ]
    }
  ]
}
```

---

## 2. Execute Import

Parses the CSV file and imports all **valid records** into the PostgreSQL `users` table. Invalid records are reported in the response and skipped.

- **URL:** `/api/import`
- **Method:** `POST`
- **Content-Type:** `multipart/form-data`

### Request Parameters

| Field | Type | Required | Description |
| :--- | :--- | :--- | :--- |
| `file` | `File` (Binary) | Yes | The `.csv` file to import. |

### Successful Response (`200 OK`)

```json
{
  "total": 3,
  "valid": 2,
  "invalid": 1,
  "results": [
    {
      "row": ["John", "Smith", "john.smith@example.com"],
      "isValid": true,
      "errors": []
    },
    {
      "row": ["Jane", "Doe", "jane.doe@example.com"],
      "isValid": true,
      "errors": []
    },
    {
      "row": ["Bob", "Invalid", "bad-email"],
      "isValid": false,
      "errors": [
        "Invalid email address format: bad-email"
      ]
    }
  ]
}
```

---

## 3. Error Responses

### Missing File (`400 / 500`)
```json
{
  "error": "No file uploaded"
}
```

### Route Not Found (`404`)
```json
{
  "error": "Not Found"
}
```

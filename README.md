# ContactDirectory

**AI Generated**
This is a simple contact directory web application built with PHP and MySQL. It allows users to manage clients and contacts, and link them together.

## Features

- Client list view with linked-contact counts.
- Contact list view with linked-client counts.
- Create and edit clients.
- Create and edit contacts.
- Link/unlink contacts to clients (many-to-many relationship).
- Simple JSON APIs for client/contact upserts.
- Encrypted IDs in URLs for basic obfuscation.

## Tech Stack

- PHP (procedural pages + utility functions)
- MySQL (via `mysqli`)
- Vanilla JavaScript (`fetch` for API calls)
- Bootstrap 5 (loaded from CDN)

## Project Structure

```text
.
├── index.php                    # Clients list (home)
├── contacts/                    # Contact listing/edit/unlink pages
├── clients/                     # Client edit/unlink pages
├── api/
│   ├── client/update/           # Client create/update endpoint
│   └── contact/update/          # Contact create/update endpoint
├── config/
│   ├── setup.php                # Timezone, .env loading, shared $now
│   ├── database.php             # DB singleton wrapper
│   ├── utils.php                # Encryption + helper functions
│   └── init.php                 # Shared layout config bootstrap
├── layouts/
│   ├── header.php               # Navbar + CSS includes
│   └── footer.php               # JS includes
└── assets/js/                   # Form submit logic for edit pages
```

## Requirements

- PHP 8.0+
- MySQL / MariaDB
- Web server that serves from this repo root (Apache/Nginx or PHP built-in server)
- OpenSSL PHP extension (used by `encrypt_data` / `decrypt_data`)

## Environment Configuration

Request `.env` file through an issue or make one yourself based on the template below and place it in the repository root:

```ini
DB_HOST=127.0.0.1
DB_USER=your_db_user
DB_PASSWORD=your_db_password
DB_NAME=contact_directory

# Must be compatible with AES-256-CBC
ENCRYPTION_KEY=your_32_char_secret_key_here

# 16-byte IV for AES-256-CBC
ENCRYPTION_IV=your_16_char_iv
```

> Notes:
>
> - `setup.php` loads `.env` using `parse_ini_file`.
> - `ENCRYPTION_KEY` and `ENCRYPTION_IV` are required for route ID encryption/decryption.

## Database Schema

The app expects at least these tables:

- `Clients`
  - `client_id` (PK)
  - `name`
  - `client_code` (unique code generated from name)
  - `created_at`, `created_by`
- `Contacts`
  - `contact_id` (PK)
  - `name`, `surname`, `email`
  - `created_at`, `created_by`
- `Client2Contact` (join table)
  - `client_id`
  - `contact_id`
  - `created_at`, `created_by`
  - `updated_at`
  - `status` (uses `1` for active, `0` for unlinked)

## Running Locally

### Option 1: PHP built-in server

From repo root:

```bash
php -S 127.0.0.1:8000
```

Then open:

- Clients: `http://127.0.0.1:8000/`
- Contacts: `http://127.0.0.1:8000/contacts`
- Live Demo: `https://directory.naicker.tech`

### Option 2: Apache/Nginx

Point your web root to this repository and ensure PHP is enabled.

## API Endpoints

Both endpoints expect `POST` with JSON and return JSON.

### `POST /api/client/update/`

Payload:

```json
{
  "client_id": "<encrypted id or empty>",
  "client_name": "Acme Corp",
  "contact_id": ["<encrypted_contact_id>"]
}
```

- If `client_id` decrypts to a valid ID, the client is updated.
- Otherwise, a new client is created and a `client_code` is generated.
- Any provided `contact_id` values are linked via `Client2Contact`.

### `POST /api/contact/update/`

Payload:

```json
{
  "contact_id": "<encrypted id or empty>",
  "contact_name": "Jane",
  "contact_surname": "Doe",
  "contact_email": "jane@example.com",
  "client_id": ["<encrypted_client_id>"]
}
```

- If `contact_id` decrypts to a valid ID, the contact is updated.
- Otherwise, a new contact is created.
- Any provided `client_id` values are linked via `Client2Contact`.

## Known Behavior / Caveats

- Timezone is currently set to `Africa/Johannesburg` in `config/setup.php`.
- There is no authentication/authorization layer yet (`created_by` is hard-coded to `1`).
- IDs are just obfuscated, not fully security-hardened access control.



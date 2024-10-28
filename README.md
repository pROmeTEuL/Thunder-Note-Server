# Thunder Note Server

This is a simple PHP application for managing notes. It provides a RESTful API to create, read, update, and delete notes. The app uses a PostgreSQL database and should be run with the PHP built-in server.

## Requirements

- PHP 7.4 or later
- PostgreSQL

## Running the App

To run the app, use the built-in PHP server:

```bash
php -S [address]:[port]
```

For example, you might run:

```bash
php -S localhost:8000
```

This starts the app at `http://localhost:8000`.

## API Endpoints

### GET /

- **Description**: Retrieve a JSON list of all notes.
- **Response**: JSON array of notes with each note containing `id`, `title`, `body`, and `date`.

### POST /

- **Description**: Create a new note.
- **Data**:
  ```json
  {
    "title": "string",
    "body": "string (optional)"
  }
  ```

### GET /[id]

- **Description**: Retrieve an individual note by `id`.
- **Response**: JSON object of the note with `id`, `title`, `body`, and `date`.

### PUT /[id]

- **Description**: Update an existing note by `id`.
- **Data**:
  ```json
  {
    "title": "string",
    "body": "string"
  }
  ```
- **Response**: JSON object of the updated note with `id`, `title`, `body`, and `date`.

### DELETE /[id]

- **Description**: Delete a note by `id`.

## Configuration

The app requires a `settings.conf` file for database configuration. This file should be located in the project root.

### settings.conf

```conf
[server config]
user = {database user}
# comment out if you don't require a password
password = {database password}
```

Set `user` to your PostgreSQL database user. If your database does not require a password, you can comment out or remove the `password` line.

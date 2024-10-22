# Fixed it for you

## Build

**Step 1:** add a .env file with the contents

```env
DATABASE_URL="sqlite://db/<someurl>.db"
LISTEN_ADDR="localhost:8080"
```

(fill in with the desired content)

**Step 2:** run the database migrations

```sh
sqlx database create
sqlx database setup

# alternatively, use the unga bunga mode

sqlite3 db/<your_databse>.db
# god have mercy
sqlite3 your_database.db ".databases" && ls migrations/*.sql | xargs -I {} sh -c 'echo "Running migration: {}"; sqlite3 your_database.db < "{}"'


```

Follow the [sqlx install guide](https://github.com/launchbadge/sqlx/blob/main/sqlx-cli/README.md) to install the `sqlx-cli` tool.

**Step 3:** run the server

```sh
cargo run
```

## Usage

### Get all notes

Every user can query all notes in the database.

```sh
curl -v -XGET localhost:8080
```

### Add a note

Every user can add a note to the database.

```sh
curl -v -X \
POST -H \
 "Content-Type: application/json" -d '{"title": "fort", "body": "night"}' localhost:8080
```

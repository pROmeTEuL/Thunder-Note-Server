-- Add migration script here
CREATE TABLE IF NOT EXISTS thunder_note (
    id TEXT PRIMARY KEY NOT NULL,
    title TEXT NOT NULL,
    body TEXT
);

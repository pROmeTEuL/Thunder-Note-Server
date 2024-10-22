use std::ops::DerefMut;

use axum::{extract::State, routing::get, Json, Router};
use dotenvy::dotenv;
use error::AppError;
use nanoid::nanoid;
use serde::{Deserialize, Serialize};
use sqlx::{query, query_as, Pool, Sqlite, SqlitePool};
use tokio::net::TcpListener;

mod error;

#[derive(Deserialize)]
struct Config {
    database_url: String,
    listen_addr: String,
}

#[tokio::main]
async fn main() -> anyhow::Result<()> {
    tracing_subscriber::fmt::init();
    dotenv()?;
    let config: Config = envy::from_env()?;
    let pool = SqlitePool::connect(&config.database_url).await?;

    let app = Router::new()
        .route("/", get(get_all_notes).post(add_note))
        .with_state(AppState { db: pool });

    let tcp = TcpListener::bind(config.listen_addr).await?;
    let server = axum::serve(tcp, app);
    tracing::info!("🔥 Listening on {}", server.local_addr()?);
    server.await?;
    Ok(())
}

type AppResult<T> = Result<T, AppError>;

#[derive(Debug, Clone)]
struct AppState {
    db: Pool<Sqlite>,
}

#[derive(Serialize, Deserialize)]
struct Note {
    #[serde(default)]
    id: String,
    title: String,
    body: Option<String>,
}

async fn get_all_notes(State(state): State<AppState>) -> AppResult<Json<Vec<Note>>> {
    let mut db = state.db.acquire().await?;
    let db = db.deref_mut();
    let res = query_as!(Note, "SELECT * FROM notes").fetch_all(db).await?;
    tracing::info!("📝 Got {} notes", res.len());
    Ok(Json(res))
}

async fn add_note(
    State(state): State<AppState>,
    Json(payload): Json<Note>,
) -> AppResult<Json<Note>> {
    let mut db = state.db.acquire().await?;
    let db = db.deref_mut();
    let id = nanoid!();
    let query = query!(
        "INSERT INTO notes (id, title, body) VALUES (?, ?, ?)",
        id,
        payload.title,
        payload.body
    );
    query.execute(db).await?;
    tracing::info!("✨ Added note with id {}", id);
    Ok(Json(Note { id, ..payload }))
}

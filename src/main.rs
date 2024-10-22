use std::env;

use axum::{response::IntoResponse, routing::get, Router};
use dotenvy::dotenv;
use serde::Deserialize;
use sqlx::{Pool, Sqlite, SqlitePool};
use tokio::net::{unix::SocketAddr, TcpListener};

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
        .route("/", get(get_note).post(add_note))
        .with_state(AppState { db: pool });

    let tcp = TcpListener::bind(config.listen_addr).await?;
    let server = axum::serve(tcp, app);
    tracing::info!("🔥 Listening on {}", server.local_addr()?);
    server.await?;
    Ok(())
}

#[derive(Debug, Clone)]
struct AppState {
    db: Pool<Sqlite>,
}

async fn get_note() -> impl IntoResponse {
    "hello get note"
}

async fn add_note() -> impl IntoResponse {
    "hello add note"
}

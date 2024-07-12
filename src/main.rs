use actix_cors::Cors;
use actix_web::{get, post, web, App, Error, HttpResponse, HttpServer, Responder};
use serde::{Deserialize, Serialize};
use serde_json::json;
use std::fs::{self, File, OpenOptions};
use std::io::{prelude::*, BufReader};
use std::path::Path;

#[derive(Deserialize, Serialize)]
struct SessionInfo {
    user: String,
    date: String,
    status: String,
}

#[derive(Deserialize, Serialize)]
struct CpuRequest {
    #[serde(rename = "cpu_yuzde")]
    cpu_percentage: String,
    #[serde(rename = "bilgisayar_adi")]
    computer_name: String,
    #[serde(rename = "ram_yuzde")]
    ram_percentage: String,
    #[serde(rename = "ram_kapasite")]
    ram_capacity: String,
    #[serde(rename = "disk_kullanim")]
    disk_useage: String,
    #[serde(rename = "disk_kapasite")]
    disk_capacity: String,
    #[serde(rename = "oturum_bilgisi")]
    session_info: Vec<SessionInfo>,
}

#[get("/")]
async fn hello() -> impl Responder {
    HttpResponse::Ok().body("Hello world!")
}

#[post("/cpurequest")]
async fn cpu_request(req_body: web::Json<CpuRequest>) -> Result<HttpResponse, Error> {
    let file_name = format!("{}.txt", req_body.computer_name);
    let file_path = Path::new("./Computers").join(&file_name);

    let json_content = json!(
        {
            "cpu_yuzde": req_body.cpu_percentage,
            "bilgisayar_adi": req_body.computer_name,
            "ram_yuzde": req_body.ram_percentage,
            "ram_kapasite": req_body.ram_capacity,
            "disk_kullanim": req_body.disk_useage,
            "disk_kapasite": req_body.disk_capacity,
            "oturum_bilgisi": req_body.session_info
        });

    // Create the directory if it doesn't exist
    std::fs::create_dir_all("./Computers")?;

    // Check if the file already exists
    if !file_path.exists() {
        // If the file doesn't exist, create it
        let file = File::create(file_path)?;
        writeln!(&file, "{}", json_content.to_string())?;
    } else {
        let file = File::create(file_path)?;
        writeln!(&file, "{}", json_content.to_string())?;
    }

    Ok(HttpResponse::Ok().body("Request body saved"))
}

#[get("/cpuusage")]
async fn cpu_usage() -> Result<impl Responder, Error> {
    let mut result = json!({});

    let computers_dir = Path::new("./Computers");

    if computers_dir.is_dir() {
        for entry in fs::read_dir(computers_dir)? {
            let entry = entry?;
            let path = entry.path();
            if path.is_file() {
                let file_name = path.file_stem().unwrap().to_str().unwrap();
                let mut file = File::open(&path)?;
                let mut contents = String::new();
                file.read_to_string(&mut contents)?;
                
                let json_content: serde_json::Value = serde_json::from_str(&contents)?;
                result[file_name] = json_content;
            }
        }
    }

    Ok(HttpResponse::Ok().json(result))
}

#[actix_web::main]
async fn main() -> std::io::Result<()> {
    HttpServer::new(|| {
        let cors = Cors::default() // Default CORS ayarları ile Cors objesi oluştur
            .allowed_origin("http://localhost:8000") // İzin verilen origin ekleyin, uygulamanızın çalıştığı URL'yi buraya ekleyin
            .allowed_methods(vec!["GET", "POST"]); // İzin verilen HTTP metodlarını belirleyin

        App::new()
            .wrap(cors) // CORS middleware'ini uygulamaya wrap edin
            .service(hello)
            .service(cpu_request)
            .service(cpu_usage)
    })
    .bind(("127.0.0.1", 9827))?
    .run()
    .await
}

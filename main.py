import base64

from fastapi import FastAPI, File, HTTPException, UploadFile
from fastapi.responses import FileResponse
from fastapi.staticfiles import StaticFiles

from grader import grade_image

app = FastAPI(title="PicGradr", description="AI-powered dating profile photo grader")

app.mount("/static", StaticFiles(directory="."), name="static")

ALLOWED_TYPES = {"image/jpeg", "image/png", "image/gif", "image/webp"}


@app.get("/")
def index():
    return FileResponse("index.html")


@app.get("/article.html")
def article():
    return FileResponse("article.html")


@app.get("/information.html")
def information():
    return FileResponse("information.html")


@app.get("/pg_logo_main.png")
def logo():
    return FileResponse("pg_logo_main.png")


@app.post("/analyze")
async def analyze(image: UploadFile = File(...)):
    if image.content_type not in ALLOWED_TYPES:
        raise HTTPException(status_code=400, detail="Only JPEG, PNG, GIF, and WEBP images are allowed.")

    data = await image.read()
    if len(data) == 0:
        raise HTTPException(status_code=400, detail="Uploaded file is empty.")

    b64 = base64.b64encode(data).decode("utf-8")

    result = grade_image(b64, image.content_type)
    return result.model_dump()

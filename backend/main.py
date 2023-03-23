from fastapi import FastAPI
from starlette.middleware.cors import CORSMiddleware

from app.api import api_router


app = FastAPI(title="pyNotifier")

app.add_middleware(
    CORSMiddleware,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

app.include_router(api_router, prefix="/api/v2")

from typing import Generator

from app.repo import LocalSession


def get_pg_db() -> Generator:
    try:
        db = LocalSession()
        yield db
    finally:
        db.close()

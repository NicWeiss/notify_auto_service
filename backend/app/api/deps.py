from typing import Generator

from app.repos import LocalSession


def get_pg_db() -> Generator:
    try:
        db = LocalSession()
        yield db
    finally:
        db.close()

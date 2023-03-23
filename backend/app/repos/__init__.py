from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker

from app.core.config import settings


engine = create_engine(settings.SQLALCHEMY_URI, pool_pre_ping=True, pool_size=60, max_overflow=20)
LocalSession = sessionmaker(autocommit=False, autoflush=False, bind=engine)

from pydantic import BaseConfig


class Settings(BaseConfig):
    SQLALCHEMY_URI = 'postgresql://test:test@0.0.0.0:8097/test'


settings = Settings()

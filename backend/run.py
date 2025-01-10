# from app.repo.kyc import KYCSession
import uvicorn

from app.core.config import settings
from app.main import app
from app.utils.logger import logger

# from alembic.config import Config
# from alembic import command as alembic_command


# def db_check() -> None:
#     try:
#         db = KYCSession()
#         Try to create session to check if DB is awake
#         db.execute("SELECT 1")
#     except Exception as e:
#         # logger.error(e)
#         raise e


# def migrate(url: str) -> None:
#     alembic_cfg = Config("alembic.ini")
#     alembic_cfg.set_main_option("sqlalchemy.url", url)
#     alembic_command.upgrade(alembic_cfg, "head")


def main() -> None:
    logger.info("INIT")
    is_reload = False
    main_app = app

    # db_check()

    # if not settings.IS_DEBUG:
    # migrate(settings.SQLALCHEMY_KYC_URI)

    if settings.IS_LIVE_RELOAD:
        main_app = "app.main:app"
        is_reload = True

    uvicorn.run(
        main_app,
        reload=is_reload,
        host=settings.APP_HOST,
        port=settings.APP_PORT,
        log_config=None,
        proxy_headers=True,
        forwarded_allow_ips="*"
    )


if __name__ == "__main__":
    main()

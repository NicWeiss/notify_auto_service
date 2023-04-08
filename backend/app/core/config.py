import distutils
import os
from typing import Dict
from pydantic import PostgresDsn, AnyHttpUrl


class Settings:
    SQLALCHEMY_URI: PostgresDsn
    SMTP_URI: AnyHttpUrl
    NOTIFIER_SENDER: Dict = None
    NOTIFIER_SENDER_EMAIL: str
    NOTIFIER_SENDER_NAME: str

    IP_LOCATION_PROVIDER_TOKEN: str = ''

    RABBITMQ_DSN: str
    REDIS_DSN: str

    IS_DEBUG: bool = False
    DO_NOT_RUN_PERIODIC_TASK: bool = False

    TELERGAM_BOT_TOKEN: str
    FCM_SERVER_KEY: str

    def __init__(self):
        for key, type in self.__annotations__.items():
            env_value = os.environ.get(key)

            if env_value is None:
                if not hasattr(self, key):
                    raise Exception(f'Missing {key=} in enviroment')
                else:
                    setattr(self, key, getattr(self, key))
                    continue

            if type is Dict:
                type = dict
            elif type in [AnyHttpUrl, PostgresDsn]:
                type = str
            elif type is bool:
                env_value = bool(distutils.util.strtobool(env_value))

            setattr(self, key, type(env_value))

        self.assemble_sender()

    def assemble_sender(self):
        self.NOTIFIER_SENDER = {
            "name": self.NOTIFIER_SENDER_NAME,
            "email": self.NOTIFIER_SENDER_EMAIL
        }


settings = Settings()

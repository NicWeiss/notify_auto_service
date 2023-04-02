from pydantic import BaseModel


class NotifyAcceptorCreateScheme(BaseModel):
    notify_id: int
    acceptor_id: int

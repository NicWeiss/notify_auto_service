from typing import Optional

from sqlalchemy.orm import Session

from app.repo.crud.notify_crud import NotifyCrud
from app.services import ServiceResponse


class NotifyService:
    def __init__(self, db: Session = None) -> None:
        self.db = db
        self.notify_crud = NotifyCrud(db=db)

    def get_list(
        self,
        user_id: int,
        category_id: Optional[int],
        page: Optional[int],
        per_page: Optional[int]
    ) -> ServiceResponse:
        notify_models = self.notify_crud.get_list(
            user_id=user_id,
            category_id=category_id,
            page=page,
            per_page=per_page
        )

        return ServiceResponse(data=notify_models)

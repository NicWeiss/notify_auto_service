from typing import List, Optional

from app.repo.models.notify_model import Notify
from app.repo.crud.base_crud import Crud


class NotifyCrud(Crud):
    def __init__(self, db):
        super().__init__(db=db, model=Notify)

    def get_list(
        self,
        user_id: int,
        category_id: int = 0,
        page: int = 1,
        per_page: int = 0
    ) -> List[Optional[Notify]]:
        query = self.db.query(self.model).filter(self.model.user_id == user_id)

        if category_id:
            query = query.filter(self.model.category_id == category_id)

        query = query.order_by(self.model.id.desc())

        if per_page:
            page = (page - 1) if (page - 1) >= 0 else 0
            query = query.limit(per_page)
            query = query.offset(page * per_page)

        return query.all()

import math
from typing import List, Optional

from sqlalchemy import and_, func

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
        query = self.db.query(self.model).filter(and_(
            self.model.user_id == user_id,
            self.model.is_deleted.is_(False)
        ))

        if category_id:
            query = query.filter(self.model.category_id == category_id)
        else:
            query = query.filter(self.model.category_id.is_(None))

        query = query.order_by(self.model.id.desc())

        if per_page:
            page = (page - 1) if (page - 1) >= 0 else 0
            query = query.limit(per_page)
            query = query.offset(page * per_page)

        return query.all()

    def get_all_by_user_id_and_category_id(self, user_id: int, category_id: int) -> List[Notify]:
        return self.db.query(self.model).filter(and_(
            self.model.user_id == user_id,
            self.model.category_id == category_id,
            self.model.is_deleted.is_(False)
        )).all()

    def get_meta(self, user_id: int, category_id: int, per_page: int = 0):
        records_count = self.db.query(func.count(self.model.id)).filter(and_(
            self.model.user_id == user_id,
            self.model.category_id == category_id,
            self.model.is_deleted.is_(False)
        )).scalar()
        if not per_page or per_page < 1:
            per_page = records_count

        return {'total_pages': math.ceil(records_count / per_page)}

    def get_by_periodic_and_time(self, periodic: str, time: str) -> List[Notify]:
        return self.db.query(self.model).filter(and_(
            self.model.periodic == periodic,
            self.model.time == time,
            self.model.is_deleted.is_(False),
            self.model.is_disabled.is_(False)
        )).all()

    def get_by_day_of_week_and_time(self, day_of_week: int, time: str) -> List[Notify]:
        return self.db.query(self.model).filter(and_(
            self.model.day_of_week == day_of_week,
            self.model.time == time,
            self.model.is_deleted.is_(False),
            self.model.is_disabled.is_(False)
        )).all()

    def get_by_periodic_and_date_and_time(self, periodic: str, date: str, time: str) -> List[Notify]:
        filters = []
        filters.append(self.model.periodic == periodic)
        filters.append(self.model.time == time)
        filters.append(self.model.is_deleted.is_(False))
        filters.append(self.model.is_disabled.is_(False))

        if '%' in date:
            filters.append(self.model.date.like(date))
        else:
            filters.append(self.model.date == date)

        return self.db.query(self.model).filter(and_(*filters)).all()

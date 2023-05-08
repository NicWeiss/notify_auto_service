import calendar
import pytz
from datetime import datetime, timedelta

from sqlalchemy.orm import Session

from app.repo.crud.date_operation_crud import DateOperationCrud
from app.repo.schemas.date_operations_scheme import DateOperationCreateScheme
from app.services import ServiceResponse


class DateOperationService:
    def __init__(self, db: Session = None) -> None:
        self.db = db
        self.date_operation_crud = DateOperationCrud(db=db)

    def create_new_operations(self):
        now_date = datetime.utcnow().replace(second=0).replace(microsecond=0).replace(tzinfo=pytz.UTC)
        last_operation_model = self.date_operation_crud.get_last_operation()

        if last_operation_model:
            last_date = last_operation_model.create_at.replace(second=0).replace(microsecond=0).replace(tzinfo=pytz.UTC)
        else:
            last_date = now_date - timedelta(minutes=1)

        if last_date > now_date:
            return

        delta_min = int((now_date - last_date).total_seconds() / 60)
        if delta_min < 1:
            return True

        for min in range(int(delta_min) - 1, -1, -1):
            print(f'Operation create for date {(now_date - timedelta(minutes=min))}')
            date_data = self._get_date_object(date=(now_date - timedelta(minutes=min)))
            scheme_object = DateOperationCreateScheme(date_data=date_data, status='new', create_at=now_date)
            self.date_operation_crud.create(scheme=scheme_object)

        return ServiceResponse()

    def _get_date_object(self, date: datetime):
        date_object = {}

        date_object['first_day'] = 1
        date_object['last_day'] = int(calendar.monthrange(date.year, date.month)[1])
        date_object['day'] = date.day
        date_object['day_of_week'] = date.weekday() + 1
        date_object['month'] = date.month
        date_object['time'] = date.strftime('%H:%M')
        date_object['date'] = date.strftime('%Y-%m-%d')

        return date_object

    def get_operation_ids_for_process(self) -> ServiceResponse:
        operations = self.date_operation_crud.get_ids_for_process()

        return ServiceResponse(data=operations)

    def get_operation_by_id(self, id: int) -> ServiceResponse:
        operation = self.date_operation_crud.get_by_id(id=id)

        return ServiceResponse(data=operation)

    def clear_done_operations(self):
        self.date_operation_crud.clear_done_operations()

        return ServiceResponse()

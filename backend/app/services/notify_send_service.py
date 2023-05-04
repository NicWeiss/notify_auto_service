import logging
from typing import List

from arrow import Arrow
from sqlalchemy.orm import Session

from app.repo.crud.date_operation_crud import DateOperationCrud
from app.repo.crud.notify_crud import NotifyCrud
from app.repo.schemas.date_operations_scheme import DateOperationUpdateScheme
from app.services import ServiceResponse
from app.services.email_service import EmailService
from app.services.fcm_service import FcmService
from app.utils.telegram import Telegram
from app.utils.firebase import FireBase

logger = logging.getLogger(__name__)

WORKDAYS = ['1', '2', '3', '4', '5']
HOLIDAYS = ['6', '7']


class NotifySenderService:
    def __init__(self, db: Session = None) -> None:
        self.db = db
        self.notify_pairs = []

        self.date_operation_crud = DateOperationCrud(db=db)
        self.notify_crud = NotifyCrud(db=db)
        self.email_service = EmailService()

    def process_operation(self, operation) -> None:
        print(f'Start operation {operation.id}')
        scheme = DateOperationUpdateScheme(status='process')
        self.date_operation_crud.update(db_object=operation, scheme=scheme)

        first_day = operation.date_data['first_day']
        last_day = operation.date_data['last_day']
        day = operation.date_data['day']
        day_of_week = operation.date_data['day_of_week']
        month = operation.date_data['month']

        time = operation.date_data['time']
        date = operation.date_data['date']

        self._find_by_periodic(periodic='everyday', time=time)
        self._find_once(date=date, time=time)
        self._find_day_of_week(time=time, day_of_week=day_of_week)
        self._find_every_month(day=day, time=time)
        self._find_every_year(month=month, day=day, time=time)

        if first_day == day:
            self._find_by_periodic(periodic='first_month_day', time=time)

        if last_day == day:
            self._find_by_periodic(periodic='last_month_day', time=time)

        if day_of_week in WORKDAYS:
            self._find_by_periodic(periodic='workday', time=time)

        if day_of_week in HOLIDAYS:
            self._find_by_periodic(periodic='weekend', time=time)

        scheme = DateOperationUpdateScheme(status='done', complete_at=Arrow.now())
        self.date_operation_crud.update(db_object=operation, scheme=scheme)

        self._send_pairs()
        print(f'Operation {operation.id} is done!')

        return ServiceResponse()

    def _find_by_periodic(self, periodic: str, time: str):
        notifies = self.notify_crud.get_by_periodic_and_time(periodic=periodic, time=time)
        self._prepare_send_pairs(notifies=notifies)

    def _find_once(self, date: str, time: str):
        notifies = self.notify_crud.get_by_periodic_and_date_and_time(periodic='once', date=date, time=time)
        self._prepare_send_pairs(notifies=notifies)

    def _find_day_of_week(self, day_of_week: int, time: str):
        notifies = self.notify_crud.get_by_day_of_week_and_time(day_of_week=day_of_week, time=time)
        self._prepare_send_pairs(notifies=notifies)

    def _find_every_month(self, day: int, time: str):
        day = str(day) if day > 10 else f'0{day}'
        date = f'%-{day}'

        notifies = self.notify_crud.get_by_periodic_and_date_and_time(periodic='every_month', date=date, time=time)
        self._prepare_send_pairs(notifies=notifies)

    def _find_every_year(self, month: int, day: int, time: str):
        day = str(day) if day > 10 else f'0{day}'
        month = str(month) if month > 10 else f'0{month}'
        date = f'%-{month}-{day}'

        notifies = self.notify_crud.get_by_periodic_and_date_and_time(periodic='every_year', date=date, time=time)
        self._prepare_send_pairs(notifies=notifies)

    def _prepare_send_pairs(self, notifies: List):
        for notify in notifies:
            if not notify.acceptors:
                logger.info(f'Notify {notify.id} has no acceptors')
                continue

            for acceptor in notify.acceptors:
                self.notify_pairs.append((notify, acceptor))

    def _send_pairs(self):
        for notify, acceptor in self.notify_pairs:
            account = acceptor.account
            title = notify.name
            text = notify.text or ''
            transport_type = acceptor.system.type
            user_id = notify.user_id

            if transport_type == 'email':
                self.email_service.create(
                    recipients=[account],
                    subject=title,
                    text=text
                )
                if not self.email_service.send():
                    print('Email sent is failure')

            elif transport_type == 'tg':
                result = Telegram.send(
                    recipient=account,
                    title=title,
                    text=text
                )
                if not result:
                    print('Telegram sent is failure')

            elif transport_type == 'push':
                fcm_service = FcmService(db=self.db, user_id=user_id)
                tokens = account.split(';') if account else []

                for token in tokens:
                    result = FireBase.send_push(
                        recipients=account.split(';') if account else '',
                        title=title,
                        text=text
                    )

                    if not result:
                        fcm_service.remove_fcm_token(token=token)

        self.notify_pairs = []

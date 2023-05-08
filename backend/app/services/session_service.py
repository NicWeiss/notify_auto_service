import secrets

from arrow import Arrow
from datetime import timedelta
from typing import Dict

from sqlalchemy.orm import Session

from app.repo.crud.session_crud import SessionCrud
from app.repo.schemas.session_scheme import SessionCreateScheme
from app.services import ServiceResponse
from app.services.location_service import LocationService


class SessionService:
    def __init__(self, db: Session = None) -> None:
        self.db = db
        self.session_crud = SessionCrud(db=db)

    def set_session(self, user_id: int, user_ip: str, session_data: Dict) -> ServiceResponse:
        data = session_data.dict()
        expire_delta = timedelta(days=3650) if data.get('client', {}).get('mobile', False) else timedelta(days=1)

        result = LocationService().get_location(ip=user_ip)
        if not result.is_error:
            data['location'] = result.data

        data['session'] = secrets.token_hex(nbytes=16)
        data['expire_at'] = Arrow.now() + expire_delta
        data['user_id'] = user_id

        session_scheme = SessionCreateScheme(**data)
        self.session_crud.create(scheme=session_scheme)

        return ServiceResponse(data=data['session'])

    def get_session(self, session: str) -> ServiceResponse:
        db_object = self.session_crud.get_by_session(session=session)

        if not db_object:
            return ServiceResponse(is_error=True, description='Session not found')

        return ServiceResponse(data=db_object)

    def get_by_user_id(self, user_id: int) -> ServiceResponse:
        session_models = self.session_crud.get_all_by_user_id(user_id=user_id)

        return ServiceResponse(data=session_models)

    def delete_session(self, user_id: int, session_id: int):
        session = self.session_crud.get_by_id_and_user_id(user_id=user_id, id=session_id)
        self.session_crud.remove_db_object(db_object=session)

        return ServiceResponse()

    def clear_expires_sessions(self) -> ServiceResponse:
        self.session_crud.clear_expires_sessions()

        return ServiceResponse()

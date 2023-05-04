from fastapi import APIRouter

from app.api.endpoints import acceptors, auth, categories, notify, sessions, system, user
from app.api.schemas.utils import ApiValidationError

invalid_response = {422: {'model': ApiValidationError, 'description': 'Validation Error'}}

api_router = APIRouter()
api_router.include_router(acceptors.router, tags=["Acceptor"], responses=invalid_response)
api_router.include_router(auth.router, tags=["Auth"], responses=invalid_response)
api_router.include_router(categories.router, tags=["Category"], responses=invalid_response)
api_router.include_router(notify.router, tags=["Notify"], responses=invalid_response)
api_router.include_router(sessions.router, tags=["Session"], responses=invalid_response)
api_router.include_router(system.router, tags=["System"], responses=invalid_response)
api_router.include_router(user.router, tags=["User"], responses=invalid_response)

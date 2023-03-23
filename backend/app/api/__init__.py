from fastapi import APIRouter

from app.api.endpoints import test
from app.api.schemas.utils import ApiValidationError

invalid_response = {422: {'model': ApiValidationError, 'description': 'Validation Error'}}

api_router = APIRouter()
api_router.include_router(test.router, tags=["tets"], responses=invalid_response)

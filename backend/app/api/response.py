from typing import List, Union

from fastapi import HTTPException
from sqlalchemy.orm import DeclarativeBase


def db_object_to_dict(db_object):
    dict_object = {}
    for column in db_object.__table__.columns:
        dict_object[column.name] = str(getattr(db_object, column.name))

    return dict_object


class EmberResponse():
    def __new__(
        cls,
        model_name: str,
        data: Union[DeclarativeBase, List[Union[DeclarativeBase, None]]],
        total_pages: int = 0
    ) -> None:
        response = {'meta': {}}

        if data is None:
            return HTTPException(status_code=404, detail=f'Model {model_name} not found')
        elif type(data) is list:
            data = [db_object_to_dict(obj) for obj in data]
        else:
            data = db_object_to_dict(data)

        response[model_name] = data
        response['meta']['total_pages'] = total_pages

        return response

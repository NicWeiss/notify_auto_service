from typing import List, Union

from arrow import Arrow
from fastapi import HTTPException
from sqlalchemy.orm import DeclarativeBase
from sqlalchemy.orm.collections import InstrumentedList


class EmberResponse():
    @classmethod
    def dump_field_value(cls, field_value):
        field_value_type = type(field_value)

        if field_value_type in [type(None), str, int, float, bool]:
            return field_value

        if field_value_type is Arrow:
            return str(field_value)
        if field_value_type in [list, InstrumentedList]:
            return [cls.dump_field_value(item) for item in field_value]
        if field_value_type is dict:
            resp_dict = {}
            for key, val in field_value.items():
                resp_dict[key] = cls.dump_field_value(field_value=val)
            return resp_dict

        if hasattr(field_value, '_sa_registry'):
            return cls.db_object_to_dict(db_object=field_value)

        raise TypeError(f'Unknown field type {field_value_type}')

    @classmethod
    def db_object_to_dict(cls, db_object):
        model_as_dict = db_object.__dict__
        model_as_dict.pop('_sa_instance_state', None)

        dict_object = {}
        for column, value in model_as_dict.items():
            dumped_value = cls.dump_field_value(field_value=value)

            dict_object[column] = dumped_value

        return dict_object

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
            data = [cls.db_object_to_dict(obj) for obj in data]
        else:
            data = cls.db_object_to_dict(data)

        response[model_name] = data
        response['meta']['total_pages'] = total_pages

        return response

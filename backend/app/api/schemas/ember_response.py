from typing import Dict, List, Optional, Union

from pydantic import BaseModel
from pydantic.fields import ModelField


class MetaScheme(BaseModel):
    total_pages: Optional[int]


class EmberResponseScheme(BaseModel):
    meta: Optional[MetaScheme]

    def __init__(self, *args, **kw):
        data = kw.copy()
        data.pop('meta', None)
        keys = list(data.keys())

        if len(keys) != 1:
            raise Exception('Too many arguments in response')

        self.__fields__[keys[0]] = ModelField.infer(
            name=keys[0],
            value=None,
            annotation=Union[List[Union[Dict, None]], Dict],
            class_validators=None,
            config=self.__config__,
        )

        super().__init__(*args, **kw)

    class Config:
        arbitrary_types_allowed = True

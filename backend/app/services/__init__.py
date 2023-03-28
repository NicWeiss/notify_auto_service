from typing import Dict


class ServiceResponse:
    status = None
    is_error = None
    error = None
    description = None
    data = None
    meta = None

    def __init__(self, is_error: bool = False, data: Dict = {}, meta: Dict = {}, description: str = ''):
        if is_error:
            self.status = 'error'
        else:
            self.status = 'ok'

        self.description = description
        self.is_error = is_error
        self.data = data
        self.meta = meta

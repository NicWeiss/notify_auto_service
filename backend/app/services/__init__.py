
class ServiceResponse:
    status = None
    is_error = None
    error = None
    description = None
    data = None

    def __init__(self, is_error=False, data={}, description=''):
        if is_error:
            self.description = description
            self.status = 'error'
        else:
            self.status = 'ok'

        self.is_error = is_error
        self.data = data

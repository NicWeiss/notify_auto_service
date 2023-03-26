from app.repo.crud.user import UserCrud


class TestService:
    def __init__(self, db=None):
        self.db = db

    def get_name_of_test_user(self):
        crud = UserCrud(db=self.db)
        user = crud.get_by_id(id=1)

        if user:
            return user.name
        else:
            raise Exception('Test user not found')

class Crud:
    def __init__(self, db, model):
        self.db = db
        self.model = model

    def get_by_id(self, id):
        return self.db.query(self.model).filter(self.model.id == id).first()

package models

type Category struct {
	Id        int    `json:"id"`
	IsDeleted bool   `json:"is_deleted"`
	Name      string `json:"name"`
	UserId    int    `json:"user_id"`
	IsHidden  bool   `json:"is_hidden"`
}

func (n *Category) GetEmptyItem() (any, any) {
	nt := Category{}
	return nt, &nt
}

func (n *Category) MapRecords(prepared []interface{}) []Category {
	var result = make([]Category, 0)
	for _, element := range prepared {
		result = append(result, element.(Category))
	}

	return result
}

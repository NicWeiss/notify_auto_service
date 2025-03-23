package models

type Acceptor struct {
	Id         int    `json:"id"`
	IsDeleted  bool   `json:"is_deleted"`
	IsDisables bool   `json:"is_disabled"`
	IsSystem   bool   `json:"is_system"`
	Name       string `json:"name"`
	UserId     int    `json:"user_id"`
	SystemId   int    `json:"system_id"`
	Account    string `json:"account"`
}

func (n *Acceptor) GetEmptyItem() (any, any) {
	inst := Acceptor{}
	return inst, &inst
}

func (n *Acceptor) MapRecords(prepared []interface{}) []Acceptor {
	var result = make([]Acceptor, 0)
	for _, element := range prepared {
		result = append(result, element.(Acceptor))
	}

	return result
}

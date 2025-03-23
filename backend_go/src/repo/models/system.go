package models

type System struct {
	Id         int    `json:"id"`
	IsDeleted  bool   `json:"is_deleted"`
	IsDisabled bool   `json:"is_disabled"`
	IsSystem   bool   `json:"is_system"`
	Name       string `json:"name"`
	Help       string `json:"help"`
	Type       string `json:"type"`
}

func (n *System) GetEmptyItem() (any, any) {
	inst := System{}
	return inst, &inst
}

func (n *System) MapRecords(prepared []interface{}) []System {
	var result = make([]System, 0)
	for _, element := range prepared {
		result = append(result, element.(System))
	}

	return result
}

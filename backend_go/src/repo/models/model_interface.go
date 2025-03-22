package models

type Model interface {
	GetEmptyItem() (interface{}, interface{})
}

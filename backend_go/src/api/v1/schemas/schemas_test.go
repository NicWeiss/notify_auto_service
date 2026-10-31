package schemas

import (
	"strings"
	"testing"

	"github.com/gin-gonic/gin/binding"
)

func bind(t *testing.T, body string, destination any) error {
	t.Helper()
	return binding.JSON.BindBody([]byte(body), destination)
}

// The body is a real request of the frontend: Ember Data sends the ids of the
// embedded acceptors as strings.
func TestNotifyFromEmberWithStringIds(t *testing.T) {
	body := `{"notify":{"name":"Разберись ","text":null,"periodic":"everyday","day_of_week":null,"date":null,
		"time":"06:58","is_disabled":true,"category_id":null,"repeate_interval":null,"is_autodisable":false,
		"autodisable_at":"2026-03-17T21:58:33.177Z",
		"acceptors":[{"id":"2","name":"TG","system_id":100,"account":"222940588","is_disabled":false,"is_system":false}]}}`

	var params NotifyParams
	if err := bind(t, body, &params); err != nil {
		t.Fatal(err)
	}

	fields := params.Notify
	if fields.Acceptors == nil || len(*fields.Acceptors) != 1 || (*fields.Acceptors)[0].Id != 2 {
		t.Errorf("acceptors: %+v", fields.Acceptors)
	}
	if fields.CategoryId != nil || fields.DayOfWeek != nil || fields.RepeateInterval != nil {
		t.Errorf("null must stay nil: %+v", fields)
	}
	if fields.Time == nil || *fields.Time != "06:58" || fields.IsDisabled == nil || !*fields.IsDisabled {
		t.Errorf("fields: %+v", fields)
	}
}

func TestNumbersAsStringsAndAsNumbers(t *testing.T) {
	var notify NotifyParams
	if err := bind(t, `{"notify":{"category_id":"7","day_of_week":3,"repeate_interval":"15","acceptors":[{"id":4},{"id":"5"}]}}`, &notify); err != nil {
		t.Fatal(err)
	}
	if *notify.Notify.CategoryId.Int() != 7 || *notify.Notify.DayOfWeek.Int() != 3 || *notify.Notify.RepeateInterval.Int() != 15 {
		t.Errorf("%+v", notify.Notify)
	}
	if got := *notify.Notify.Acceptors; got[0].Id != 4 || got[1].Id != 5 {
		t.Errorf("acceptors: %+v", got)
	}

	// the category of "reset/delete by category" is the id of an Ember record
	var category NotifyCategoryParams
	if err := bind(t, `{"category_id":"12"}`, &category); err != nil || category.CategoryId != 12 {
		t.Errorf("category: %+v %v", category, err)
	}
	if err := bind(t, `{"category_id":12}`, &category); err != nil || category.CategoryId != 12 {
		t.Errorf("category: %+v %v", category, err)
	}

	var acceptor AcceptorCreateParams
	if err := bind(t, `{"acceptor":{"name":"TG","account":"1","system_id":"100"}}`, &acceptor); err != nil || acceptor.Acceptor.SystemId != 100 {
		t.Errorf("acceptor: %+v %v", acceptor, err)
	}

	var update AcceptorUpdateParams
	if err := bind(t, `{"acceptor":{"system_id":"101"}}`, &update); err != nil || *update.Acceptor.SystemId.Int() != 101 {
		t.Errorf("acceptor update: %+v %v", update, err)
	}
}

func TestWrongNumbersAreRejected(t *testing.T) {
	var notify NotifyParams
	for _, body := range []string{
		`{"notify":{"acceptors":[{"id":"abc"}]}}`,
		`{"notify":{"category_id":"x"}}`,
		`{"notify":{"day_of_week":1.5}}`,
	} {
		if err := bind(t, body, &notify); err == nil || !strings.Contains(err.Error(), "integer") && !strings.Contains(err.Error(), "number") {
			t.Errorf("%s: accepted or unclear error: %v", body, err)
		}
	}

	var category NotifyCategoryParams
	if err := bind(t, `{}`, &category); err == nil {
		t.Error("category_id is required")
	}
}

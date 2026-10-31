package crud

import (
	"fmt"
	"time"

	"github.com/lib/pq"

	"notifier/src/core"
	"notifier/src/repo/models"
)

const notifyColumns = `n.id, coalesce(n.is_deleted, false), coalesce(n.name, ''), n.user_id, n.text, coalesce(n.periodic, ''),
	n.day_of_week, n.date, coalesce(n.time, ''), n.repeate_interval, coalesce(n.is_disabled, false), n.category_id,
	coalesce(n.is_autodisable, false), n.autodisable_at`

func scanNotify(row scanner) (models.Notify, error) {
	var n models.Notify
	err := row.Scan(
		&n.Id, &n.IsDeleted, &n.Name, &n.UserId, &n.Text, &n.Periodic,
		&n.DayOfWeek, &n.Date, &n.Time, &n.RepeateInterval, &n.IsDisabled, &n.CategoryId,
		&n.IsAutodisable, &n.AutodisableAt,
	)
	n.Acceptors = make([]models.Acceptor, 0)
	return n, err
}

// queryNotifies runs a query which selects notifyColumns and loads the
// acceptors (enabled and not deleted only) of the found notifies.
func queryNotifies(q core.Querier, sql string, args ...any) ([]models.Notify, error) {
	rows, err := q.Query(sql, args...)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	notifies := make([]models.Notify, 0)
	for rows.Next() {
		n, err := scanNotify(rows)
		if err != nil {
			return nil, err
		}
		notifies = append(notifies, n)
	}
	if err = rows.Err(); err != nil {
		return nil, err
	}
	rows.Close()

	return notifies, loadAcceptors(q, notifies)
}

func loadAcceptors(q core.Querier, notifies []models.Notify) error {
	if len(notifies) == 0 {
		return nil
	}

	ids := make([]int64, 0, len(notifies))
	position := make(map[int]int, len(notifies))
	for i, n := range notifies {
		ids = append(ids, int64(n.Id))
		position[n.Id] = i
	}

	rows, err := q.Query(
		`select na.notify_id, `+acceptorColumns+acceptorFrom+`
		 join notify_acceptor na on na.acceptor_id = a.id
		 where na.notify_id = any($1) and a.is_deleted is not true and a.is_disabled is not true
		 order by a.id`,
		pq.Array(ids),
	)
	if err != nil {
		return err
	}
	defer rows.Close()

	for rows.Next() {
		var notifyId int
		var a models.Acceptor
		err = rows.Scan(&notifyId, &a.Id, &a.IsDeleted, &a.IsDisabled, &a.IsSystem, &a.Name, &a.UserId, &a.SystemId, &a.Account, &a.SystemType)
		if err != nil {
			return err
		}

		i := position[notifyId]
		notifies[i].Acceptors = append(notifies[i].Acceptors, a)
	}

	return rows.Err()
}

func categoryFilter(categoryId *int, argNumber int) (string, []any) {
	if categoryId == nil {
		return ` and n.category_id is null`, nil
	}

	return ` and n.category_id=$` + itoa(argNumber), []any{*categoryId}
}

// ListNotifies returns notifies of one category (nil - without category).
// perPage = 0 turns the pagination off.
func ListNotifies(q core.Querier, userId int, categoryId *int, page int, perPage int) ([]models.Notify, error) {
	filter, filterArgs := categoryFilter(categoryId, 2)
	sql := `select ` + notifyColumns + ` from notify n where n.user_id=$1 and n.is_deleted is not true` + filter + ` order by n.id desc`
	args := append([]any{userId}, filterArgs...)

	if perPage > 0 {
		if page < 1 {
			page = 1
		}
		sql += ` limit ` + itoa(perPage) + ` offset ` + itoa((page-1)*perPage)
	}

	return queryNotifies(q, sql, args...)
}

func CountNotifies(q core.Querier, userId int, categoryId *int) (int, error) {
	filter, filterArgs := categoryFilter(categoryId, 2)
	args := append([]any{userId}, filterArgs...)

	var count int
	err := q.QueryRow(
		`select count(*) from notify n where n.user_id=$1 and n.is_deleted is not true`+filter,
		args...,
	).Scan(&count)

	return count, err
}

func GetNotify(q core.Querier, id int, userId int) (models.Notify, error) {
	notifies, err := queryNotifies(
		q,
		`select `+notifyColumns+` from notify n where n.id=$1 and n.user_id=$2 and n.is_deleted is not true`,
		id, userId,
	)
	if err != nil {
		return models.Notify{}, err
	}
	if len(notifies) == 0 {
		return models.Notify{}, errNoRows
	}

	return notifies[0], nil
}

// NotifyWrite: for the nullable columns (text, date, day_of_week,
// repeate_interval, category_id, autodisable_at) nil means "set NULL", for the
// others nil means "keep the value".
type NotifyWrite struct {
	Name            *string
	Text            *string
	Periodic        *string
	DayOfWeek       *int
	Date            *string
	Time            *string
	RepeateInterval *int
	IsDisabled      *bool
	CategoryId      *int
	IsAutodisable   *bool
	AutodisableAt   *time.Time
}

func utcPointer(t *time.Time) *time.Time {
	if t == nil {
		return nil
	}

	utc := t.UTC()
	return &utc
}

func CreateNotify(q core.Querier, userId int, data NotifyWrite) (int, error) {
	var id int
	err := q.QueryRow(
		`insert into notify (user_id, name, text, periodic, day_of_week, date, time, repeate_interval,
		   is_disabled, category_id, is_autodisable, autodisable_at, is_deleted)
		 values ($1, coalesce($2, ''), $3, coalesce($4, ''), $5, $6, coalesce($7, ''), $8,
		   coalesce($9, false), $10, coalesce($11, false), $12, false)
		 returning id`,
		userId, data.Name, data.Text, data.Periodic, data.DayOfWeek, data.Date, data.Time, data.RepeateInterval,
		data.IsDisabled, data.CategoryId, data.IsAutodisable, utcPointer(data.AutodisableAt),
	).Scan(&id)

	return id, err
}

func UpdateNotify(q core.Querier, id int, userId int, data NotifyWrite) error {
	_, err := q.Exec(
		`update notify set name=coalesce($3, name), text=$4, periodic=coalesce($5, periodic), day_of_week=$6, date=$7,
		   time=coalesce($8, time), repeate_interval=$9, is_disabled=coalesce($10, is_disabled), category_id=$11,
		   is_autodisable=coalesce($12, is_autodisable), autodisable_at=$13
		 where id=$1 and user_id=$2`,
		id, userId, data.Name, data.Text, data.Periodic, data.DayOfWeek, data.Date,
		data.Time, data.RepeateInterval, data.IsDisabled, data.CategoryId,
		data.IsAutodisable, utcPointer(data.AutodisableAt),
	)

	return err
}

func DeleteNotify(q core.Querier, id int, userId int) error {
	_, err := q.Exec(`update notify set is_deleted=true where id=$1 and user_id=$2`, id, userId)
	return err
}

func ResetNotifiesCategory(q core.Querier, userId int, categoryId int) error {
	_, err := q.Exec(
		`update notify set category_id=null where user_id=$1 and category_id=$2 and is_deleted is not true`,
		userId, categoryId,
	)
	return err
}

func DeleteNotifiesByCategory(q core.Querier, userId int, categoryId int) error {
	_, err := q.Exec(
		`update notify set is_deleted=true where user_id=$1 and category_id=$2 and is_deleted is not true`,
		userId, categoryId,
	)
	return err
}

// --- notify <-> acceptor links ---

func ReplaceNotifyAcceptors(q core.Querier, notifyId int, acceptorIds []int) error {
	if _, err := q.Exec(`delete from notify_acceptor where notify_id=$1`, notifyId); err != nil {
		return err
	}

	for _, acceptorId := range acceptorIds {
		_, err := q.Exec(
			`insert into notify_acceptor (notify_id, acceptor_id, is_deleted) values ($1, $2, false)`,
			notifyId, acceptorId,
		)
		if err != nil {
			return err
		}
	}

	return nil
}

func DeleteNotifyAcceptorsByNotifyId(q core.Querier, notifyId int) error {
	_, err := q.Exec(`delete from notify_acceptor where notify_id=$1`, notifyId)
	return err
}

func DeleteNotifyAcceptorsByAcceptorId(q core.Querier, acceptorId int) error {
	_, err := q.Exec(`delete from notify_acceptor where acceptor_id=$1`, acceptorId)
	return err
}

// --- queries of the sender ---

const activeNotifies = ` from notify n where n.is_deleted is not true and n.is_disabled is not true `

// FindNotifiesForMinute returns active notifies (with acceptors) which have
// to be sent at the minute: one query for all kinds of periodicity.
func FindNotifiesForMinute(q core.Querier, minute models.DateData) ([]models.Notify, error) {
	periodics := []string{"everyday", "every_several_minutes", "every_several_hours"}
	if minute.Day == minute.FirstDay {
		periodics = append(periodics, "first_month_day")
	}
	if minute.Day == minute.LastDay {
		periodics = append(periodics, "last_month_day")
	}
	if minute.DayOfWeek >= 1 && minute.DayOfWeek <= 5 {
		periodics = append(periodics, "workday")
	} else {
		periodics = append(periodics, "weekend")
	}

	return queryNotifies(
		q,
		`select `+notifyColumns+activeNotifies+`and n.time=$1 and (
		   n.periodic = any($2)
		   or (n.periodic='once' and n.date=$3)
		   or (n.periodic='day_of_week' and n.day_of_week=$4)
		   or (n.periodic='every_month' and n.date like $5)
		   or (n.periodic='every_year' and n.date like $6)
		 ) order by n.id`,
		minute.Time, pq.Array(periodics), minute.Date, minute.DayOfWeek,
		fmt.Sprintf("%%-%02d", minute.Day),
		fmt.Sprintf("%%-%02d-%02d", minute.Month, minute.Day),
	)
}

// UpdateNotifyTimes sets the next time of several notifies by one statement.
func UpdateNotifyTimes(q core.Querier, ids []int64, times []string) error {
	if len(ids) == 0 {
		return nil
	}

	_, err := q.Exec(
		`update notify n set time = v.time from unnest($1::int[], $2::text[]) as v(id, time) where n.id = v.id`,
		pq.Array(ids), pq.Array(times),
	)

	return err
}

func DisableNotifies(q core.Querier, ids []int64) error {
	if len(ids) == 0 {
		return nil
	}

	_, err := q.Exec(`update notify set is_disabled=true where id = any($1)`, pq.Array(ids))
	return err
}

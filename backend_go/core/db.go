package core

import (
	"database/sql"
	"fmt"

	_ "github.com/lib/pq"
)

const (
	host     = "localhost"
	port     = 8097
	user     = "test"
	password = "test"
	dbName   = "test"
)

var Session *sql.DB = nil

func InitDBConnection() {
	sqlInfo := fmt.Sprintf(
	    "host=%s port=%d user=%s password=%s dbname=%s sslmode=disable",
	    host,
	    port,
	    user,
	    password,
	    dbName,
	)

    db, err := sql.Open("postgres", sqlInfo)

    if err != nil {
        panic(err)
    }

//     defer db.Close()

    err = db.Ping()
    if err != nil {
        panic(err)
    }

    fmt.Println("Successfully connected!")

    Session = db
//     return db
}

// func DbCheck(db sql) {
//     err = db.Ping()
//     if err != nil {
//         panic(err)
//     }
// }

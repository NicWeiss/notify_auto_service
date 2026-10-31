-- Outbox of the notification sender: the matcher puts one row per
-- (notify, acceptor) here, the dispatcher delivers them in parallel with retries.
create table if not exists delivery (
    id bigserial primary key,
    notify_id integer,
    user_id integer,
    system_type varchar not null,
    account varchar not null,
    title varchar not null default '',
    body varchar not null default '',
    status varchar not null default 'new',
    attempts integer not null default 0,
    next_attempt_at timestamp not null,
    locked_at timestamp,
    created_at timestamp not null,
    sent_at timestamp,
    last_error varchar
);

-- the queue itself
create index if not exists ix_delivery_queue on delivery (system_type, next_attempt_at) where status = 'new';
create index if not exists ix_delivery_process on delivery (locked_at) where status = 'process';
create index if not exists ix_delivery_finished on delivery (status, sent_at);

-- the matcher looks for notifies by the minute
create index if not exists ix_notify_active_time on notify (time) where is_deleted is not true and is_disabled is not true;

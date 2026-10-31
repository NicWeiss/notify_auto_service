-- Baseline schema, equal to the result of all former alembic migrations
-- (up to a28090e372c1). Every statement is idempotent.

create table if not exists "user" (
    id serial primary key,
    name varchar,
    email varchar,
    password varchar,
    is_deleted boolean,
    registred_at timestamp
);

create table if not exists system (
    id serial primary key,
    name varchar,
    is_disabled boolean,
    is_system boolean,
    help varchar,
    type varchar,
    is_deleted boolean
);

create table if not exists acceptor (
    id serial primary key,
    name varchar,
    system_id integer references system (id),
    user_id integer references "user" (id),
    account varchar,
    is_disabled boolean,
    is_system boolean,
    is_deleted boolean
);

create table if not exists category (
    id serial primary key,
    name varchar,
    user_id integer references "user" (id),
    is_hidden boolean,
    is_deleted boolean
);

create table if not exists session (
    id serial primary key,
    session varchar,
    user_id integer references "user" (id),
    client jsonb,
    location jsonb,
    expire_at timestamp not null,
    is_deleted boolean
);

create table if not exists notify (
    id serial primary key,
    name varchar,
    user_id integer references "user" (id),
    text varchar,
    periodic varchar,
    day_of_week integer,
    date varchar,
    time varchar,
    is_disabled boolean,
    category_id integer references category (id),
    is_deleted boolean,
    repeate_interval integer,
    is_autodisable boolean,
    autodisable_at timestamp
);

create table if not exists notify_acceptor (
    id serial primary key,
    notify_id integer references notify (id),
    acceptor_id integer references acceptor (id),
    is_deleted boolean
);

create table if not exists reg_code (
    id serial primary key,
    code varchar,
    email varchar,
    expire_at timestamp not null,
    is_deleted boolean
);

create table if not exists date_operation (
    id serial primary key,
    date_data jsonb,
    create_at timestamp,
    complete_at timestamp,
    start_process_at timestamp,
    status varchar,
    worker_id varchar,
    is_deleted boolean
);

create index if not exists ix_session_session on session (session);
create index if not exists ix_notify_user_id on notify (user_id);
create index if not exists ix_notify_acceptor_notify_id on notify_acceptor (notify_id);
create index if not exists ix_date_operation_status on date_operation (status);

insert into system (id, name, is_disabled, is_system, help, type, is_deleted) values
(100, 'Telegram', false, false, ' Нужен id: <br>Для этого откройте бота <a target="_blank" href="http://t.me/my_id_bot">What is my ID</a>, зaпустите его. Cкопируйте код. <br><span style="color: #e05959;"><br>Внимание, для получения оповещений необходимо подписаться на бота</span> <a target="_blank" href="http://t.me/WeissNotifierSystem_bot"> Notifier</a>', 'tg', false),
(101, 'Email', false, false, 'Укажите адрес электронной почты', 'email', false),
(102, 'Push', false, true, '', 'push', false)
on conflict (id) do nothing;

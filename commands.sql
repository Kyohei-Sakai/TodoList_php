-- データベースの設定
-- ローカル開発環境

-- rootユーザでログイン
-- $ mysql -u root -p

-- mysql> create database todo_myapp;
-- mysql> show databases;

-- dbuserでパスワード（htmr821）を設定
-- mysql> grant all on todo_myapp.* to dbuser@localhost identified by 'htmr821';
-- mysql> use todo_myapp;

drop table todos;

create table todos (
    id int not null auto_increment primary key,
    state tinyint(1) default 0, /* 0: not finished, 1: finished */
    title text,
    created datetime
);

insert into todos (state, title, created) values
(0, 'todo 0', now()),
(0, 'todo 1', now()),
(1, 'todo 2', now());


select * from todos;

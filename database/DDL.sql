/*
 * Copyright (c) 2024. Bennet Becker <dev@bennet.cc>
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 */

create table app_passwords
(
    id       bigint auto_increment
        primary key,
    uid      varchar(256)                         not null,
    password varchar(256)                         not null,
    created  datetime default utc_timestamp() not null,
    comment  text                                 null,
    deleted  datetime                             null,
    constraint app_passwords_pk
        unique (uid, password)
);

create index app_passwords_uid_index
    on app_passwords (uid);

create table log
(
    id        bigint auto_increment
        primary key,
    pwid      bigint                                   not null,
    service   varchar(255)                             null,
    src_ip    text                                     null,
    src_rdns  text                                     null,
    src_loc   text                                     null,
    src_isp   text                                     null,
    timestamp datetime(3) default utc_timestamp(3) not null on update utc_timestamp(3),
    constraint log_app_passwords_id_fk
        foreign key (pwid) references app_passwords (id)
            on delete cascade
);

create definer = roundcube@`%` view app_passwords_with_log as
(
select distinct `pws`.`id`       AS `id`,
                `pws`.`uid`      AS `uid`,
                `pws`.`password` AS `password`,
                `pws`.`created`  AS `created`,
                `pws`.`comment`  AS `comment`,
                `l`.`timestamp`  AS `last_used_timestamp`,
                `l`.`src_ip`     AS `last_used_src_ip`,
                `l`.`src_rdns`   AS `last_used_src_rdns`,
                `l`.`src_loc`    AS `last_used_src_loc`,
                `l`.`src_isp`    AS `last_used_src_isp`,
                `pws`.`deleted`  AS `deleted`
from ((select `app_passwords`.`id`       AS `id`,
              `app_passwords`.`uid`      AS `uid`,
              `app_passwords`.`password` AS `password`,
              `app_passwords`.`created`  AS `created`,
              `app_passwords`.`comment`  AS `comment`,
              `app_passwords`.`deleted`  AS `deleted`
       from `app_passwords`) `pws` left join (with s1 as (select `log`.`id`                                                                      AS `id`,
                                                                 `log`.`pwid`                                                                    AS `pwid`,
                                                                 `log`.`src_ip`                                                                  AS `src_ip`,
                                                                 `log`.`src_rdns`                                                                AS `src_rdns`,
                                                                 `log`.`src_loc`                                                                 AS `src_loc`,
                                                                 `log`.`src_isp`                                                                 AS `src_isp`,
                                                                 `log`.`timestamp`                                                               AS `timestamp`,
                                                                 rank() over ( partition by `log`.`pwid` order by `log`.`timestamp` desc) AS `Rank`
                                                          from `log`)
                                              select `s1`.`id`        AS `id`,
                                                     `s1`.`pwid`      AS `pwid`,
                                                     `s1`.`src_ip`    AS `src_ip`,
                                                     `s1`.`src_rdns`  AS `src_rdns`,
                                                     `s1`.`src_loc`   AS `src_loc`,
                                                     `s1`.`src_isp`   AS `src_isp`,
                                                     `s1`.`timestamp` AS `timestamp`,
                                                     `s1`.`Rank`      AS `Rank`
                                              from `s1`
                                              where `s1`.`Rank` = 1
                                              order by `s1`.`timestamp`) `l` on (`l`.`pwid` = `pws`.`id`))
order by `pws`.`created`);


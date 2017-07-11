<?php

date_default_timezone_set('Asia/Shanghai');

$db = new mysqli();
$db->connect('localhost', 'root', '', 'typecho');

$sql = <<<SQL
select c.title, c.created, c.text, t1.tags,t2.categories from
    typecho_contents as c
left join
    (select r.cid, r.mid, group_concat(m.name) as 'tags' from typecho_relationships r left join typecho_metas m on r.mid=m.`mid` where m.type='tag' group by cid) as t1
    on c.cid=t1.cid
left join 
    (select r.cid, r.mid, group_concat(m.name) as 'categories' from typecho_relationships r left join typecho_metas m on r.mid=m.`mid` where m.type='category' group by cid) as t2
    on c.cid=t2.cid
SQL;

$res = $db->query($sql);

if ($res && $res->num_rows > 0) {
    is_dir('posts') OR mkdir('posts');

    while ($r = $res->fetch_object() ) {
        $created = date('Y-m-d H:i:s',$r->created);
        $content = str_replace('<!--markdown-->','',$r->text);
        $templet = <<<TEMPLET
---
title: $r->title
categories: $r->categories
tags: [$r->tags]
date: $created
---

$content

TEMPLET;
        //替换不合法文件名字符
        $filename = str_replace(array(" ", "?","\\","/" ,":" ,"|", "*" ), '-', $r->title);
        file_put_contents('posts/'.$filename.'.md', $templet);
    }

    $res->free();
}

$db->close();

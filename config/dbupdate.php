<?php
return [
    'data'=>[
        'users'  =>  [
            ['account'=>'admin','password'=>'$2y$10$4DOhSpDbEZYLeNCVyFd3fuyQ3Bq7LACZs4xj4/rQmsv0nEOsqs9t2','nickname'=>'inis','sex'=>'保密','email'=>'admin@qq.com','head_img'=>'//q.qlogo.cn/g?b=qq&nk=97783391&s=640','level'=>'admin','create_time'=>1625587200,'update_time'=>1625587200]
        ],
        'article'=>  [
            ['title'=>'欢迎使用，inis 博客系统','description'=>'当你看到这篇文章，就表示您的系统已经搭建成功！','content'=>'当你看到这篇文章，就表示您的系统已经搭建成功！','font_count'=>18,'users_id'=>1,'create_time'=>1625587200,'update_time'=>1625587200]
        ],
        'music'  =>  [
            ['title'=>'默认歌单','url'=>'https://c.y.qq.com/base/fcgi-bin/u?__=ZhpZFd43','head_img'=>'//q.qlogo.cn/g?b=qq&nk=97783391&s=640','create_time'=>1625587200,'update_time'=>1625587200]
        ],
        'page'   =>  [
            ['title'=>'友链库','alias'=>'links','content'=>'###个人信息[tabs class="nav-bordered"][item name="自我介绍"active="true"][info class="alert-primary bg-white text-primary"]-愿你出走半生，归来仍是少年！[/info][/item][item name="博主友链信息"color="#ff0000"font-weight="bold"][info class="alert-danger bg-white text-danger"]-名称：-地址：-描述：你有多努力，就有多特殊！-头像：`友链申请须知：`**如果你想申请本站的友链，你可以在你的博客上随便找个位置填写上我的友链信息，然后在本页评论区向我提交你的友链信息，我会根据你提交的信息拜访贵方博客，根据评定后在本站相对于的位置填写你的友链信息**[/info][/item][/tabs]##友链申请说明[tabs class="nav-bordered"][item name="内页友链申请"active="true"][info class="alert-warning"]申请条件：`任意博友`[/info][tag class="badge-danger"]内页友链申请不做任何限制，所有人都可以申请[/tag][/item][item name="推荐友链申请"][info class="alert-success"]申请条件：站内文章不少于`20篇`，且质量优良[/info][tag class="badge-danger"]推荐友链限制，为了更好的共同学习和互动[/tag][/item][item name="全站友链申请"color="#ff0000"font-weight="bold"][info class="alert-danger"]申请条件：站内`原创高质量`文章不少于`30篇`，且非常活跃的博主[/info][tag class="badge-danger"]全站友链位置有限[/tag][/item][/tabs]','create_time'=>1625587200,'update_time'=>1625587200]
        ],
        'links_sort'=>[
            ['name'=>'默认分组','description'=>'这是默认的友链分组']
        ],
        'options'=>[
            ['keys'=>'copy','value'=>'备案号'],
            ['keys'=>'description','value'=>'INIS API SYSTEM'],
            ['keys'=>'domain','value'=>'*'],
            ['keys'=>'email_serve','opt'=>'{"port": "587", "smtp": "smtp.qq.com", "email": "", "encry": "tls", "encoded": "UTF-8", "email_cc": "", "nickname": "inis", "password": ""}'],
            ['keys'=>'email_template_1','value'=>"<style>.inis-card{width:550px;height:auto;border-radius:5px;margin:0 auto;box-shadow:0px 0px 20px #888888;position:relative}.inis-card-img{background-image:url(https://picabstract-preview-ftn.weiyun.com/ftn_pic_abs_v3/d238b26f98ee7f65ecce545440c47c892470aa613f3b44018c1e984cccde28d7048273dd4f5aac10e27610adf770c3ae?pictype=scale&from=30013&version=3.3.3.3&uin=1211515059&fname=18.jpeg&size=750);width:550px;height:250px;background-size:cover;background-repeat:no-repeat;border-radius:5px 5px 0px 0px}.inis-card-body{background-color:white;line-height:180%;padding:0 15px 12px;width:520px;margin:10px auto;color:#555555;font-family:'Century Gothic','Trebuchet MS','Hiragino Sans GB',微软雅黑,'Microsoft Yahei',Tahoma,Helvetica,Arial,'SimSun',sans-serif;font-size:12px;margin-bottom:0px}.inis-card-body > h2{font-size:14px;font-weight:normal;padding:13px 0 10px 8px}.inis-card-body > h2 > a{text-decoration:none;color:#ff7272}.inis-row{padding:0 12px 0 12px;margin-top:18px}.comment{background-color:#f5f5f5;border:0px solid #DDD;border-radius:5px;padding:10px 15px;margin:18px 0}.comment > img{margin:0px 6px 5px 6px;width:25px}.comment > a{text-decoration:none;color:#ff7272}.inis-more{text-decoration:none;color:rgb(255,255,255);width:40%;text-align:center;background-color:rgb(255,114,114);height:40px;line-height:40px;box-shadow:3px 3px 3px rgba(0,0,0,0.3);display:block;margin:auto}.inis-font{color:#8c8c8c;font-family:'Century Gothic','Trebuchet MS','Hiragino Sans GB',微软雅黑,'Microsoft Yahei',Tahoma,Helvetica,Arial,'SimSun',sans-serif;font-size:10px;width:100%;text-align:center;padding-bottom:1px}</style><div class=inis-card><div class=inis-card-img></div><div class=inis-card-body><h2>您的<a>《{article}》</a>有了新的评论：</h2><div class=inis-row><p><strong>{nickname}</strong>&nbsp;给您的评论：</p><p class=comment>{text}</p><p>详细信息：</p><p class=comment>IP：{ip}<br />邮箱：<a href=mailto:{email}>{email}</a></p></div></div><a class=inis-more href={admin_url} target=_blank>查看回复的完整內容</a><div class=inis-font><p>©2020-2021 Copyright {site}</p></div></div>"],
            ['keys'=>'email_template_2','value'=>"<style>.inis-card{width:550px;height:auto;border-radius:5px;margin:0 auto;box-shadow:0px 0px 20px #888888;position:relative;padding-bottom:5px}.inis-card-img{background-image:url(https://picabstract-preview-ftn.weiyun.com/ftn_pic_abs_v3/d238b26f98ee7f65ecce545440c47c892470aa613f3b44018c1e984cccde28d7048273dd4f5aac10e27610adf770c3ae?pictype=scale&from=30013&version=3.3.3.3&uin=1211515059&fname=18.jpeg&size=750);width:550px;height:300px;background-size:cover;background-repeat:no-repeat;border-radius:5px 5px 0px 0px}.inis-head-title{width:200px;height:40px;background-color:rgb(255,114,114);margin-top:-20px;margin-left:20px;box-shadow:3px 3px 3px rgba(0,0,0,0.3);color:rgb(255,255,255);text-align:center;line-height:40px}.inis-card-body{background-color:white;line-height:180%;padding:0 15px 12px;width:520px;margin:30px auto;color:#555555;font-family:'Century Gothic','Trebuchet MS','Hiragino Sans GB',微软雅黑,'Microsoft Yahei',Tahoma,Helvetica,Arial,'SimSun',sans-serif;font-size:12px;margin-bottom:0px}.inis-card-body > h2{font-size:14px;font-weight:normal;padding:13px 0 10px 8px}.inis-card-body > h2 > a{text-decoration:none;color:#ff7272}.inis-row{padding:0 12px 0 12px;margin-top:18px}.comment{background-color:#f5f5f5;border:0px solid #DDD;border-radius:5px;padding:10px 15px;margin:18px 0}.comment > img{margin:0px 6px 5px 6px;width:25px}.inis-font{color:#8c8c8c;font-family:'Century Gothic','Trebuchet MS','Hiragino Sans GB',微软雅黑,'Microsoft Yahei',Tahoma,Helvetica,Arial,'SimSun',sans-serif;font-size:10px;width:100%;text-align:center;}.inis-more{text-decoration:none;color:#FFF;width:40%;text-align:center;background-color:#ff7272;height:40px;line-height:35px;box-shadow:3px 3px 3px rgba(0,0,0,0.30);margin:-10px auto;display:block;}</style><div class=inis-card><div class=inis-card-img></div><div class=inis-head-title>亲爱的{nickname}</div><div class=inis-card-body><h2>您在<a href={site_url}target=_blank>《{article}》</a>的评论有了新的回复：</h2><div class=inis-row><p>您的评论：</p><p class=comment>{text}</p><p><strong>{author}</strong>&nbsp;给您的回复：</p><p class=comment>{content}</p></div></div><div class=inis-font style='word-wrap:break-word;margin-top:-30px;'><p style=padding:20px>萤火虫消失之后，那光的轨迹仍久久地印在我的脑际。那微弱浅淡的光点，仿佛迷失方向的魂灵，在漆黑厚重的夜幕中彷徨。——《挪威的森林》村上春树</p></div><a class=inis-more href={site_url}target=_blank>查看回复的完整內容</a><div class=inis-font><p style=margin-top:30px>本邮件为系统自动发送，请勿直接回复~</p></div><div class=inis-font><p>©2020-2021 Copyright{site}</p></div></div>"],
            ['keys'=>'email_template_3','value'=>'<div style="margin: 0 auto;width: 800px;"><table border="0"cellspacing="0"cellpadding="0"width="800"bgcolor="#0092ff"height="66"><tbody><tr><td width="50"></td><td width="750"><img style="WIDTH: 135px"src="{domain}/index/assets/images/logo-1.png"></td></tr></tbody></table><table style="FONT-FAMILY: 黑体; FONT-SIZE: 10pt"border="0"cellspacing="0"cellpadding="50"width="800"><tbody><tr><td width="800"><div><div style="FONT-SIZE: 11pt">{email}，您好！</div><br><div style="FONT-SIZE: 11pt">以下是您用于验证身份的验证码，请在<span style="color:red">{valid_time}内</span>输入并完成验证。如非本人操作，请忽略此邮件。</div><br><br><div><span style="COLOR: #0094ff; FONT-SIZE: 40pt">{code}</span></div><br><br><hr style="BORDER-BOTTOM: #808080 0px dashed; BORDER-LEFT: #808080 0px dashed; HEIGHT: 1px; BORDER-TOP: #808080 1px dashed; BORDER-RIGHT: #808080 0px dashed"><br><div style="COLOR: #808080">此邮件由系统自动发出，系统不接受回信，因此请勿直接回复。<br>安全使用您的帐号注意事项：<br>1、请不要在其他网站上使用相同的邮箱和密码进行注册。<br>2、请不要告知任何人您的帐号密码信息。<br><br>如果您错误的收到本电子邮件，请您忽略上述内容。</div><br><hr style="BORDER-BOTTOM: #808080 0px dashed; BORDER-LEFT: #808080 0px dashed; HEIGHT: 1px; BORDER-TOP: #808080 1px dashed; BORDER-RIGHT: #808080 0px dashed"><div><br></div><div style="TEXT-ALIGN: right; FONT-SIZE: 11pt">{site}</div><div style="TEXT-ALIGN: right; FONT-SIZE: 11pt">{time}</div></div></td></tr></tbody></table></div>'],
            ['keys'=>'keywords','value'=>'API,INIS'],
            ['keys'=>'site_conf','opt'=>'{"token": {"open": 0, "value": "", "status": 0}, "domain": {"status": 0}}'],
            ['keys'=>'site_ico','value'=>'//q.qlogo.cn/g?b=qq&nk=97783391&s=640'],
            ['keys'=>'site_img','value'=>'//q.qlogo.cn/g?b=qq&nk=97783391&s=640'],
            ['keys'=>'site_url'],
            ['keys'=>'title','value'=>'INIS API']
        ]
    ]
];
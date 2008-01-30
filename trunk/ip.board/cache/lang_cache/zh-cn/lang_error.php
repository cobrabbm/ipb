<?php

/*
+--------------------------------------------------------------------------
|   Invision Power Board v2.3.4 Chinese Language Package
|   =======================================================================
|   作者: Skylook
|   =======================================================================
|   网站: http://www.ipbchina.com
|   发布: 2008-01-25
|   语言包版权归 IPBChina.COM 所有
+--------------------------------------------------------------------------
*/

$lang = array (

'awaiting_coppa'			=> '我们必须收到您的监护人的许可证明才能为您创建帐号. 收到证明后, 我们将会立即创建您的帐号并且给您的注册信箱发送通知信件 ',

// 2.3
'han_login_create_failed'	=> "当试图创建您的帐号信息时发生了一个不可恢复的错误. 系统返回的错误如下:<br /><#EXTRA#>",
'han_login_pass_failed'		=> "更改您的用户密码的操作失败. 请您联系系统管理员.",

// 2.2.2

'ucp_birthday_legal_date'   => "您所填写的生日日期非法. 比如, 您可能填写了类似 2月30日 这样的日期.",
'search_word_long' 			=> "您搜索的关键字超过了 <#EXTRA#> 个字符. 请返回并且简短关键字长度.",


// 2.2 Edited
'member_in_add_book'		=>	"您已经在通讯录中添加了该会员",
// 2.2
'help_no_id'				=> "该帮助文件不存在",
'err_attach_not_attached'   => "您所点击的附件来自一个尚未保存的主题",
'csite_not_enabled'			=> '论坛门户系统尚未启用. 情检查您的论坛后台设置',
'bruteforce_account_lock'	=> "非常抱歉, 因为在系统设定的时间段内错误登录次数超过规定值您的帐号已经被锁定了. <#EXTRA#>",
'md5check_failed'           => "非常抱歉, MD5校验号不匹配",

'cannot_block'				=> "您没有拒收该会员短消息的权限.",
'invalid_use'				=> "处理申请的过程中发生错误. 请返回后重试, 或者联系管理员以获得帮助.", 
'no_usepm_member_full' 		=> "这封短消息无法正常发送因为该用户的收件箱已满.",

//2.1
'ucp_name_change'   => '非常抱歉, 24 小时内您只能更改一次昵称.',
'topic_rate_no_perm' => '您没有权限给该主题评分',
'cal_no_perm'        => '您没有权限查看该日历或者该日历不存在',
'cp_admin_user'    => "您不能在这个控制面板里面编辑管理员的个人档案",

// 2.1 changed

'no_usepm_member' =>	"短消息无法发送, 因为收件人已关闭短消息功能或者收件箱已满.",
'search_word_short' => "您所搜索的关键词少于 <#EXTRA#> 个字符或者您正在尝试搜索 'html'、'img' 等禁止的词语. 请返回增加搜索关键词的长度或者使用其他关键词进行搜索.",

//2.0.2

'cp_admin_user'    => "您不能在这里编辑管理员的个人档案",

// 2.0

'erl_email'   => '您的邮件地址',

'reg_no_agree'         => "您没有选中 '我同意' 选项. 如果您同意服务条款, 请确定选中 '我同意' 选项后再继续. 如果您不同意服务条款, 请点击 <a href='<#EXTRA#>'>这里</a>.",

'no_upload_permission' => "您没有上传文件权限",

// added in 1.2.1

'banned_email' => "非常抱歉, 您不能在帐号中使用这个邮件地址, 请选择其他的邮件地址. ",
'subs_no_curid' => "当前的订阅包裹 ID 号不正确, 处理失败.",
'subs_no_upgrade' => "这种付费方式不支持版本更新. 处理过程无法继续. ",
'subs_no_selected' => "您没有选择一个订阅包裹, 请返回重新选择然后继续. ",
'subs_nomethod_selected' => "您没有选择付费方式. 请返回重新选择然后继续. ",
'subs_no_api' => "无法找到必须的 API 文件, 处理过程无法继续. ",
'subs_no_upgrade' => "这种付费方式不支持版本更新. 处理过程无法继续. ",
'subs_fail' => "发生未知的错误. 错误代码 <#EXTRA#>.",
'sub_already' => "您订阅的是一个已经或正在付费的项目. 现在您无法继续. ",
//--



'auth_no_key_not_allow' => "非常抱歉, 在论坛管理员审核通过您的账号之前, 您无法执行此操作",


// added 1.2 B3

'no_flash_av'   => "您没有在头像、帖子、签名中使用 Flash 文件的权限",
'no_warn_max'   => "当前会员的警告等级无法继续改变. 可能已经到达了最高或最低等级",

// added 1.2
'split_too_much' => "您不能拆分所有的主题帖子, 必须在原主题中至少留下一篇帖子",

'ml_error' => "该用户组已经选择不在会员列表中显示.",
'protected_user' => "您无法执行该操作, 因为该会员受到系统保护",
'warned_already' => "该会员已经在规定时间内被警告过一次",
'no_mmid'  => "不是有效的论坛批量管理操作",
'del_post' => "授权错误 - 请返回后重试, 如果您尝试的功能发生错误, 请使用正确的选项.",
'stupid_beggar'  => "所有下拉菜单的选项都被禁止了, 您必须在虚线下选择一个选项, 并且严格遵守相关规定.",
'fd_noneselected' => "您没有选择任何需要清空的短消息文件夹, 请返回重试! ",
'cal_range_wrong' => "非常抱歉, 事件结束日期必须晚于开始日期. ",
'posting_off_susp' => "非常抱歉, 您的帖子被暂时禁止显示, 在日期 <#EXTRA#> 后才能取消禁止.",
'account_susp'  => "您的账号被暂停使用, 在日期 <#EXTRA#> 后才能继续使用该帐号.",
'email_change_v' => "非常抱歉, 您的账号正在等待激活, 请验证您的帐号后重试.",
'is_broken_link' => "非常抱歉, 该链接已经失效.",
'pass_no_match'  => "两次输入的密码不匹配, 请返回重试",
'av_no_gallery' => "非常抱歉, 您所选择的头像分类不存在, 请重新选择",
'no_integ'  => "非常抱歉, 该会员当前还没有 Integrity Messenger 信息.",

'file_2_big'   => "非常抱歉, 您的图像尺寸超过规定范围 - 请选择符合规定尺寸的图像后重试",
'no_photo_selected' => "继续下一步前, 请确信您输入了一个正确的图像地址或选择了需要上传的图像文件",
'photo_invalid_ext' => "非常抱歉, 当前系统不允许您使用该格式图片文件作为个人照片.",


// Added in RC2

'no_help_file' => "非常抱歉, 我们没有找到帮助文件, 请检查后重试!",


//--

// Added in RC1

'no_member_mail' => "非常抱歉, 您没有权限通过论坛发送邮件!",

//--

// Added in Beta 3

'no_vote'  => "您没有选择投票选项来进行投票, 请返回并选择您希望的投票选项",

//--- End B3 additions ---

'me_no_forum'   => "您没有任何论坛的管理权限",

'no_name_search_results' => "您所选择过滤的会员名未能找到, 请返回并重试.",


'split_not_enough' => "您必须选择至少一篇帖子来进行拆分或者移动!",

'mt_no_topic'  => "我们未能在数据库中找到与您输入的链接相匹配的主题, 请重试并仔细检查您所输入的信息",
'mt_same_topic' => "您不能够合并相同的主题, 这种做法是无效的",

'forum_no_post_allowed'    => "您不能够将主题转移到此论坛中, 因为该论坛不允许发表帖子",


'no_msn'					=>	"该会员没有有效的 MSN Messenger 账号.",
'no_yahoo'					=>	"该会员没有有效的 Yahoo! Messenger 账号.",

'ccc'  => "您在 '<#EXTRA#>' 中输入了太多的文字, 请返回并检查您的输入",


'search_flood' => "非常抱歉, 管理员启用了限制查询频率的功能. 请等待 <#EXTRA#> 秒后重试.",

'cal_no_events' => "当日没有任何会员生日或者其他事件, 请返回并重试.",
'cal_date_oor'   => "您所选定的时间日期不合法(比如您选择2月30日). 请返回并检查您的输入后重试.",

'cal_title_none' => "事件标题不能少于 2 个字符",
'cal_title_long' => "您输入的事件标题过长, 请限制在 64 个字符以内",

'search_off' => "非常抱歉, 管理员已禁止搜索功能. 请稍后重试",
'reg_off' => "论坛管理员已关闭新会员注册.",

'offline_title' => "论坛关闭",
'offline_login' => "如果您有权限进入已关闭的论坛, 请在下面登录",

'skin_not_found' => "您选择的风格不存在. 可能已被管理员删除, 请返回重新选择其它风格",

'no_view_board' => "您没有查看论坛的权限",

'forum_read_only' => "该论坛为只读论坛, 不允许发表或回复主题.",

'no_av_upload'  => "您没有权限上传头像到服务器",
'no_av_type'    => "您不能上传此类型文件作为头像",
'no_av_name'    => "未输入上传的文件名, 请返回重新输入",

'upload_failed' => "上传失败. 请联系管理员纠正此问题",

'er_log_in_title'  => "您尚未登录, 请在下面登录",
'erl_enter_name'   => "您的会员名称",
'erl_enter_pass'   => "您的登录密码",
'erl_log_in_submit' => "登录",

'er_useful_links'  => "快速链接导航:",
'er_lost_pass'      => "忘记密码恢复",
'er_register'       => "注册会员账号",
'er_contact_admin'  => "联系论坛管理员",
'er_help_files'      => "查看帮助文档",

'no_archive_messages'     => "非常抱歉, 没有找到与关键词相匹配的短消息. 请缩小范围后重试.",

'no_delete_post'           => "非常抱歉, 您不能删除本主题的第一篇帖子. 如果您想将本主题完全删除, 请从以下版主管理选项里选择 '删除主题'.",
'exp_text'                  =>  "非常抱歉, 有错误产生. 如果您不知道如何使用此功能或者不知道错误产生的原因, 请阅读帮助文件来获取更多信息.",
'msg_head'                  =>  "错误返回信息:",

'auth_no_mem'               => "系统无法找到与您输入的 ID 相匹配的会员. 请仔细检查链接以及您在表单中输入的数据是否正确, 如果错误依然存在, 请联系管理员来帮助您.",
'auth_key_wrong'            => "非常抱歉, 您输入的会员验证字符串不正确. 请检查您在表单中输入的链接或数据是否正确. 如果错误依然存在, 请联系管理员来帮助您.",
'auth_no_key'               => "非常抱歉, 该会员不需要验证账号, 请仔细检查输入的内容. 如果不是近期的验证, 则有可能该帐号已经被删除.",

'email_addy_mismatch'       => "非常抱歉, 两次输入的邮件地址不相同, 请返回检查.",

'lp_no_pass'                => "非常抱歉, 系统没有关于这个账号的新密码, 请联系管理员来解决此问题",

'val_key_present'           => "这个账号已处于激活确认之下, 可能是正在进行注册、恢复忘记密码或者更改邮件的操作. 在此操作之前请进行先前的激活操作, 如果您确定账号已被激活或者再次激活, 请联系管理员来解决此问题",

'complete_form'             => "进行下一步操作之前您必须将表单填写完整. 请返回检查您的输入.",

'forum_no_access'					=>	"非常抱歉, 您无权在此论坛查看. 如果这是加密论坛, 请输入密码后进入.",
'name_too_long'					=>	"输入的会员名称过长",
'msg_blocked'					=>	"该会员设置了在论坛不接受短消息",
'no_msg_title'					=>	"请输入信息标题",
'reg_link'					=>	"马上注册!",
'wrong_pass'					=>	"非常抱歉, 您输入的密码错误, 请重新输入.",
'poll_no_guests'					=>	"游客不能投票",
'no_such_user'					=>	"非常抱歉, 没有相关姓名的会员记录, 请检查您的输入是否正确.",
'search_no_input'					=>	"您必须输入搜索关键词!",
'stf_no_msg'					=>	"您必须输入要发送的信息",
'invite_no_name'					=>	"您必须输入您的姓名",
'poll_none_found'					=>	"无法找到此投票",
'no_search_id'					=>	"没有找到相关的 ID 档案,请重试",
'no_guest_new_topics'					=>	"非常抱歉, 论坛管理员设置了游客不能发布主题, 如果您是注册会员, 请登录",
'no_user'					=>	"非常抱歉,无法找到该会员",
'no_search_words'					=>	"您未输入需要搜索的关键词",
'invalid_login'					=>	"非法登录. 会员名称或登录密码错误, 请检查您的输入是否正确, 注意密码的大小写, 比如 'PaSSwOrD' 和 'password' 是不同的",
'web_too_long'					=>	"个人主页地址过长",
'stf_no_email'					=>	"请输入您朋友的邮件地址以便发送页面",
'mem_id_wrong'					=>	"非常抱歉, 您的会员 ID 格式不正确",
'forum_off'					=>	"非常抱歉, 此论坛已关闭",
'no_newer'					=>	"这是最近的主题",
'poss_hack_attempt'					=>	"我们将此项操作视作一种可能的攻击, 您的操作已被记录, 论坛管理员将获得邮件通知",
'not_logged_in'					=>	"您尚未登录",
'int_too_long'					=>	"个人爱好介绍过长",
'no_to_member'					=>	"未能在数据库中找到该会员, 请检查会员名称拼写是否正确",
'invalid_ext'					=>	"论坛不允许使用此种扩展名的图片. 正确格式为: http://www.ipbchina.com/picture.gif,而类似以下格式则是错误的:http://www.ipbchina.com/picture.one.gif",
'subject_long'					=>	"邮件主题过长.",
'move_already_moved'					=>	"该主题已被转移, 因而不能再次进行移动操作. 如果您想再次移动此主题, 请选择此主题转移到目标论坛后的主题进行操作",
'cookie_error'					=>	"非常抱歉, 发生 Cookies 错误, 请重新登录",
'no_attach'					=>	"论坛不允许使用附件",
'no_aol'					=>	"该会员没有有效的 AOL 账号",
'name_too_short'					=>	"输入的会员名称过短",
'missing_files'					=>	"非常抱歉,一些必要的文件可能已经丢失, 如果您在试图查看主题, 可能是因为主题已被转移或删除, 请返回后重试.",
'post_too_long'					=>	"非常抱歉, 您的帖子过长, 请进行精简",
'locked_topic'					=>	"非常抱歉, 主题已被关闭",
'flash_url'					=>	"无效的 Flash 动画地址",
'profile_guest'					=>	"我们无法在数据库中找到该会员, 可能该会员已被删除.",
'stf_no_subject'					=>	"您必须输入一个邮件标题",
'not_icq_number'					=>	"无效的 ICQ 帐号",
'rp_noemail'					=>	"在发送报告之前, 您必须输入一个有效的邮件地址",

'upload_too_big'					=>	"您要上传文件的大小超过了管理员设置您所在用户组所允许上传文件的最大限额.",

'user_exists'					=>	"会员名已存在, 请重新选择",
'no_msg_chosen'					=>	"您没有选择任何短消息, 您可以通过每条短消息右侧的复选框进行选择",
'error_back'					=>	"返回",
'log_in_yes'					=>	"您当前登录的会员是:",
'flash_number'					=>	"Flash 动画的高度和宽度必须为数字",
'no_avatar'					=>	"管理员已经设置禁止在本论坛使用头像",
'poll_you_voted'					=>	"您已经在此主题中投过票了",
'not_url_photo'					=>	"无效的图片链接地址. 论坛不允许在 [IMG] 标签中引用动态链接",
'no_action'					=>	"请不要调整生成的链接地址. 如果您在此出现错误, 请返回重试",
'no_guest_posting'					=>	"论坛管理员设置了游客不能发表帖子, 如果您是注册会员, 请登录",
'invalid_email'					=>	"请输入有效的邮件地址",
'no_start_polls'					=>	"您不能发表投票",
'move_no_forum'					=>	"未选择目标论坛",
'no_use_messenger'					=>	"本论坛禁止您使用短消息功能",
'no_msg'					=>	"请输入短消息内容",
'no_dynamic'					=>	"非常抱歉, [IMG] 标签不允许使用动态链接",
'no_rules_show'					=>	"没有此论坛的常见问题/规定条款文件",
'membername_none'					=>	"非常抱歉, 我们无法找到该会员名的账号",
'log_in_now'					=>	"立即登录!",
'no_subject'					=>	"您未输入邮件的标题",
'pass_too_long'					=>	"输入的密码过长",
'username_short'					=>	"输入的会员名过短",
'already_sub'					=>	"您已经订阅此主题或论坛",
'forgot_pass'					=>	"忘记密码?",
'no_older'					=>	"这是最早的主题",
'stf_no_name'					=>	"您必须输入朋友的姓名以便发送此页面",
'no_view_topic'					=>	"您无权查看此主题",
'IBForums_error'					=>	"论坛错误",
'no_replies'					=>	"非常抱歉, 您无权回复此主题",
'error_title'					=>	"论坛信息",
'no_chosen_member'					=>	"请输入或选择您要发送短消息的收件人姓名!",
'pass_blank'					=>	"请输入密码",
'flood_control'					=>	"论坛设置了灌水限制, 请等待 <#EXTRA#> 秒后再发帖子",
'no_message'					=>	"请输入邮件正文",
'member_in_add_book'					=>	"您已经在通讯录中添加了该会员",
'posting_off'					=>	"您的发帖权限已被禁止",
'move_same_forum'					=>	"您不能在同一个论坛内移动主题",
'error_txt'					=>	"出现错误",
'rp_nomsg'					=>	"请输入发送给版主的信息",
'too_many_images'					=>	"非常抱歉, 您帖子中包含图片的数目超过了论坛的限制",
'invite_no_email'					=>	"请输入邮件地址",
'not_op'					=>	"非常抱歉, 您无权编辑此信息",
'no_poll_reply'					=>	"投票创建者将此创建为仅允许投票的主题",
'request_hack'					=>	"某些数据与论坛记录不相匹配, 我们将把它认为是一种可能的黑客攻击!",
'photo_too_long'					=>	"照片的地址过长",
'avatar_invalid_ext'					=>	"非常抱歉, 您使用了无效的文件扩展名",
'no_reply_polls'					=>	"您不能在此投票主题内投票",
'no_post'					=>	"您必须输入帖子内容",
'calendar_not_all'					=>	"请完整填写全部的生日输入框或者全部留空, 我们不能处理残缺的生日数据...",
'not_authorised'					=>	"非常抱歉, 您尚未激活您的账号, 请检查您注册使用的邮件信箱.",
'no_email_stored'					=>	"非常抱歉, 在我们的记录中未能找到匹配的邮件地址",
'topictitle_too_long'					=>	"您输入的主题标题过长",
'no_such_msg'					=>	"该条短消息不存在",
'wrong_name'					=>	"非常抱歉, 我们未能找到与您输入相匹配的会员名 (可能来自登录论坛或 Session 验证).",
'too_many_emoticons'					=>	"您提交的的内容中表情符号的个数超出论坛限额, 请减少您内容中表情符号的个数",
'no_topic_title'					=>	"您必须输入主题标题",
'guest_abuse'					=>	"我们不能确定您的发帖状态, 如果您是游客, 请重新登录",
'password_no_match'					=>	"您输入的密码和登录资料不匹配",
'already_logged_in'					=>	"该会员已登录!",
'no_guests'					=>	"非常抱歉, 游客不能使用此功能.",
'no_permission'					=>	"非常抱歉, 您无权使用此功能. 如果您尚未登录, 请使用以下表单登录论坛",
'server_too_busy'					=>	"服务器忙, 请稍后重试",
'loc_too_long'					=>	"所在地区输入过长.",
'poll_you_created'					=>	"您已经创建了此投票主题",
'go_back'					=>	"请返回纠正此项错误",
'log_in_no'					=>	"您尚未登录",
'pass_too_short'					=>	"输入的密码太短",
'no_register'					=>	"非常抱歉, 管理员关闭了会员注册",
'move_no_topic'					=>	"该主题不在我们的数据库内, 可能已被删除",
'flash_too_big'					=>	"非常抱歉, 您所发表的 Flash 动画高度或宽度超出限制",
'end_subs_value'					=>	"在 '取消订阅' 输入框中您必须使用数值",
'no_starting'					=>	"非常抱歉, 您无权在此论坛发布新主题",
'no_search_forum'					=>	"您未选择需要搜索的论坛或者您选择了密码保护的论坛. 如果属于后者, 请您确定在搜索之前您已经登录到该密码保护论坛中.",
'not_registered'					=>	"游客不能进行这项操作, 如果您已经注册,请先登录",
'no_icq'					=>	"该会员没有一个有效的 ICQ 帐号",
'not_aol_name'					=>	"无效的 AOL 账号",
'private_email'					=>	"该会员已选择将邮件地址保密",
'user_agent_no_match'					=>	"您在使用并非您登录时所使用的计算机.",
'poll_add_vote'					=>	"投票!",
'you_are_banned'					=>	"非常抱歉, 您被禁止使用此论坛!",
'incorrect_use'					=>	"错误使用了一个论坛文件",
'no_search_results'					=>	"非常抱歉, 我们未能找到任何匹配的结果. 请重试并放宽您的搜索标准.",
'request_error'					=>	"发生错误, 并非所有的必填项都已通过确认链接发送, 请确定输入了完整的链接, 有可能是您的邮件客户端将链接地址切断, 请重试.",
'not_yahoo_name'					=>	"无效的 YIM 账号",
'stf_invalid_email'					=>	"您输入的邮件地址格式不正确",
'pass_link'					=>	"点击此处!",
'no_email'					=>	"您必须输入您的邮件地址来索取您登录的详细资料",
'max_message_from'					=>	"您的短消息信箱已满, 您已经到达了论坛所允许的最大限额, 您必须从您的收件箱或者其它文件夹中删除您所保存的一些短消息.",
'cant_use_feature'					=>	"您不允许使用论坛的这部分功能",
'memid_too_long'					=>	"您的会员 ID 过长",
'moderate_no_permission'					=>	"您无权执行此项功能",
'too_many_img'					=>	"非常抱歉, 您发表的帖子中使用图片的个数超过了论坛的限制",
'no_sub_id'					=>	"非常抱歉, 我们无法找到该主题订阅",
'max_message_to'					=>	"该会员的短消息信箱已满, 因此无法接收新的短消息",
'error_not_registered'					=>	"尚未注册?",
'username_long'					=>	"您输入的会员名称过长",
'memid_too_short'					=>	"您的会员 ID 过短...",
'sig_too_long'					=>	"您的签名过长",
'no_username'					=>	"您必须输入一个会员名称",
'no_avatar_selected'					=>	"未选择头像,请返回修改",
'incorrect_avatar'					=>	"非常抱歉, 这是个无效的头像",
'avatar_invalid_url'					=>	"非常抱歉, 您输入的头像图片链接格式错误.",
'rp_noname'					=>	"在我们发送报告之前您必须输入您的姓名",
'illegal_img_ext'					=>	"非常抱歉, 您不能在图片标签中使用该文件扩展名",
'cannot_remove_dir'					=>	"您不能删除此文件夹",
'email_exists'					=>	"此邮件地址已存在于我们的记录中, 您不能够使用",
'please_complete_form'					=>	"在提交表单之前请全部填写完整",
'invite_no_email_f'					=>	"您必须输入您朋友的邮件地址",
'move_no_source'					=>	"未选择源论坛",
'log_in'					=>	"尚未登录?",
'data_incorrect'					=>	"某项数据输入栏含有错误的数据类型, 请仔细检查您在表单中输入的链接或数据",
);
?>

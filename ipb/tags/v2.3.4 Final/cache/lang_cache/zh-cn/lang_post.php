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

'pro_poll'					=> '投票',
'pro_post'					=> '帖子',
'pro_postopts'				=> '选项',
'pro_attach'				=> '编辑附件',

'qe_full_edit'   			=> "完整编辑模式",

'reason_for_edit'			=> "编辑原因",
'captcha_explain'			=> "请输入图片中给出的 6 个字符的验证码. 如果您无法看到验证码请点击图片重新生成(区分大小写). ",

'attach_space_used'			=> "附件空间已使用",
'attach_space_of'			=> "总计空间",
'max_single_attach'			=> "单个附件上传限制",
'attach_select_file'		=> "请指定一个文件",
'attach_but_upload'			=> "上传",
'attach_manage_current'		=> "上传文件管理",
'attach_header'				=> "附件上传",

'attach_js__no_items'		=> "当前没有任何文件",
'attach_js__uploading'		=> "附件正在上传...",
'attach_js__init'			=> "附件系统初始化...",
'attach_js__upload_success'	=> "附件上传成功. 您可以在 '管理已上传文件' 菜单中进行管理",
'attach_js__upload_fail'	=> "附件上传失败. 请联系管理员检查论坛权限和相关设置",
'attach_js__upload_fail1'	=> "附件上传失败. 文件大小大于当前可用空间",
'attach_js__upload_fail2'	=> "附件上传失败. 您没有上传这一类型文件的权限",
'attach_js__upload_fail3'	=> "附件上传失败. 请联系管理员检查上传文件夹是否存在",
'attach_js__upload_fail4'	=> "附件上传失败. 请联系管理员检查上传文件夹是否可写",
'attach_js__upload_fail5'	=> "您没有选择上传的附件",
'attach_js__ready'			=> "附件系统初始化完毕",
'attach_js__delete'			=> "删除附件",
'attach_js__insert'			=> "插入到帖子中",
'attach_js__confirm'		=> "继续进行删除操作?",
'attach_js__isremoved'		=> "附件已删除",
'attach_js__removing'		=> "正在删除附件...",



'poll_multichoice'			=> "多选投票? (允许会员选择多于 1 个选项) ",
'poll_only_title'			=> "只允许投票",
'poll_only_desc'			=> "您是否希望在本主题中禁止回帖而只能投票?",

'domain_not_allowed'		=> "您输入了一个管理员禁止链接的地址",
'too_many_quotes'			=> "您的帖子中引用个数超出限制",

// 2.2
'quote_mismatch'			=> "引用打开和关闭标签个数不相匹配. 请您检查并且修正.",
'merge_too_many_img'		=> "非常抱歉, 如果和前一主题合并, 主题中的图片个数将超出许可",
'merge_too_many_emoticons'	=> "您刚刚发表的帖子中表情符号个数超出许可. 请您减少帖子中的表情符号数目",
'guest_captcha'				=> "验证码",
'alt_codebit'				=> "验证码",
'alt_loadingimg'			=> "正在加载图片",
'err_reg_code'      		=> "验证码错误, 一个新的验证码已生成, 请重新输入",
'reg_code_enter'			=> "管理员要求所有的游客在发帖时必须输入验证码. 请输入下面的验证码然后点击 '添加回复' 来发表.",


'ignore_first_line'    => "您已经选择忽略所有帖子来自:",


//v2.1 NEW

'qe_complete_edit' => "编辑完成",
'qe_cancel_edit'   => "取消编辑",

'attach_button'			  => '插入到帖子中',
'attach_button_title'	  => '将该附件的标签插入到帖子中',
'picons_none'             => '[ 不使用 ]',
'calendar_delete_t'       => '选择这个选项以在提交后删除此事件',
'calendar_group_filter_t' => '<br />请选择可以查看本事件的用户组 (管理员可以查看任何事件), 如不选任何用户组, 则默认为任何用户组都可以查看<br />(可以多选)',
'mod_open_time'           => '打开时间',
'mod_close_time'          => '关闭时间',
'mod_date_format'         => 'MM/DD/YYYY',
'mod_time_format'         => 'HH:MM',
'post_optional'           => '(可选)',
'poll_tt_add_question'    => '添加投票问题',
'poll_tt_add_choice'      => '添加投票选项',
'poll_tt_remove_question' => "删除问题",
'poll_tt_remove_choice'   => "删除选项",
'poll_tt_confirm'         => "请确认这个操作",
'poll_tt_stat_lang'       => "您还可以添加 <%1> 个问题, 每个问题可以有 <%2> 个选项.",

'poll_manage_link'        => "点击这里管理这个主题的投票选项",
'poll_fs_title'           => '投票标题',
'poll_fs_content'         => '投票内容',
'poll_fs_close'           => '关闭投票表单',
'poll_fs_options'		  => '投票选项',

//v2.1 CHANGED



//v2.0

'upload_text'       =>	"<strong>附件上传</strong><br />您的剩余附件空间大小为:",
'upload_unlimited'  => "<em>无限制大小</em>",
'invalid_mime_type' =>	"上传失败,该类型文件不被接受.",
'upload_to_big'     => "上传失败,文件大小超过限制.",

'pp_nohtml'   => 'HTML 禁用',
'pp_html1'    => 'HTML 启用 - 手动断行方式',
'pp_html2'    => 'HTML 启用 - 自动断行方式',

'button_add_attachment' => "上传附件",
'attach_remove'     => '删除',
'attach_space_left' => "您在本帖中已经使用了 %s 附件空间.",
'custom_tags_incorrect' => "BBCODE 发生错误,开放标签和关闭标签不匹配.",
'custom_tags_incorrect2' => "BBCODE 发生错误,您可能标签用法错误.您可能错误的使用了标签,如原本应该是 [TAG] 标签您写成了 [TAG=],反之亦然.",


//

// v1.2
'enable_track' => "当有新回复后 <strong>发送</strong> 邮件通知?",
'already_sub'  => "您将收到回复通知邮件",
'mod_pinclose' => "置顶 & 关闭主题",
'posting_poll' => "正在发布投票",

'po_options' => "主题选项",

'poll_tag_allowed'			=> "[URL] 和 [IMG] 标签允许使用",

'eu_keep'					=> "保留当前的附件",
'eu_delete'					=> "删除附件",
'eu_new'					=> "用一个新的附件替换",

'append_edit'				=> "在此帖中 <b>添加</b> '被编辑过' 的说明行?",
'edit_ops'					=> "编辑选项",


'unreg_namestuff'			=> "未注册会员信息",

mod_options					=> "高级选项",

mod_close					=> "关闭主题",
'mod_move'					=> "移动主题",
mod_pin						=> "置顶主题",
mod_nowt					=> "(不做处理)",

'msg_no_title'				=> "您必须输入一个主题标题",

'tt_topic_settings'			=> "主题设置",
'tt_options'				=> "功能选项",
'tt_poll_settings'			=> "投票设置",

'tt_subject'				=> "主题订阅回复通知",
'ft_subject'   => "论坛订阅 新主题通知",

'upload_failed'				=> "上传文件失败,因为论坛 'upload' 文件夹没有设置正确的属性,请联系管理员并报告这个错误.",



post_to_quote_txt					=>	"您可以在这里编辑引用帖子",
submit_poll					=>	"发表投票主题",


submit_new					=>	"发布主题",



enabled					=>	"启用",

errors_found					=>	"发生以下错误",
post_edited					=>	"帖子编辑成功",

replying_in					=>	"回复于",


email_address					=>	"您注册的邮件地址",

no_post						=>	"您必须输入帖子内容",

submit_reply				=>	"发表回复",
stat_allow_img				=>	"[IMG] 标签:",
original_post				=>	"最初的帖子",
html_on						=>	"帖子 HTML 代码 <b>允许</b>",
please_log_in				=>	"您无权查看此论坛",
reply_added					=>	"您的回复已添加",
no_topic_title				=>	"请输入标题,长度必须大于 2 个字符",
too_many_emoticons			=>	"您的帖子使用表情数超过限制,请减少您在帖子内容中使用的表情个数",
no_poll_data				=>	"没有投票选项,这是个没有意义的投票!",
top_txt_new					=>	"发布新主题于",
posting_cond				=>	"发帖条件",



moderate_topic				=>	"主题在发表之前必须经过版主审核",

invalid_ext					=>	"本论坛禁止使用该图片扩展名.正确的格式为:http://www.ipbchina.com/picture.gif,无效的格式: http://www.ipbchina.com/picture.one.gif",
reg_username				=>	"您注册的会员名称",
upload_title				=>	"文件附件",


post_icon					=>	"帖子图标",

moderate_post				=>	"在回复帖子发表之前必须由管理员查看审核",
flash_too_big				=>	"请减小您要粘贴的 Flash 文件尺寸",
guest_name					=>	"输入您的姓名",

topic_title					=>	"主题标题",
flash_url					=>	"您输入的 Flash 动画地址无效",
post_preview				=>	"预览帖子",
non_quotes					=>	"引用的起始标签与关闭标签个数不匹配",
max_length					=>	"帖子最大长度:",
options						=>	"选项",
disabled					=>	"禁用",


no_file_ext					=>	"请确定您要上传的文件有扩展名",
help_cards					=>	"帮助卡片:",
review_topic				=>	"预览完整的主题(在新窗口中打开)",



editing_post				=>	"编辑帖子 ",
posting_new_topic			=>	"发布主题",

flash_number				=>	"Flash 动画的高度和宽度必须是整数",
enable_sig					=>	"是否在帖子中 <b>加入</b> 个人签名?",
top_txt_edit				=>	"编辑帖子在",

poll_to_many				=>	"投票选项超额,请删除一些",



last_posts					=>	"最近 10 篇帖子 [ 降序排列 ]",
top_txt_poll				=>	"创建投票",
too_many_img				=>	"非常抱歉,您所发表的图片数超出规定范围",
topic_desc					=>	"主题描述",

no_dynamic					=>	"非常抱歉,在 [IMG] 标签中不允许使用动态图片",

post_options				=>	"发帖选项",


click_smilie				=>	"表情符号",
button_spell				=>	"拼写检查",

topic_title_long			=>	"主题标题不能超过 50 个字符",


new_post_added				=>	"您的帖子已发表",
top_txt_reply				=>	"回复:",
posted_on					=>	"发表时间",
post						=>	"输入您的帖子内容",
poll_not_enough				=>	"投票应该至少有两个选项",
post_rules					=>	"发帖规则",

post_icon_txt				=>	"您可以选择一篇帖子图标",
submit_edit					=>	"提交更改",

enable_emo					=>	"是否在帖子中 <b>使用</b> 表情符号?",
quoting_post				=>	"引用帖子 ",

email_title					=>	"主题回复通知",
button_preview				=>	"预览帖子",

);
?>

<?php

namespace App;

class ErrorCode
{
    const CODE_DEFAULT = 10000; // 默认错误代码
    const CAN_NOT_CHANGE_INVITER = 10001; // 不能修改邀请人
    const BOUND_PHONE_VERIFY_CODE_EXPIRES = 10002; // 已绑定手机号的验证码已过期
    const LOGIN_IN_OTHER_DEVICE = 10003; // 账号在其它设备登录
}

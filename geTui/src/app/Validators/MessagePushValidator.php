<?php

namespace GeTui\App\Validators;

use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\LaravelValidator;

class MessagePushValidator extends LaravelValidator
{

    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'title' => 'required|max:100',
            'content' => 'required|max:250',
            'options' => 'max:250',
            'origin_url' => 'url',
            'is_push_type' => 'required',
        ],
        ValidatorInterface::RULE_UPDATE => [],
    ];


    protected $errors = [
        'title.required' => '消息标题必须！',
        'content.required' => '消息内容必须！',
        'title.max' => '标题内容太长，请不要超过100个字符！',
        'content.max' => '消息内容太长，请不要超过255个字符！',
        'options.max' => '一次性指定用户过多！',
        'origin_url.url' => '请输入正确的Url',
        'is_push_type.required' => '请选择跳转目的地',
    ];
}

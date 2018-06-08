<?php

/**
 * Class IGtNotify
 */
class IGtNotify {

    /**
     * 通知标题
     * @var
     */
    var $title;

    /**
     * 通知内容
     * @var
     */
    var $content;

    /**
     * 通知内容中携带的透传内容
     * @var
     */
    var $payload;

    /**
     * @return mixed
     */
    public function get_title()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function set_title($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function get_content()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function set_content($content)
    {
        $this->content = $content;
    }

    /**
     * @return mixed
     */
    public function get_payload()
    {
        return $this->payload;
    }

    /**
     * @param mixed $payload
     */
    public function set_payload($payload)
    {
        $this->payload = $payload;
    }
}
<?php
namespace ShaoZeMing\GeTui;

/**
 *  Message.php
 *
 * @author szm19920426@gmail.com
 * $Id: message.php 2017-08-16 下午7:38 $
 */
class Message
{
     protected $content;
     protected $title;

    /**
     * @param mixed $mobile
     *
     * @return Message
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
}

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $content
     *
     * @return Message
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
}

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }
}
<?php

namespace ElfSundae\BearyChat;

class Message
{
    /**
     * The GearyChat client for sending message.
     *
     * @var \ElfSundae\BearyChat\Client
     */
    protected $client;

    /**
     * The text to be sent with the message.
     *
     * @var string
     */
    protected $text;

    /**
     * The notification for the text.
     *
     * @var string
     */
    protected $notification;

    /**
     * Indicates the text field should be parsed as markdown syntax.
     *
     * @var bool
     */
    protected $markdown = true;

    /**
     * The channel that the message should be sent to.
     *
     * @var string
     */
    protected $channel;

    /**
     * The user that the message should be sent to.
     *
     * @var string
     */
    protected $user;

    /**
     * The attachments to be sent.
     *
     * @var array
     */
    protected $attachments = [];

    /**
     * The default values for each attachment.
     *
     * @var array
     */
    protected $attachmentDefaults = [];

    /**
     * Create a new message.
     *
     * @param  \ElfSundae\BearyChat\Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;

        $this->configureDefaults($client->getMessageDefaults());
    }

    /**
     * Get the text.
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set the text.
     *
     * @param  string  $text
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text ? (string)$text : null;

        return $this;
    }

    /**
     * Get the notification.
     *
     * @return string
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * Set the notification.
     *
     * @param  string  $notification
     * @return $this
     */
    public function setNotification($notification)
    {
        $this->notification = $notification ? (string)$notification : null;

        return $this;
    }

    /**
     * Get the markdown.
     *
     * @return bool
     */
    public function getMarkdown()
    {
        return $this->markdown;
    }

    /**
     * Set the markdown.
     *
     * @param  bool $markdown
     * @return $this
     */
    public function setMarkdown($markdown)
    {
        $this->markdown = (bool)$markdown;

        return $this;
    }

    /**
     * Enable markdown syntax for text.
     *
     * @return $this
     */
    public function enableMarkdown($enable = true)
    {
        return $this->setMarkdown($enable);
    }

    /**
     * Disable markdown syntax for text.
     *
     * @return $this
     */
    public function disableMarkdown()
    {
        return $this->setMarkdown(false);
    }

    /**
     * Get the channel which the message should be sent to.
     *
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Set the channel which the message should be sent to.
     *
     * @param  string  $channel
     * @return $this
     */
    public function setChannel($channel)
    {
        $this->channel = $channel ? (string)$channel : null;

        return $this;
    }

    /**
     * Set the channel which the message should be sent to.
     *
     * @param  string  $channel
     * @return $this
     */
    public function toChannel($channel)
    {
        return $this->setChannel($channel);
    }

    /**
     * Get the user which the message should be sent to.
     *
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the user which the message should be sent to.
     *
     * @param  string  $user
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user ? (string)$user : null;

        return $this;
    }

    /**
     * Set the user which the message should be sent to.
     *
     * @param  string  $user
     * @return $this
     */
    public function toUser($user)
    {
        return $this->setUser($user);
    }

    /**
     * Set the target (user or channel) that the message should be sent to.
     *
     * The target may be started with '@' for sending to user, and the channel's
     * starter mark '#' is optional.
     *
     * It will remove all targets if the given target is null.
     *
     * @param  string  $target
     * @return $this
     */
    public function to($target)
    {
        $this->setChannel(null);
        $this->setUser(null);

        if (!empty($target)) {
            $target = (string)$target;

            $mark = mb_substr($target, 0, 1);
            $to = mb_substr($target, 1);

            if ($mark == '@' && !empty($to)) {
                $this->setUser($to);
            } else if ($mark == '#' && !empty($to)) {
                $this->setChannel($to);
            } else {
                $this->setChannel($target);
            }
        }

        return $this;
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * Set the attachments for the message.
     *
     * @param  mixed  $attachments
     * @return $this
     */
    public function setAttachments($attachments)
    {
        $this->removeAttachments();

        if (is_array($attachments)) {
            foreach ($attachments as $attachment) {
                $this->addAttachment($attachment);
            }
        }

        return $this;
    }

    /**
     * Add an attachment to the message.
     *
     * The parameter can be an array that contains attachment's all field.
     * The parameters can also be attachment's fields that ordered with
     * text, title, images and color. Except the text, other parameters
     * can be ignored.
     *
     * @param  mixed $attachment...
     * @return $this
     */
    public function addAttachment($attachment)
    {
        if ($attachment && !is_array($attachment)) {
            $attachment = $this->getAttachmentFromArguments(func_get_args());
        }

        if (!empty($attachment)) {
            $attachment += $this->attachmentDefaults;

            $this->attachments[] = $attachment;
        }

        return $this;
    }

    protected function getAttachmentFromArguments($args)
    {
        $attachment = [];
        $argsCount = count($args);

        if ($argsCount > 0 && !empty($args[0])) {
           $attachment['text'] = (string)$args[0];
        }

        if ($argsCount > 1 && !empty($args[1])) {
            $attachment['title'] = (string)$args[1];
        }

        if ($argsCount > 2 && !empty($args[2])) {
            $images = [];
            $imagesArgument = is_array($args[2]) ? $args[2] : [$args[2]];
            foreach ($imagesArgument as $value) {
                if (is_string($value)) {
                    $images[] = ['url' => $value];
                } else if (is_array($value) && isset($value['url'])) {
                    $images[] = $value;
                }
            }
            if (!empty($images)) {
                $attachment['images'] = $images;
            }
        }

        if ($argsCount > 3 && !empty($args[3])) {
            $attachment['color'] = (string)$args[3];
        }

        return $attachment;
    }

    /**
     * Add an attachment to the message.
     * It alias to `addAttachment`.
     *
     * @param  array  $attachment
     * @return $this
     */
    public function add($attachment)
    {
        return call_user_func_array([$this, 'addAttachment'], func_get_args());
    }

    /**
     * Remove attachment[s] for the message.
     *
     * @param  mixed
     * @return $this
     */
    public function removeAttachments()
    {
        if (func_num_args() > 0) {
            $indices = is_array(func_get_arg(0)) ? func_get_arg(0) : func_get_args();

            foreach ($indices as $index) {
                unset($this->attachments[$index]);
            }

            $this->attachments = array_values($this->attachments);
        } else {
            $this->attachments = [];
        }

        return $this;
    }

    /**
     * Remove attachment[s] for the message.
     * It alias to `removeAttachments`.
     *
     * @param  mixed
     * @return $this
     */
    public function remove()
    {
        return call_user_func_array([$this, 'removeAttachments'], func_get_args());
    }

    /**
     * Configure message defaults.
     *
     * @param  array  $defaults
     */
    protected function configureDefaults(array $defaults)
    {
        if (isset($defaults[MessageDefaults::CHANNEL]))
            $this->setChannel($defaults[MessageDefaults::CHANNEL]);
        if (isset($defaults[MessageDefaults::USER]))
            $this->setUser($defaults[MessageDefaults::USER]);
        if (isset($defaults[MessageDefaults::MARKDOWN]))
            $this->setMarkdown($defaults[MessageDefaults::MARKDOWN]);
        if (isset($defaults[MessageDefaults::NOTIFICATION]))
            $this->setNotification($defaults[MessageDefaults::NOTIFICATION]);

        if (isset($defaults[MessageDefaults::ATTACHMENT_COLOR]))
            $this->attachmentDefaults['color'] = $defaults[MessageDefaults::ATTACHMENT_COLOR];
    }

    /**
     * Convert the message to array.
     *
     * @return array
     */
    public function toArray()
    {
        return array_filter([
            'text' => $this->getText(),
            'notification' => $this->getNotification(),
            'markdown' => $this->getMarkdown(),
            'channel' => $this->getChannel(),
            'user' => $this->getUser(),
            'attachments' => $this->getAttachments(),
        ], function ($value, $key) {
            return !(is_null($value) ||
                     ($key == 'markdown' && $value == true) ||
                     (is_array($value) && empty($value)));
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Send the message.
     *
     * The parameters can be `($text, $markdown, $notification)`, and the $text and
     * the $notification can be `null` that does not modify the exist field.
     * The parameters can also be
     * `($text, $attachment_text, $attachment_title, $attachment_images, $attachment_color)`.
     *
     * @param mixed
     * @return bool
     */
    public function send()
    {
        if ($count = func_num_args()) {
            if (!is_null(func_get_arg(0))) {
                $this->setText(func_get_arg(0));
            }

            if ($count > 1 && is_bool(func_get_arg(1))) {
                $this->setMarkdown(func_get_arg(1));

                if ($count > 2 && !is_null(func_get_arg(2))) {
                    $this->setNotification(func_get_arg(2));
                }
            } else if ($count > 1) {
                call_user_func_array(
                    [$this, 'addAttachment'],
                    array_slice(func_get_args(), 1)
                );
            }
        }

        return $this->client->sendMessage($this);
    }

    /**
     * Send the message to the given target.
     *
     * @param  string|mixed  $target
     * @return bool
     */
    public function sendTo($target)
    {
        $this->to($target);

        return call_user_func_array([$this, 'send'], array_slice(func_get_args(), 1));
    }
}
# BearyChat for PHP

A PHP package for sending message to the [BearyChat][] with the [Incoming Webhook][1].

## Installation

You can install this package using the [Composer][] manager.
```
composer require elfsundae/bearychat
```

Then you may create an Incoming Webhook on your [BearyChat][] team account, and read the [payload format][1].

## Documentation

### Introduction

To send messages, first create a [BearyChat client](src/Client.php) with your webhook URL:

```php
$client = new \ElfSundae\BearyChat\Client('http://hook.bearychat.com/=.../incoming/...');
```

Besides the webhook, you may want to setup some default values for all messages which will be sent with this client:

```php
use ElfSundae\BearyChat\Client;

$client = new Client($webhook, [
    'channel' => 'server-log',
    'attachment_color' => '#3e4787'
]);
```

All defaults keys are listed in [`MessageDefaults`](src/MessageDefaults.php) . You can access message default with `$client->getMessageDefaults($key)`, or retrieve all defaults with `$client->getMessageDefaults()` .

To send a message, just call `sendMessage` with a [message payload][1]:

```php
$client->sendMessage([
    'text' => 'Hi, Elf!',
    'user' => 'elf'
]);

$json = '{"text": "Good job :+1:", "channel": "all"}';
$client->sendMessage($json);
```

In addition to the ugly payload, there are a variety of convenient methods that can work with the payload in [`Message`](src/Message.php) class. Any unhandled methods to a `Client` instance will be sent to a new `Message` instance, and the most of `Message` methods return `Message` itself, so you can chain [message modifications](#message-modifications) to achieve one-liner code.

You can also call the powerful `send` or `sendTo` method with message contents for [sending a message](#sending-message).

```php
$client->to('#all')->text('Hello')->add('World')->send();

$client->sendTo('all', 'Hello', 'World');
```

### Message Modifications

+ **text**: `getText` , `setText($text)` , `text($text)`
+ **notification**: `getNotification` , `setNotification($notification)` , `notification($notification)`
+ **markdown**: `getMarkdown` , `setMarkdown($markdown)` , `markdown($markdown = true)`
+ **channel**: `getChannel` , `setChannel($channel)` , `channel($channel)` , `to($channel)`
+ **user**: `getUser` , `setUser($user)` , `user($user)` , `to('@'.$user)`
+ **attachments**: `getAttachments` , `setAttachments($attachments)` , `attachments($attachments)` , `addAttachment(...)` , `add(...)` , `removeAttachments(...)` , `remove(...)`

As you can see, the `to($target)` method can change the message's target to an user if `$target` is started with `@` , otherwise it will set the channel that the message should be sent to. The channel's starter mark `#` is **optional** in `to` method, which means the result of `to('#dev')` and `to('dev')` is the same.

Method `setAttachments($attachments)` accepts an array of attachments, and each attachment can be an array (attachment payload) or a variable arguments list in order of `text, title, images, color`, and the `images` can be an image URL or an array contains image URLs. **Note:** This type of attachment parameters is also applicable to the method `addAttachment` or `add` .

```php
$client->to('@elf')
->text('message')
->add([
    'text' => 'Content of the first attachment.',
    'title' => 'First Attachment',
    'images' => [
        ['url' => $imageUrl],
        ['url' => $imageUrl2]
    ],
    'color' => '#10e4fe'
])
->add(
    'Content of the second attachment.',
    'Second Attachment',
    [$imageUrl, $imageUrl2],
    'red'
)
->send();
```

To remove attachments, call `removeAttachments` or `remove` with indices.

```php
$message->remove(0)->remove(0, 1)->remove([1, 3])->remove();
```

### Message Presentation

Call `toArray()` method on a Message instance will create an payload array.

```php
$message = $client->to('@elf')->text('foo')->markdown(false)
            ->add('bar', 'some images', 'path/to/image', 'blue');

echo json_encode($message->toArray(), JSON_PRETTY_PRINT);
```

will output:

```json
{
    "text": "foo",
    "markdown": false,
    "user": "elf",
    "attachments": [
        {
            "text": "bar",
            "title": "some images",
            "images": [
                {
                    "url": "path\/to\/image"
                }
            ],
            "color": "blue"
        }
    ]
}
```

### Sending Message

You can call `send` or `sendTo` method on a Message instance to send that message.

The `send` method optional accepts variable number of arguments to quickly change the payload content:

+ Sending a basic message: `send($text, $markdown = true, $notification)`
+ Sending a message with one attachment added: `send($text, $attachment_text, $attachment_title, $attachment_images, $attachment_color)`

The `sendTo` method is useful when you want to change the message's target before calling `send` method.

```php
$client = new Client($webhook, [
    'channel' => 'all'
]);

// Sending a message to the default channel
$client->send('Hi there :smile:');

// Sending a customized message
$client->send('disable **markdown**', false, 'custom notification');

// Sending a message with one attachment added
$client->send('message title', 'Attachment Content');

// Sending a message with an customized attachment
$client->send(
    'message with an customized attachment',
    'Attachment Content',
    'Attachment Title',
    $imageUrl,
    '#f00'
);

// Sending a message with multiple images
$client->send('multiple images', null, null, [$imageUrl1, $imageUrl2]);

// Sending a message to a different channel
$client->sendTo('iOS', '**Lunch Time !!!**');

// Sending a message to an user
$client->sendTo('@elf', 'Where are you?');
```

## License

The BearyChat PHP package is available under the [MIT license](LICENSE).

[1]: https://bearychat.com/integrations/incoming
[BearyChat]: https://bearychat.com
[Composer]: https://getcomposer.org

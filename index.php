<?php


class Message
{

    protected string $token;


    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function mysql_connect()
    {

        return mysqli_connect('localhost', 'user', 'password', 'database');
    }

    public function get_message()
    {
        return json_decode(file_get_contents('php://input'), TRUE);
    }

    public function get_books_from_db($text)
    {
        if ($this->mysql_connect()) {
            $sql = mysqli_query($this->mysql_connect(), "SELECT * FROM `wp_posts` WHERE `post_title` LIKE '%{$text}%' AND `post_status` = 'publish' LIMIT 0,10");

            while ($result = mysqli_fetch_array($sql)) {
                $res = $res . "\n\n📚" . $result['post_title'] . "\nPost: https://website.com/" . $result['post_name'];
                $cnt = mysqli_num_rows($sql);
            }
        } else {
            $res = 'Error mysql connection';
            $cnt = '0';
        }
        return array(
            "result" => $res,
            "count" => $cnt,
        );
    }

    public function send($chat_id, $count, $result)
    {
        $params = [
            'chat_id' => $chat_id,
            'text' => 'Posts found: ' . $count . $result
        ];

        file_get_contents('https://api.telegram.org/bot' . $this->token . '/sendMessage?' . http_build_query($params));
    }
}


$token = '##########################';

$message = new Message($token);
$data = $message->get_message();
$text = $data['message']['text'];
$message->mysql_connect();
$books = $message->get_books_from_db($text);
$message->send($data['message']['chat']['id'], $books['count'], $books['result']);

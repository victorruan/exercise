<?php

class WebServer
{
    protected $ip;
    protected $port;
    protected $webRoot;

    public function __construct($ip, $port, $webRoot)
    {
        $this->ip = $ip;
        $this->port = $port;
        $this->webRoot = $webRoot;
    }

    public function start()
    {
        $fd = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($fd < 0) {
            echo "Error:" . socket_strerror(socket_last_error()) . "\n";
            exit;
        }
        if (socket_bind($fd, $this->ip, $this->port) < 0) {
            echo "BIND FAILED:" . socket_strerror(socket_last_error()) . "\n";
            exit;
        }
        if (socket_listen($fd) < 0) {
            echo "LISTEN FAILED:" . socket_strerror(socket_last_error()) . "\n";
            exit;
        }
        echo $this->ip . ":" . $this->port . "\tserver start\n";
        do {
            $clientFd = null;
            try {
                $clientFd = socket_accept($fd);
            } catch (Exception $e) {
                echo $e->getMessage();
                echo "ACCEPT FAILED:" . socket_strerror(socket_last_error()) . "\n";
            }
            try {
                $requestData = socket_read($clientFd, 1024);
                $response = $this->requestHandler($requestData);
                socket_write($clientFd, $response);
                socket_close($clientFd);
            } catch (Exception $e) {
                echo $e->getMessage();
                echo "READ FAILED:" . socket_strerror(socket_last_error()) . "\n";
            }
        } while (true);
    }

    function requestHandler($requestData)
    {
        echo $requestData;


        list($http_header, $http_body) = explode("\r\n\r\n", $requestData, 2);
        $header_data = explode("\r\n", $http_header);
        list($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], $_SERVER['SERVER_PROTOCOL']) = explode(' ',
            $header_data[0]);

        // QUERY_STRING
        $_SERVER['QUERY_STRING'] = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
        if ($_SERVER['QUERY_STRING']) {
            // $GET
            parse_str($_SERVER['QUERY_STRING'], $_GET);
        }

        unset($header_data[0]);
        foreach ($header_data as $content){
            if (empty($content)) {
                continue;
            }
            list($key, $value)       = explode(':', $content, 2);
            if ($key == "Cookie"){
                parse_str(str_replace('; ', '&', $value), $_COOKIE);
                break;
            }
        }

        $name = $_GET['name']??($_COOKIE['name']??'');
        $pass = $_GET['password']??($_COOKIE['pass']??'');

        if (!empty($name)&&!empty($pass)){
            return $this->showHtml($name,$pass);
        }

        if ($_SERVER['REQUEST_URI'] == "/favicon.ico") {
            return "";
        }

        return $this->loginHtml();
    }

    function showHtml($user,$pass){
        $content = "<form>
用户名：$user<br>
密码：$pass<br>
Welcome!
</form>";
        return "HTTP/1.1 200 OK\r\nContent-Type: text/html; charset=utf-8\r\nSet-Cookie: name={$user}\r\nSet-Cookie: pass={$pass}\r\nContent-Length: " . strlen($content) . "\r\n\r\n" . $content;
    }

    function loginHtml()
    {
        $content = "<form>
用户名：<input name='name'><br>
密码：<input type='password' name='password'><br>
<input type='submit' > 
</form>";
        return "HTTP/1.1 200 OK\r\nContent-Type: text/html; charset=utf-8\r\nContent-Length: " . strlen($content) . "\r\n\r\n" . $content;
    }
}

$server = new WebServer("0.0.0.0", "8080", __DIR__);
$server->start();

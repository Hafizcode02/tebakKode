<?php

namespace App\Http\Controllers;

use App\Gateway\EventLogGateway;
use App\Gateway\QuestionLogGateway;
use App\Gateway\UserGateway;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Log\Logger;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;

class Webhook extends Controller
{
    /**
     * @var LINEBot
     */
    private $bot;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var Response
     */
    private $response;
    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var EventLogGateway
     */
    private $logGateway;
    /**
     * @var UserGateway
     */
    private $userGateway;
    /**
     * @var QuestionGateway
     */
    private $questionGateway;
    /**
     * @var array
     */
    private $user;

    public function __construct(
        Request $request,
        Response $response,
        Logger $logger,
        EventLogGateway $logGateway,
        UserGateWay $userGateway,
        QuestionGateway $questionGateway
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->logger = $logger;
        $this->logGateway = $logGateway;
        $this->userGateway = $userGateway;
        $this->questionGateway = $questionGateway;

        $channel_access_token = "VxYKeAbFGENN1alp0C5PO0kXk5VXiEwQBkMdo7t/AwxmzwqSVpVi4uAF1lvc7b4Ya0+n1PUBwqq8+u/nJ9itb0T1A8H2PE5zmEkKAM/wWEH/kFUAXUNVcATk+XN4SmN5p9xkQctGJYBVVwZplTvCJAdB04t89/1O/w1cDnyilFU=";
        $channel_secret = "b9d833aadaa179f828267bd8ee0a228f";

        // create bot object
        $httpClient = new CurlHTTPClient(getenv($channel_access_token));
        $this->bot = new LINEBot($httpClient, ['channelSecret' => getenv($channel_secret)]);
    }

    public function __invoke()
    {
        // get request
        $body = $this->request->all();

        // debugging data
        $this->logger->debug('Body', $body);

        // save log
        $signature = $this->request->server('HTTP_X_LINE_SIGNATURE') ?: '-';
        $this->logGateway->saveLog($signature, json_encode($body, true));

        return $this->handleEvents();
    }
}

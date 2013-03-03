
<?php

require_once CakePlugin::path('Ratchet') . 'Vendor' . DS . 'autoload.php';
App::uses('CakeWampServer', 'Ratchet.Lib');
App::uses('CakeWampAppServer', 'Ratchet.Lib');
App::uses('PhpSerializeHandler', 'Ratchet.Lib');
App::uses('CakeSession','Model/Datasource');

use Ratchet\Session\SessionProvider;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\Socket\Server as Reactor;
use React\EventLoop\Factory as LoopFactory;
use Symfony\Component\HttpFoundation\Session\Storage\Handler;
//use Ratchet\Server\FlashPolicy;

class WebsocketShell extends Shell {
    
    private $loop;
    private $ioServer;
    private $flashPolicy;
    
    public function __construct($stdout = null, $stderr = null, $stdin = null) {
        parent::__construct($stdout, $stderr, $stdin);
        
        $this->loop = LoopFactory::create();
	$app = new CakeWampAppServer($this, $this->loop);
	
	$context = new React\ZMQ\Context($this->loop);
	$pull = $context->getSocket(ZMQ::SOCKET_PULL);
	$pull->bind('tcp://127.0.0.1:5555'); // Binding to 127.0.0.1 means the only client that can connect is itself
	$pull->on('message', array($app, 'update'));
	
	//Session Storage
	$memcache = new Memcache;
	$memcache->connect('localhost', 11211);
	$session = new SessionProvider(
		new CakeWampServer(
			$app
                ),
		new Handler\MemcacheSessionHandler($memcache,array(
			'prefix' => 'reversix_'
		)),
		array(),
		new PhpSerializeHandler()
	);

        // Flash Policy
        /*$flashSock = new Reactor($this->loop);
        $flashSock->listen(843, '0.0.0.0');
        $policy = new FlashPolicy;
        $policy->addAllowedAccess('*', '*');
        $this->flashPolicy = new IoServer($policy, $flashSock);*/
        
        // Websocket
        $socket = new Reactor($this->loop);
        $socket->listen(8080, '0.0.0.0');
        $this->ioServer = new IoServer(new WsServer($session), $socket, $this->loop);
    }
    
    public function run() {
        $this->loop->run();
    }
    
}
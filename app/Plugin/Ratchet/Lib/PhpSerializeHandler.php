<?php

use \Ratchet\Session\Serialize\PhpHandler;
use Ratchet\Session\Serialize\HandlerInterface;

class PhpSerializeHandler extends PhpHandler implements HandlerInterface {
    
    /**
     * {@inheritdoc}
     */
    function serialize(array $data) {
        return serialize($data);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($raw) {
        return array(
            '_sf2_attributes' => parent::unserialize($raw),
        );
    }
}
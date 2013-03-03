<?php

class RatchetAppController extends AppController {
    
    public function beforeFilter() {
        parent::beforeFilter();
        
	throw NotFoundException;
    }
    
}
WAMP = function(server){
	this.init(server);
}

WAMP.prototype.init = function(server){
	//ab._debugrpc = true;
	//ab._debugpubsub = true;
	this.wsuri = server;
	
	// WAMP session object
	this.session = null;
	
	// When not connected, queue commands
	this.queue = [];
	
	// These commands are critical for the app and must be performed again (before anything else) if disconnected
	// Typically, these are things like subscriptions
	this.requeue = [];
	
	this.connect();
}

WAMP.prototype.connect = function() {
	var This = this;
	
	// establish session to WAMP server
	this.session = new ab.Session(this.wsuri,

		// fired when session has been opened
		function() {
			This.run();
		},

		// fired when session has been closed
		function(reason) {
			This.error(reason);
		}
	);
};

WAMP.prototype.error = function(reason){
	var msg = '';
	
	switch (reason) {
		case ab.CONNECTION_CLOSED:
			msg = $('<div>').addClass('message').html('<span style="color:maroon;font-size:11px;">You have been disconnected.</span>');
			break;
		case ab.CONNECTION_UNREACHABLE:
			msg = $('<div>').addClass('message').html('<span style="color:maroon;font-size:11px;">Connection failed - retrying...</span>');

			// automatically reconnect after 2s
			window.setTimeout(this.connect.bind(this), 2000);
			break;
		case ab.CONNECTION_UNSUPPORTED:
			msg = $('<div>').addClass('message').html('<span style="color:maroon;font-size:11px;">Browser does not support WebSocket</span>');
			
			break;
		case ab.CONNECTION_LOST:
			msg = $('<div>').addClass('message').html('<span style="color:maroon;font-size:11px;">Connection lost; reconnecting...</span>');			

			// automatically reconnect after 2s
			window.setTimeout(this.connect.bind(this), 2000);
			break;
	}
	
	if(msg){
		var scroll = false;
		var div = document.getElementById('messages');
		if(div.scrollTop >= div.scrollHeight - div.clientHeight - 10)
			scroll = true;

		$('#messages').append(msg);

		if(scroll)
			div.scrollTop = div.scrollHeight;
	}
}

WAMP.prototype.run = function(){
	if(!this.session._websocket_connected)
		return;
	
	this.queue = this.requeue.concat(this.queue);
	
	while(this.queue.length){
		var q = this.queue.pop();
		
		switch(q.type){
			case 'subscribe':
				this.subscribe(q.data.topic, q.data.callback)
				break;
			case 'call':
				this.call(q.data.url,q.data.data,q.data.callback);
				break;
		}
	}
}

WAMP.prototype.subscribe = function(topic,callback){
	if(!this.session._websocket_connected){
		this.requeue.push({
			type: 'subscribe',
			data: {
				topic: topic,
				callback: callback
			}
		});
		return;
	}
	
	this.session.subscribe(topic,callback)
}

WAMP.prototype.call = function (url, data, callback, requeue) {
	if(typeof requeue == 'undefined')
		requeue = false;

	if(!this.session._websocket_connected){
		var data = {
			type: 'call',
			data: {
				url: url,
				data: data,
				callback: callback
			}
		}
		
		if(requeue)
			this.requeue.push(data);
		else
			this.queue.push(data);
		return;
	}

        this.session.call('callUrl', {
		'url': url,
		'data': data
	}).then(
		function (res) {
			callback(res[0]);
		}
        );
}
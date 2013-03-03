Lobby = function(controller){
	this.controller = controller;	
	this.init();
}

Lobby.prototype.init = function(){
	//Initialize vars
	this.draw = true;
	this.undraw = false;
	this.undraw_playnow = true;
	
	//Create Canvas
	this.canvas = document.createElement('canvas');
	this.canvas.height = 200;
	this.canvas.width = 190;
	this.canvas.style.position = 'absolute';
	this.canvas.style.right = 0;
	this.canvas.style.top = 0;
	this.context = this.canvas.getContext('2d');
	
	//Place canvas
	this.controller.viewport.appendChild(this.canvas);
	
	//Handle user input
	this.events();
}

Lobby.prototype.events = function(){
	//user clicked on "Play Now!"
	$(this.canvas).mousedown((function(event){		
		if(event.offsetX == undefined){
			event.offsetX = event.pageX - $(this.canvas).offset().left;
			event.offsetY = event.pageY - $(this.canvas).offset().top;			
		}
		
		if(event.offsetX >= 0 && event.offsetX <= 184 && event.offsetY >= 130 && event.offsetY <= 184){
			this.controller.play_now();
		}
	}).bind(this));
}

Lobby.prototype.remove_events = function(){
	$(this.canvas).unbind('mousedown');
}

Lobby.prototype.render = function(time){
	if(this.draw)
		this.draw_lobby();

	if(this.undraw)
		this.undraw_lobby();
	
	//Hide playnow
	if(Game.data && Game.data.GameUser && Game.data.GameUser.length >= 4){
		if(this.undraw_playnow){
			this.undraw_play_now();
		}
	}
}

Lobby.prototype.undraw_play_now = function(){
	//Local Vars
	var context = this.context;
	var canvas = this.canvas;

	context.clearRect(0,125,canvas.width,canvas.height);

	this.remove_events();

	/*
	var gradient = context.createLinearGradient(canvas.width, 110, canvas.width, canvas.height);
	gradient.addColorStop(0, '#333');
	gradient.addColorStop(1, '#333');
	context.fillStyle = gradient;

	context.font = "bold 24pt Arial";

	var padding = 15;
	var textWidth = context.measureText("Play Now!").width;
	var marginLeft = (canvas.width - textWidth - padding * 2)/2;
	context.strokeStyle = "#000000";
	context.lineWidth = 2;
	context.fillRect(marginLeft, 100 + padding * 2, canvas.width - marginLeft * 2, 24 + padding * 2);
	context.strokeRect(marginLeft, 100 + padding * 2, canvas.width - marginLeft * 2, 24 + padding * 2);
	
	context.font = "bold 12pt Arial";
	context.fillStyle = "#ffffff";
	context.fillText("Game has Begun!", canvas.width/2, 18 + 115 + padding * 2);
	*/
}

Lobby.prototype.undraw_lobby = function(){
	//Local Vars
	var context = this.context;
	var canvas = this.canvas;

	context.clearRect(0,0,canvas.width,canvas.height);
	
	this.remove_events();
}

Lobby.prototype.draw_lobby = function(){
	//Local Vars
	var context = this.context;
	var canvas = this.canvas;
	
	//Initiallize ReversiX Info
	context.textAlign = 'center';
	
	context.fillStyle = "#b4bdff";
	context.font = "bold 24pt Arial";
	context.fillText("Reversi", canvas.width/2, 25);

	context.fillStyle = "#ffffff";
	context.font = "bold 18pt Arial";
	context.fillText("to the", canvas.width/2, 50);

	context.fillStyle = "#ffcc00";
	context.font = "bold 24pt Arial";
	context.fillText("eXtreme", canvas.width/2, 80);

	context.fillStyle = "#ffffff";
	context.font = "italic bold 8pt Arial";
	context.fillText("Fast Paced, Multiplayer, Realtime", canvas.width/2, 100);
	
	//Play Now!
	var gradient = context.createLinearGradient(canvas.width, 110, canvas.width, canvas.height);
	gradient.addColorStop(0, '#ffffff');
	gradient.addColorStop(1, '#222222');
	context.fillStyle = gradient;

	context.font = "bold 24pt Arial";

	var padding = 15;
	var textWidth = context.measureText("Play Now!").width;
	var marginLeft = (canvas.width - textWidth - padding * 2)/2;
	context.strokeStyle = "#999999";
	context.lineWidth = 1;
	context.fillRect(marginLeft, 100 + padding * 2, canvas.width - marginLeft * 2, 24 + padding * 2);
	context.strokeRect(marginLeft, 100 + padding * 2, canvas.width - marginLeft * 2, 24 + padding * 2);

	context.fillStyle = "#ffffff";
	context.fillText("Play Now!", canvas.width/2, 24 + 115 + padding * 2);

	context.fillStyle = "#000000";
	context.fillText("Play Now!", canvas.width/2, 24 + 115 + padding * 2 - 1);	
}
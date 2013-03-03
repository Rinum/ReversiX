Player = function(controller){
	this.controller = controller;	
	this.init();
}

Player.prototype.init = function(){
	//Initialize vars
	this.draw = true;
	this.undraw = false;
	
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
}

Player.prototype.render = function(time){
	if(this.draw)
		this.draw_player(time);

	if(this.undraw)
		this.undraw_player();
}

Player.prototype.undraw_player = function(){
	//Local Vars
	var context = this.context;
	var canvas = this.canvas;

	context.clearRect(0,0,canvas.width,canvas.height);
}

Player.prototype.draw_player = function(time){
	//Local Vars
	var context = this.context;
	var canvas = this.canvas;
	var player = this.controller.user;
	
	context.clearRect(0, 0, canvas.width, canvas.height);

	/* User Info */
	context.font = "18pt Arial";
	context.textAlign = 'center';
	context.fillStyle = this.controller.strokeStyle;
	context.fillText(player.username, canvas.width/2, 21);

	context.fillStyle = this.controller.fillStyle;
	context.fillText(player.username, canvas.width/2, 20);

	context.fillStyle = this.controller.fillStyle; //Put user color here
	context.strokeStyle = this.controller.strokeStyle;
	context.lineWidth = 1;
	context.beginPath();
	context.arc(100, 60, 25, 0, Math.PI*2, true);
	context.fill();
	context.stroke();
	
	//Cooldown Timer
	var diff = 5;
	
	if(this.controller.parent.parent.data.GameMove){
		var last = this.controller.parent.parent.data.GameMove[this.controller.user.id];
		if(last){
			last = last['last_attempted'];

			var runtime = time;
			var lastmove = new XDate(last,true).getTime();

			diff = (runtime - lastmove)/1000;
		}
	}	
	
	context.fillStyle = this.controller.fillStyle;
	context.fillRect (2, 146, (diff > 5 ? 5 : diff)/5 * (canvas.width - 4), 23);

	/* Cooldown Timer */
	context.fillStyle = "#ffc819";
	context.font = "18pt Arial";
	context.fillText("Cooldown Timer", canvas.width/2, 135);

	context.strokeStyle = (this.controller.fillStyle == "#ffffff" ? "#000000" : "#FFFFFF");
	context.lineWidth = 2;
	context.strokeRect(1, 145, canvas.width - 2, 25);
}
Ranking = function(controller){
	this.controller = controller;	
	this.init();
}

Ranking.prototype.init = function(){
	//Initialize vars
	this.draw = true;
	
	//Create Canvas
	this.canvas = document.createElement('canvas');
	this.canvas.height = 200;
	this.canvas.width = 190;
	this.canvas.style.position = 'absolute';
	this.canvas.style.right = 0;
	this.canvas.style.top = '200px';
	this.context = this.canvas.getContext('2d');
	
	//Place canvas
	document.getElementById('viewport').appendChild(this.canvas);
}

Ranking.prototype.render = function(time){
	//Local vars
	var board = this.controller.parent.board;
	var context = this.context;
	var canvas = this.canvas;
	var tile = this.controller.parent.tile;
	var pieces = this.controller.parent.pieces;
	var players = this.controller.parent.players;
	
	//Draw axis?
	if(this.draw){
		this.gradient = context.createLinearGradient(0, 0, canvas.width, 0);
		this.gradient.addColorStop(0, '#ffff00');
		this.gradient.addColorStop(0.25, '#ffcc00');
		this.gradient.addColorStop(0.5, '#ff9900');
		this.gradient.addColorStop(0.75, '#ff6600');
		this.gradient.addColorStop(1, '#ff0000');		
		
		//Initialize Ranking
		context.fillStyle = "#ffffff";
		context.font = "18pt Arial";
		context.textAlign = 'center';
		context.fillText("Player Rankings", canvas.width/2, 18);
		context.font = "10pt Arial";
		context.fillText("Control %", canvas.width/2, 18+15);

		context.fillStyle = this.gradient;

		context.font = "10pt Arial";
		context.fillText("0        25       50       75       100", canvas.width/2, 18+15+15);
		context.strokeStyle = "#ffffff";
		context.beginPath();
		context.moveTo(0, 18+15+15+10);
		context.lineTo(canvas.width, 18+15+15+10);
		context.closePath();
		context.stroke();
	}
	
	//player rankings
	context.textAlign = 'left';
	context.clearRect(0,18+15+15+15,canvas.width,canvas.height);
	
	var blocks = {};
	var totalBlocks = 0;
	var p = {};
	
	for(var i = 0; i < board.rows; i++){
		for(var j = 0; j < board.cols; j++){
			p = pieces[i][j];
			if(p){
				if(!blocks[p])
					blocks[p] = 0;
					
				blocks[p]++;
				totalBlocks++;
			}
		}
	}

	var num = 0;
	for(var r in players){
		p = players[r];

		context.fillStyle = this.gradient;

		context.strokeStyle = "#000000";
		context.lineWidth = 0.5;
		context.font = "bold 16pt Verdana";
		context.fillRect(0, 65 + 35 * num, canvas.width * (blocks[r]/totalBlocks), 30);
		context.strokeStyle = p.strokeStyle;
		context.lineWidth = 2;
		context.strokeText(p.user.username, 35, 87 + 35 * num);
		context.fillStyle = p.fillStyle;
		context.fillText(p.user.username, 35, 87 + 35 * num);

		//get last game move time
		var diff = 5;
		var moves = this.controller.parent.parent.data.GameMove;
		if(moves){
			var last = moves[p.user.id];
			if(last){
				last = last['last_attempted'];

				var runtime = time;
				var lastmove = new XDate(last,true).getTime();

				diff = (runtime - lastmove)/1000;
			}
		}
		
		if(p.user.username == 'Hadz')
			console.log(diff);

		context.fillRect(6, 70 + 35 * num, (diff > 5 ? 5 : diff)/5 * 23, 20);
		
		context.lineWidth = 2;
		context.strokeRect(5, 70 + 35 * num, 25, 20);
		
		num++;
	}
}
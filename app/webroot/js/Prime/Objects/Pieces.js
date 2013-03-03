Pieces = function(controller){
	this.controller = controller;	
	this.init();
}

Pieces.prototype.init = function(){
	//Initialize vars
	var board = this.controller.board;
	var tile = this.controller.tile;
	
	//Create Canvas
	this.canvas = document.createElement('canvas');
	this.canvas.height = board.rows * (tile.height + tile.padding);
	this.canvas.width = board.cols * (tile.width + tile.padding);
	this.canvas.style.position = 'absolute';
	this.canvas.style.left = 0;
	this.canvas.style.top = 0;
	this.context = this.canvas.getContext('2d');
	
	//Place canvas
	document.getElementById('viewport').appendChild(this.canvas);
	
	//Handle user input
	this.events();
}

Pieces.prototype.events = function(){
	//user clicked on "Play Now!"
	$(this.canvas).mousedown((function(event){
		if(event.offsetX == undefined){
			event.offsetX = event.pageX - $(this.canvas).offset().left;
			event.offsetY = event.pageY - $(this.canvas).offset().top;			
		}		
		
		var x = Math.floor(event.offsetX/this.controller.tile.width);
		var y = Math.floor(event.offsetY/this.controller.tile.height);
		
		this.controller.attack(x,y);
	}).bind(this));
}

Pieces.prototype.render = function(time){
	var board = this.controller.board;
	var context = this.context;
	var canvas = this.canvas;
	var tile = this.controller.tile;
	var pieces = this.controller.pieces;
	var players = this.controller.players;
	
	//clear pieces
	context.clearRect(0,0,canvas.width,canvas.height);
	
	//draw pieces
	for(var i = 0; i < board.rows; i++){
		for(var j = 0; j < board.cols; j++){
			if(pieces[i][j] != 0){
				var p = pieces[i][j];
				context.strokeStyle = players[p].strokeStyle;
				context.lineWidth = 1;

				context.fillStyle = players[p].fillStyle;
				context.beginPath();
				context.arc((i+0.5) * tile.width, (j+0.5) * tile.height, 15, 0, Math.PI*2, true);
				context.fill();
				context.stroke();
			}
		}
	}
}
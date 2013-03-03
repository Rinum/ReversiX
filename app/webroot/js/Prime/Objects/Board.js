Board = function(controller){
	this.controller = controller;	
	this.init();
}

Board.prototype.init = function(){
	//Initialize vars
	this.draw = true;
	this.board = this.controller.board;
	this.tile = this.controller.tile;
	
	//Create Canvas
	this.canvas = document.createElement('canvas');
	this.canvas.height = this.board.rows * (this.tile.height + this.tile.padding);
	this.canvas.width = this.board.cols * (this.tile.width + this.tile.padding);
	this.canvas.style.position = 'absolute';
	this.canvas.style.left = 0;
	this.canvas.style.top = 0;
	this.context = this.canvas.getContext('2d');
	
	//Place canvas
	this.controller.viewport.appendChild(this.canvas);
}

Board.prototype.render = function(time){
	if(!this.draw)
		return;

	//Local Vars
	var context = this.context;
	var board = this.board;
	var tile = this.tile;
	
	//Perform Calculations
	board.width = tile.width * board.cols;
	board.height = tile.height * board.rows;
	
	//Clear canvas
	context.clearRect(0,0,this.canvas.width,this.canvas.height);
	
	//Initialize Board
	context.fillStyle="#e9e9e9";
	context.fillRect(0, 0, board.rows * tile.width + 1, board.cols * tile.height + 1);
	context.fillStyle = "#254117";
	for(var i = 0; i < board.rows; i++){
		for(var j = 0; j < board.cols; j++){
			context.fillRect(j * tile.width + 1, i * tile.height + 1, tile.width - tile.padding, tile.height - tile.padding);
		}
	}
	
	//Update controller
	this.controller.board_drawn = true;
}
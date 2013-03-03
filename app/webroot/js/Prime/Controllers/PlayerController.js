PlayerController = function(parent,user){
	this.parent = parent;
	this.user = user;
	this.strokeStyle = '#000';
	this.fillStyle = '#666';
}

PlayerController.prototype.init = function(){
	//Set controller viewport
	this.viewport = this.parent.viewport;
	
	/* Player */
	this.player_object = new Player(this);
	this.player_object_id = this.parent.parent.add_object(this.player_object);
}

PlayerController.prototype.run = function(time){

}
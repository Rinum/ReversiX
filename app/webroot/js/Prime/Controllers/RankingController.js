RankingController = function(parent){
	this.parent = parent;
	this.init();
}

RankingController.prototype.init = function(){
	//Create ranking object to create ranking label/axis
	this.ranking_object = new Ranking(this);
	
	//Add object to Prime
	this.ranking_object_id = this.parent.parent.add_object(this.ranking_object);
	
	//Initial logic
	this.ranking_drawn = false;
}

RankingController.prototype.run = function(time){
	if(this.ranking_drawn)
		this.ranking_object.draw = false;
	
	this.ranking_drawn = true;
}
jQuery( document ).ready(function(jq){
	jq.noConflict(true);

    jq("#inputbutton").click(function() {
		//alert("hello");
		console.log("button clicked");
		//get values of the two text input boxes
		var username = jq("#nameinput").val();
		var userscore = jq("#scoreinput").val();
		console.log("name " + username);
		console.log("score " + userscore);
		//get current table value
		var currtablevals = jq("#Leaderboard").html();
		console.log("table " + currtablevals);	
		var newrow = "<tr><td>" + username + "</td><td>" + userscore + "</td></tr>";
		console.log("adding " + newrow);	
		var combinedrows = currtablevals + newrow;
		console.log("combined table now " + combinedrows);
		//update the table with new values
		jq("#Leaderboard").html(combinedrows);
		
		
	}); //end of click function
	

	
}); //end of call
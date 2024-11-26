jQuery( document ).ready(function(jq){
	jq.noConflict(true);

    jq("#inputbutton").click(function() {
		//alert("hello");
		console.log("button clicked");
		//get values of the two text input boxes
		var username = jq("#nameinput").val();
		var userscore1 = parseInt(jq("#scoreinput1").val());
		var userscore2 = parseInt(jq("#scoreinput2").val());
		var userscore3 = parseInt(jq("#scoreinput3").val());
		var userscore4 = parseInt(jq("#scoreinput4").val());
		var userscore5 = parseInt(jq("#scoreinput5").val());
		var userscore6 = parseInt(jq("#scoreinput6").val());
		var userscoretotal = userscore1 + userscore2 + userscore3 + userscore4 + userscore5 + userscore6;
		var orgname = jq("#orginput").val();
		console.log("name " + username);
		console.log("score " + userscoretotal);
		//get current table value
		var currtablevals = jq("#Leaderboard").html();
		console.log("table " + currtablevals);	
		var newrow = "<tr><td>" + username + "</td><td>" + userscore1 + "</td><td>" + userscore2 + "</td><td>" + userscore3 + "</td><td>" + userscore4 + "</td><td>" + userscore5 + "</td><td>" + userscore6 + "</td><td>" + userscoretotal + "</td><td>" + orgname + "</td></tr>";
		console.log("adding " + newrow);	
		var combinedrows = currtablevals + newrow;
		console.log("combined table now " + combinedrows);
		//update the table with new values
		jq("#Leaderboard").html(combinedrows);
		
		
	}); //end of click function
	
	jq("#inputbutton_old").click(function() {
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

		//declare datatable for sorting and search of table results
		jq('#resultstable').DataTable();
	
}); //end of call
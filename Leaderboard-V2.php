<?php
//now a php file for database connection
include 'functions.php';

$getUserinfo = getScores();
//var_dump($getUserinfo);

function getScores() {
    try {
        // Get database connection
        $connection = getConnection();
        
        // Query to retrieve user and organisation data
        $sql = "SELECT userTable.organisationID, userTable.username, userTable.userID, organisationTable.name FROM userTable ";
        $sql .= "INNER JOIN organisationTable ON userTable.organisationID = organisationTable.organisationID ";
        $stmt = $connection->query($sql);
		
		// Fetch user records into an associative array
        $userdata = [];
		$i=0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$thisuserid = $row['userID'];
				$thisscore = linktoScores($thisuserid);
			
			    $userdata[$i][0] = $row['username'];
				$userdata[$i][1] = $thisscore;
				$userdata[$i][2] = $row['name'];
				$i++;
				//var_dump($row);
        }

        return $userdata;
    } catch (Exception $e) {
        throw new Exception("Error fetching episodes: " . $e->getMessage(), 0, $e);
    }
}

function linktoScores($userid){
	    try {
        // Get database connection
        $connection = getConnection();
        $scoretotal = 0;
        // Query to retrieve user and organisation data
        $sql = "select userID, quiz1Score, quiz2Score,  quiz3Score, quiz4Score, quiz5Score, quiz6Score ";
		$sql .= "from  ";
		$sql .= "leaderboardTable ";
		$sql .= "WHERE userID = " . $userid;
        $stmt = $connection->query($sql);
		
		        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			    $scoretotal = $row['quiz1Score'] + $row['quiz2Score'] + $row['quiz3Score'] + $row['quiz4Score'] + $row['quiz5Score'] + $row['quiz6Score'];
				}
        

        return $scoretotal;
		
		} catch (Exception $e) {
        throw new Exception("Error fetching episodes: " . $e->getMessage(), 0, $e);
		}

}

$tableresults = '<table class="table table-striped" id="resultstable">';
$tableresults .= '<thead><tr class="leaderboard"><th>Name</th><th>Score</th><th>Organisation Name</th></tr></thead>';
$tableresults .= '<tbody id="Leaderboard">';
	foreach ($getUserinfo as list($one,$two,$three)) {
		$tableresults .= '<tr><td>'.$one.'</td><td>'.$two.'</td><td>'.$three.'</td></tr>';
		
	}
$tableresults .= '</tbody></table>';

?>
<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title> Leaderboard </title>
<link media="all" rel="stylesheet" href="dev2.css">
<link href="DataTables/datatables.min.css" rel="stylesheet">
<script src="jquery.js"></script>
<script src="DataTables/datatables.min.js"></script>
<!--<script src="jquery.cookie.js"></script>-->
<script src="dev2.js"></script>
</head>

<body>
<header>
</header>
<main>
<!--<table class="table table-striped">
  <thead>  <tr  class="leaderboard">
        <th>Name</th>
        <th>Score</th>
        
    </tr>
	</thead>

    <tbody id="Leaderboard">
        
    </tbody>
</table>-->

<?php
print $tableresults;

?>

<div id="inputdataname">
<label for="nameinput">Name</label>
<input type="text" id="nameinput">
</div>
<div id="inputdatascore">
<label for="scoreinput">Score</label>
<input type="text" id="scoreinput">
</div>
<div id="inputdatabtn">
<button id="inputbutton" type="button">Add to Score</button>
</div>

<!--<script>
	var myArray = [
	    {'name':'Michael', 'score':'30'},
	    {'name':'Mila', 'score':'32'},
	   
	]
	
	buildTable(myArray)



	function buildTable(data){
		var table = document.getElementById('Leaderboard')

		for (var i = 0; i < data.length; i++){
			var row = `<tr>
							<td>${data[i].name}</td>
							<td>${data[i].score}</td>
							
					  </tr>`
			table.innerHTML += row


		}
	}

</script>-->
</main>
<footer>
<?php

?>
</footer>

</body>

</html>
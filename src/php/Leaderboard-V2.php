<?php
//now a php file for database connection
include 'functions.php';

session_start(); // Starts the session
loggedIn(); // Ensures the user is logged in before loading the page
//var_dump($getUserinfo);
echo makePageStart("CyberPath | Leaderboard", "../../css/stylesheet.css");
//print '<link href="../../css/datatables.min.css" rel="stylesheet">';
//print '<link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.min.css">';
echo makeNavMenu("CyberPath");


function getScores() {
    try {
        // Get database connection
        $connection = getConnection();
        
        // Query to retrieve user and organisation data
        $sql = "SELECT userTable.organisationID, userTable.username, userTable.userID, organisationTable.name FROM userTable ";
        $sql .= "LEFT JOIN organisationTable ON userTable.organisationID = organisationTable.organisationID";
        $stmt = $connection->query($sql);
		
		// Fetch user records into an associative array
        $userdata = [];

		$i=0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$thisuserid = $row['userID'];
				$thisscorearr = [];
				$thisscore = linktoScores($thisuserid);
				$thisscorearr = linktoScoresArray($thisuserid,$i);

				$checkforzero = chkforzerovalue($thisscorearr);

				$userdata[$i][0] = $row['username'];
            	$userdata[$i][8] = $row['name'] ?? 'No Organisation'; 
				
			    $userdata[$i][0] = $row['username'];
				$userdata[$i][8] = $row['name'];				
				if($checkforzero == 1) {
					$userdata[$i][1] = $thisscorearr[$i][0];
					$userdata[$i][2] = $thisscorearr[$i][1];
					$userdata[$i][3] = $thisscorearr[$i][2];
					$userdata[$i][4] = $thisscorearr[$i][3];
					$userdata[$i][5] = $thisscorearr[$i][4];
					$userdata[$i][6] = $thisscorearr[$i][5];
					$userdata[$i][7] = $thisscorearr[$i][6];
				} else {
					$userdata[$i][1] = 0;
					$userdata[$i][2] = 0;
					$userdata[$i][3] = 0;
					$userdata[$i][4] = 0;
					$userdata[$i][5] = 0;
					$userdata[$i][6] = 0;					
					$userdata[$i][7] = 0;
				}

				$i++;
				//var_dump($row);
        }

        return $userdata;
    } catch (Exception $e) {
        throw new Exception("Error fetching scores: " . $e->getMessage(), 0, $e);
    }
}

function chkforzerovalue($thisscorearr){

	if(empty($thisscorearr)){
		return 0;
	} else {
		return 1;
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
		$j=0;
				
		$scoretotalarr = [];
		        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			    $scoretotal = $row['quiz1Score'] + $row['quiz2Score'] + $row['quiz3Score'] + $row['quiz4Score'] + $row['quiz5Score'] + $row['quiz6Score'];
	
				}
        

        return $scoretotal;
		
		} catch (Exception $e) {
        throw new Exception("Error fetching episodes: " . $e->getMessage(), 0, $e);
		}

}

function linktoScoresArray($userid,$j){
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
				
		$scoretotalarr = [];
		        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			    $scoretotal = $row['quiz1Score'] + $row['quiz2Score'] + $row['quiz3Score'] + $row['quiz4Score'] + $row['quiz5Score'] + $row['quiz6Score'];
				$scoretotalarr[$j][0] = $row['quiz1Score'];
				$scoretotalarr[$j][1] = $row['quiz2Score'];
				$scoretotalarr[$j][2] = $row['quiz3Score'];
				$scoretotalarr[$j][3] = $row['quiz4Score'];
				$scoretotalarr[$j][4] = $row['quiz5Score'];
				$scoretotalarr[$j][5] = $row['quiz6Score'];
				$scoretotalarr[$j][6] = $scoretotal;
				//var_dump($scoretotalarr);				
				}
        

        return $scoretotalarr;
		
		} catch (Exception $e) {
        throw new Exception("Error fetching episodes: " . $e->getMessage(), 0, $e);
		}

}
echo "<div class='displayBody'>\n";
echo "<h1 class='title'>Leaderboard</h1>\n";
echo "<p>Check out the leaderboard below to see how you compare to other users!</p>\n";
echo "<div class='box leaderboard-box'>\n";
$getUserinfo = getScores();
$tableresults = '<table class="table table-striped" id="resultstable">' . "\n";
$tableresults .= '<thead><tr class="leaderboard"><th>Name</th><th>Score One</th><th>Score Two</th><th>Score Three</th><th>Score Four</th><th>Score Five</th><th>Score Six</th><th>Total Score</th><th>Organisation Name</th></tr></thead>' . "\n";
$tableresults .= '<tbody id="Leaderboard">' . "\n";
foreach ($getUserinfo as list($one, $two, $three, $four, $five, $six, $seven, $eight, $nine)) {
    $tableresults .= '<tr><td>' . $one . '</td><td>' . $two . '</td><td>' . $three . '</td><td>' . $four . '</td><td>' . $five . '</td><td>' . $six . '</td><td>' . $seven . '</td><td>' . $eight . '</td><td>' . $nine .'</td></tr>' . "\n";
}
$tableresults .= '</tbody></table>' . "\n";

echo $tableresults;




?>


<!--<script src="jquery.cookie.js"></script>-->

<!--<table class="table table-striped">
  <thead>  <tr  class="leaderboard">
        <th>Name</th>
        <th>Score</th>
        
    </tr>
	</thead>

    <tbody id="Leaderboard">
        
    </tbody>
</table>-->



<div id="inputdataname">
<label for="nameinput">Name</label>
<input type="text" id="nameinput">
</div>
<div id="inputdatascore">
<label for="scoreinput1">Score One</label>
<input type="text" id="scoreinput1"><br>
<label for="scoreinput2">Two</label>
<input type="text" id="scoreinput2"><br>
<label for="scoreinput3">Three</label>
<input type="text" id="scoreinput3"><br>
<label for="scoreinput4">Four</label>
<input type="text" id="scoreinput4"><br>
<label for="scoreinput5">Five</label>
<input type="text" id="scoreinput5"><br>
<label for="scoreinput6">Six</label>
<input type="text" id="scoreinput6"><br>
</div>
<div id="inputorgname">
<label for="orginput">Organisation Name</label>
<input type="text" id="orginput">
</div>
<div id="inputdatabtn">
<button id="inputbutton" type="button">Add to Score</button>
</div>
</div>
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
<!--<script src="../js/dev2.js"></script>

<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>-->

<!-- ../js/ -->
<?php
echo makeFooter();
echo makePageEnd();
?>

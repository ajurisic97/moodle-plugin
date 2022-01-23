<?php



require_once('../../config.php');
require($CFG->dirroot . '/report/sestireport/index_form.php');
// Get the system context.
$systemcontext = context_system::instance();
$url           = new moodle_url('/report/sestireport/index.php');
// Check basic permission.
require_capability('report/sestireport:view', $systemcontext);
// Get the language strings from language file.
$strgrade      = get_string('grade', 'report_sestireport');
$strcourse     = get_string('course', 'report_sestireport');
$strpetireport = get_string('sestireport', 'report_sestireport');
$strname       = get_string('name', 'report_sestireport');
$strtitle      = get_string('title', 'report_sestireport');
// Set up page object.
$PAGE->set_url($url);
$PAGE->set_context($systemcontext);
$PAGE->set_title($strtitle);
$PAGE->set_pagelayout('report');
$PAGE->set_heading($strtitle);
// kolegiji

$sqlKolegiji      = "SELECT id, fullname
        FROM mdl_course
        WHERE visible = :visible AND id != :siteid
        ORDER BY fullname";
$courses  = $DB->get_records_sql_menu($sqlKolegiji, array(
    'visible' => 1,
    'siteid' => SITEID
));
$courseid = $_POST[course];

//testovi
/*$sqlTestovi     = 'SELECT q.id, q.name 
         FROM mdl_quiz q';
$tests    = $DB->get_records_sql_menu($sqlTestovi);
$testid   = $_POST[test];

//lekcije
$sqlLekcije = 'SELECT l.id,l.name 
		FROM mdl_lessons l';
$lessons    = $DB->get_records_sql_menu($sqlLekcije);
$lessonid   = $_POST[lesson];
*/
//studenti
$sqlStudenti = "SELECT id, CONCAT(firstname,' ',lastname) AS ime from mdl_user WHERE deleted=0 order by ime";
$names = $DB->get_records_sql_menu($sqlStudenti);
$studentid = $_POST[name];
// Ucitavanje forme:
$mform    = new sestireport_form('', array(
	'names' =>  $names,
    'courses' => $courses
));
echo $OUTPUT->header();
$mform->display();
if ($courseid != 0 and $studentid != 0) {
	$sqlUpit = $DB-> get_records_sql_menu("SELECT concat(u.firstname, ' ',u.lastname)
										as ime, c.fullname
										FROM mdl_user u 
										JOIN mdl_user_enrolments ue on ue.userid=u.id
										JOIN mdl_enrol e on e.id=ue.enrolid
										JOIN mdl_course c on c.id=e.courseid
										where c.id =? and u.id=?", array($courseid,$studentid));
	foreach($sqlUpit as $imeS => $imeK){
		$imeStudenta=$imeS;
		$imeKolegija=$imeK;
	}
	if($imeStudenta==""){
		echo "Student nije upisan na odabrani kolegij!<br>";
	}
	else{
		
	
	echo "<b>$imeKolegija</b> - Student: <b>$imeStudenta</b><br>";
	
	$nizLekcija = array();
	echo "<hr><h3>LEKCIJE:</h3>";
	$sqlLekcija=$DB->get_records_sql_menu("SELECT DISTINCT l.name, c.fullname
						FROM mdl_lesson l
						JOIN mdl_course c ON c.id=l.course
						WHERE c.id=?", array($courseid));
	$brLekcija2=0;
	foreach($sqlLekcija as $nazivLekcije => $vL){
		$brLekcija2+=1;
		$nizLekcija[]=$nazivLekcije;
		echo "$brLekcija2. $nazivLekcije<br>";
		
	}
	$sqlAttempts=$DB->get_records_sql_menu("SELECT distinct l.name from mdl_lesson l
						JOIN mdl_course c ON c.id=l.course
						JOIN mdl_lesson_attempts la ON la.lessonid=l.id
						JOIN mdl_user u ON u.id=la.userid
						WHERE u.id =? AND c.id =?", array($studentid, $courseid));
	$odradeneLekcije=array();
	$brOdradene=0;
	echo "<hr><b>Odrađene:</b><br>";
	foreach($sqlAttempts as $imeL => $imeK){
		$brOdradene+=1;
		$odradeneLekcije[]=$imeL;
		echo "$brOdradene. $imeL<br>";
	}
	$nedovrseneNiz = array_diff($nizLekcija,$odradeneLekcije);
	$brIspis=0;
	echo "<hr><b>Neodrađene:</b><br>";
	foreach($nedovrseneNiz as $imeL => $imeK){
		$brIspis+=1;
		echo "$brIspis. $imeK<br>";
	}
	$nepolozeneBr = $brLekcija2 - $brOdradene;
	$pieData      = array(
                array(
                    'Polozeno',
                    'Postotak'
                ),
                array(
                    'DA',
                    (double) $brOdradene
                ),
                array(
                    'NE',
                    (double) $brIspis
                )
    );
	$jsonTable=json_encode($pieData);
	
	echo "<hr><b>Ukupan broj lekcija: $brLekcija2 . Student/ica nije položio/la: $nepolozeneBr lekcija/e!</b> <br><hr>";
	echo "<h3>TESTOVI:</h3>";
	$sqlTestovi= $DB->get_records_sql_menu('SELECT q.name, qa.sumgrades/q.grade, c.fullname
	FROM mdl_quiz_attempts qa 
	JOIN mdl_user u ON u.id=qa.userid
	JOIN mdl_quiz q ON q.id=qa.quiz
	JOIN mdl_course c ON c.id=q.course
	WHERE u.id =? AND c.id =?',array($studentid,$courseid));
	
	
	$sqlTestovaa=$DB->get_records_sql_menu("SELECT DISTINCT q.name, c.fullname
						FROM mdl_quiz q
						JOIN mdl_course c ON c.id=q.course
						WHERE c.id=?", array($courseid));
	$bukTestovi=0;
	echo "<hr><b>Svi testovi:</b><br>";
	$nizSviTestovi=array();
	foreach($sqlTestovaa as $nazivTesta => $vT){
		$bukTestovi+=1;
		$nizSviTestovi[]=$nazivTesta;
		echo "$bukTestovi. $nazivTesta<br>";
		
	}
	echo "<hr><b>Odrađeni testovi:</b><br>";

	$brojacTest=0;
	$nizRijeseniTest=array();
	$brIspis2=0;

	foreach($sqlTestovi as $imeT => $bodovi){
		$brojacTest+=1;
		$nizRijeseniTest[]=$imeT;
		$bodovi=$bodovi*100;
		if($bodovi<50){
			$brIspis2+=1;
		}
		echo "$brojacTest. $imeT - $bodovi %";
	}
	$nedovrseniTestovi=array_diff($nizSviTestovi,$nizRijeseniTest);
	echo "<hr><b>Neodrađeni testovi:</b><br>";
	foreach($nedovrseniTestovi as $imeT => $imeK){
		$brIspis2+=1;
		echo "$brIspis2. $imeK<br>";
	}

	echo "<hr><b>Ukupan broj testova: $bukTestovi . Student/ica nije položio/la: $brIspis2 test/a!</b> <br><hr>";
	$ner = $bukTestovi - $brIspis2;
	$pieData2      = array(
                array(
                    'Rijeseno',
                    'Postotak'
                ),
                array(
                    'Položeno',
                    (double) $ner
                ),
                array(
                    'Nepoloženo',
                    (double) $brIspis2
                )
    );
	$jsonTable2=json_encode($pieData2);
	}
} else {
    echo 'Trebate odabrati kolegij i studenta!';
}
?>

<!DOCTYPE html>
<html>
  <head>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
      google.charts.load("current", {packages:["corechart"]});
      google.charts.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable(<?php
echo $jsonTable;
?>);
	var data2 = google.visualization.arrayToDataTable(<?php
		echo $jsonTable2;
		?>);
        var options = {
          title: 'Postotak odrađenih lekcija lekcija',
          is3D: true
        };
        var chart = new google.visualization.PieChart(document.getElementById('piechart'));
        chart.draw(data, options);
		
		var options2 = {
          title: 'Postotak riješenosti testova',
          is3D: true
        };
        var chart = new google.visualization.PieChart(document.getElementById('piechart2'));
        chart.draw(data2, options2);
        
        
      }
    </script>
  </head>
  <body>
    <table>
    <tr>
    <td><div id="piechart" style="width: 600px; height: 500px;"></div></td>
    <td><div id="piechart2" style="width: 600px; height: 500px;"></div></td>
    </tr>
    </table>
  </body>
</html>

<?php
echo $OUTPUT->footer();
?> 


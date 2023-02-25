<?php
/******************************************************************************************
This file is a part of SIMPLE WEB STATICSTICS GENERATOR FOR GNUAIS, a very simple and small statictics generator written in PHP
This script may have a lot of bugs, problems and it's written in very non-efficient way without a lot of good programming rules. But it works for me.
Author: Alfredo IZ7BOJ IZ7BOJ [at] gmail.com
You can modify this program, but please give a credit to original author. Program is free for non-commercial use only.
(C) Alfredo IZ7BOJ 2023
*******************************************************************************************/

include 'config.php';

//Calculation of distance and bearing between two points
function bearing_dist($lat1, $long1, $lat2, $long2) {
    $lat1 = M_PI * $lat1/180;
    $long1= M_PI * $long1/180;
    $lat2 = M_PI * $lat2/180;
    $long2= M_PI * $long2/180;
    $co   = cos($long1 - $long2) * cos($lat1) * cos($lat2) + sin($lat1) * sin($lat2);
    $ca   = atan2(sqrt(1 - $co*$co), $co);
    $az   = atan2(sin($long2 - $long1) * cos($lat1) * cos($lat2), sin($lat2) - sin($lat1) * cos($ca));

    if ($az < 0) {
        $az += 2 * M_PI;
    }

    $ret['km'] = round(6371*$ca);
    $ret['deg'] = round($az/M_PI*180);

    return $ret;
}

$mysqli = mysqli_connect($host, $user, $passwd, $schema);
/* Check if the connection succeeded */
if (!$mysqli)
{
   echo 'Connection to GNUAIS Database failed!<br>';
   echo 'Error number: ' . mysqli_connect_errno() . '<br>';
   echo 'Error message: ' . mysqli_connect_error() . '<br>';
   die();
}else{

	$sql = "(select mmsi, latitude, longitude, name, destination, speed, ais_position.time from ais_position left join ais_vesseldata using (mmsi)) ".
		   " union" .
		   "(select mmsi, latitude, longitude, 'shore station', 'n/a', 'n/a', null from ais_basestation)".
		   "ORDER BY time desc";
	$result = $mysqli->query($sql);

    $markers=array(); //create array for markers on the map
	$i=0;
	while($row = $result->fetch_assoc()) {
		$markers[$i]["latitude"]=$row["latitude"];
		$markers[$i]["longitude"]=$row["longitude"];
		$i++;
	}
    ?>
<!DOCTYPE html>
<html>
	<head>
	  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	  <meta name="Description" content="GNU-AIS statistics" />
	  <meta name="Keywords" content="" />
	  <meta name="Author" content="IZ7BOJ" />

      <link rel="stylesheet" href="node_modules/ol/ol.css">

	  <!-- next style is to show arrows in sortable table's column headers to indicate that the table is sortable -->
	  <!-- moreover, cell collapse is added -->

	  <style>
	  	table.sortable th:not(.sorttable_sorted):not(.sorttable_sorted_reverse):not(.sorttable_nosort):after {
          		content: " \25B4\25BE"
          	}
	  	table, th, td {
			border: 1px solid black;
			border-collapse: collapse;
	  	}
		.map {
			width: 1000px;
			height: 600px;
		}
		.marker {
			width: 20px;
			height: 20px;
		content: url('./icons/pin.png');
		}
	  	.rxmarker {
			content: url('./icons/rx.png');
			width: 20px;
			height: 20px;
          	}
		.label {
			text-decoration: none;
			color: white;
			font-size: 10pt;
			font-weight: bold;
			text-shadow: black 0.1em 0.1em 0.2em;
		}
    	  	</style>

    <title>AIS statistics - summary</title>

   </head>
   <body>

      <!-- add Logo, if present -->
      <?php if(file_exists($logourl)){ ?>
      <center><img src="<?php echo $logourl ?>" width="100px" height="100px" align="middle"></center>
      <br>
      <?php } ?>
      <center>
         <font size="20"><b>GNU-AIS statistics</b></font>
         <br><br>
         <hr>
      </center>
      <br>
      <?php
      // System parameters reading
      $sysver    = NULL;
      $kernelver = NULL;
      $cputemp   = NULL;
      $cpufreq   = NULL;
      $uptime    = NULL;

      $sysver = shell_exec ("cat /etc/os-release | grep PRETTY_NAME |cut -d '=' -f 2");
      $kernelver = shell_exec ("uname -r");

      if (file_exists ("/sys/class/thermal/thermal_zone0/temp")) {
          exec("cat /sys/class/thermal/thermal_zone0/temp", $cputemp);
          $cputemp = $cputemp[0] / 1000;
      }

      if (file_exists ("/sys/devices/system/cpu/cpu0/cpufreq/scaling_cur_freq")) {
          exec("cat /sys/devices/system/cpu/cpu0/cpufreq/scaling_cur_freq", $cpufreq);
          $cpufreq = $cpufreq[0] / 1000;
          }

      $uptime = shell_exec('uptime -p');

      //include custom info file, if present
      if(file_exists('custom.php')) include 'custom.php';
      ?>
      <br><br>
      <hr>
      <br>
      <center><table style="text-align: left; height: 116px; width: 600px;" border="1" cellpadding="2" cellspacing="2"></center>
         <tbody>
            <tr align="center">
               <td bgcolor="#ffd700" style="width: 600px;" colspan="2" rowspan="1">
               <span style="color: red; font-weight: bold;">SYSTEM STATUS</span></td>
            </tr>
            <tr>
               <td bgcolor="silver" style="width: 200px;"><b>System Version:</b></td>
               <td style="width: 400px;"><?php echo $sysver ?></td>
            </tr>
            <tr>
               <td bgcolor="silver" style="width: 200px;"><b>Kernel Version:</b></td>
               <td style="width: 400px;"><?php echo $kernelver ?></td>
            </tr>
            <tr>
               <td bgcolor="silver" style="width: 200px;"><b>System uptime:</b></td>
               <td style="width: 400px;"><?php echo $uptime ?></td>
            </tr>
            <tr>
               <td bgcolor="silver" style="width: 200px;"><b>CPU temperature:</b></td>
               <td style="width: 400px;"><?php echo $cputemp ?> °C </td>
            </tr>
            <tr>
               <td bgcolor="silver" style="width: 200px;"><b>CPU frequency:</b></td>
               <td style="width: 400px;"><?php echo $cpufreq ?> MHz </td>
            </tr>
         </tbody>
      </table>
      <br>
      <br>
      <br>
      <hr>
      <!-- include script for sorting data in columns -->
      <script src="sorttable.js"></script>
      <b>AIS DATA TABLE</b>
      <br><br>
		<center><table style="text-align: left; " border="1" class="sortable" id="table" ></center>
         <tbody>
            <tr>
               <th align="center" bgcolor="#ffd700"><b><font color="blue">Marker</font></b></th>
               <th align="center" bgcolor="#ffd700"><b><font color="blue">MMSI</font></b></th>
               <td align="center" bgcolor="#ffd700"><b><font color="blue">Ship details</font></b></td>
               <th align="center" bgcolor="#ffd700"><b><font color="blue">Lat</font></b></th>
               <th align="center" bgcolor="#ffd700"><b><font color="blue">Long</font></b></th>
               <th align="center" bgcolor="#ffd700"><b><font color="blue">Description</font></b></th>
               <th align="center" bgcolor="#ffd700"><b><font color="blue">Destination</font></b></th>
               <th align="center" bgcolor="#ffd700"><b><font color="blue">Speed[knots]</font></b></th>
               <th align="center" bgcolor="#ffd700"><b><font color="blue">Time</font></b></th>
               <th align="center" bgcolor="#ffd700"><b><font color="blue">Distance[Km]</font></b></th>
            </tr>
            <?php
		$i=0;
		$result = $mysqli->query($sql);
		while($row = $result->fetch_assoc()) {
		$mmsi_pad=str_repeat('0',9-strlen($row["mmsi"])).$row["mmsi"]; //add zeros for query to aprs.fi
	    ?>
            <tr>
               <td align="center"><b><?php echo $i ?></b></td>
               <td align="center"><b><?php echo '<a target="_blank" href="https://aprs.fi/?call='.$mmsi_pad.'">'.$mmsi_pad.'</a>' ?></b></td>
               <td align="center"><?php echo '<a target="_blank" href="https://www.marinetraffic.com/en/ais/details/ships/mmsi:'.$mmsi_pad.'">Ship details</a>' ?></td>
               <td align="center"><?php echo $row["latitude"] ?></td>
               <td align="center"><?php echo $row["longitude"] ?></td>
               <td align="center"><?php echo ($row["name"]!="") ? $row["name"] : 'n/a' ?></td>
               <td align="center"><?php echo ($row["destination"]!="") ? $row["destination"] : "n/a" ?></td>
               <td align="center"><?php echo round($row["speed"],2) ?></td>
               <td align="center"><?php echo ($row["time"]!=null) ? date('H:i:s',$row["time"]) : "n/a" ?></td>
               <td align="center"><?php echo bearing_dist($stationlat,$stationlon,$row["latitude"],$row["longitude"])['km'] ?></td>
            </tr>
		<?php
		$i++;
		   } //closes while fetch_assoc
		} //closes "else" of database connection success
		?>
		 </tbody>
		</table>
		<br>
		<center><div id="map" class="map"></div></center>
		<?php
		$i=0;
		for ($i = 0; $i <count($markers) ; $i++){
		?>
		<div style="display: none;">
			<div id="label<?php echo $i?>" class="label" title="label"><?php echo $i?></div>
			<div id="marker<?php echo $i?>"class="marker" title="marker"></div>
			<div id="rxlabel" class="label" title="rxlabel">RX</div>
            <div id="rxmarker" class="rxmarker" title="rxmarker"></div>
		</div>
		<?php
		}
		?>
		<script src="https://cdn.jsdelivr.net/npm/ol@v7.2.2/dist/ol.js"></script>
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ol@v7.2.2/ol.css">

		<script src="https://cdn.jsdelivr.net/npm/elm-pep@1.0.6/dist/elm-pep.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
		<script type="module">

		import Map from './node_modules/ol/Map.js';
		import OSM from './node_modules/ol/source/OSM.js';
		import Overlay from './node_modules/ol/Overlay.js';
		import TileLayer from './node_modules/ol/layer/Tile.js';
		import View from './node_modules/ol/View.js';
		import {fromLonLat, toLonLat} from './node_modules/ol/proj.js';
		import {toStringHDMS} from './node_modules/ol/coordinate.js';

		const layer = new TileLayer({
		  source: new ol.source.OSM(),
		});

		const map = new ol.Map({
		  layers: [layer],
		  target: 'map',
		  view: new ol.View({
			center: fromLonLat([<?php echo $stationlon?>, <?php echo $stationlat?>]),
			zoom: 8,
		  }),
		});

        var markers = <?php echo json_encode($markers)?>;

		//RX station marker and label
        map.addOverlay(
            new ol.Overlay({
                position: fromLonLat([<?php echo $stationlon?>, <?php echo $stationlat?>]),
                positioning: 'center-center',
                element: document.getElementById('rxmarker'),
                stopEvent: false,
                }));
        map.addOverlay(
            new ol.Overlay({
                position: fromLonLat([<?php echo $stationlon?>, <?php echo $stationlat?>]),
                element: document.getElementById('rxlabel'),
                }));

		// marker and labels for ships
		for (var i=0; i<markers.length; i++) {
			map.addOverlay(
				new ol.Overlay({
					position: fromLonLat([markers[i]["longitude"],markers[i]["latitude"]]),
					positioning: 'center-center',
					element: document.getElementById('marker'+i),
					stopEvent: false,
				}));
			map.addOverlay(
				new ol.Overlay({
					position: fromLonLat([markers[i]["longitude"],markers[i]["latitude"]]),
					element: document.getElementById('label'+i),
				}));
		};
		</script>
      <hr>
      <br>
      <center><a href="https://github.com/IZ7BOJ/GNUAIS-web-interface" target="_blank">GNU-AIS Web Interface by IZ7BOJ</a></center>
      <br>
   </body>
</html>

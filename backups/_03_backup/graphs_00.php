<div id="games" style="width: 100%; height: 400px"></div>					 <div id="games2" style="width: 100%; height: 400px"></div>					<div id="3dayavg" style="width: 100%; height: 400px"></div>		<script type="text/javascript">			var chart1;		$(document).ready(function() {			  chart1 = new Highcharts.Chart({				 chart: {					renderTo: 'games',					type: 'column'				 },				 legend: {					enabled: false				 },				 title: {					text: 'Games per day - November 2012'				 },				 xAxis: {					categories: ['11/01', '11/02', '11/03', '11/04', '11/05', '11/06', '11/07', '11/08', '11/09', '11/10', '11/11', '11/12', '11/13', '11/14', '11/15', '11/16', '11/17', '11/18', '11/19', '11/20', '11/21', '11/22', '11/23', '11/24', '11/25', '11/26', '11/27', '11/28', '11/29', '11/30']				 },				 yAxis: {					title: {					   text: 'games played'					}				 },				 series: [{					name: 'Games',					data: [19, 36, 29, 46, 57, 56, 53, 49, 71, 64, 48, 51, 45, 32, 38, 52, 30, 47, 54, 42, 40, 47, 39, 40, 14, 39, 43, 47, 36, 36]				 }]			  });			  			  chart2 = new Highcharts.Chart({				 chart: {					renderTo: 'games2',					type: 'column'				 },				 legend: {					enabled: false				 },				 title: {					text: 'Games per day - December 2012'				 },				 xAxis: {					categories: ['12/01', '12/02', '12/03', '12/04', '12/05', '12/06', '12/07', '12/08', '12/09', '12/10', '12/11', '12/12', '12/13', '12/14', '12/15', '12/16', '12/17', '12/18', '12/19', '12/20', '12/21', '12/22', '12/23', '12/24', '12/25', '12/26', '12/27', '12/28', '12/29', '12/30', '12/31']				 },				 yAxis: {					title: {					   text: 'games played'					}				 },				 series: [{					name: 'Games',					data: [33, 39, 43, 32, 33, 39, 48, 22, 34, 37, 29, 34, 45, 47, 28, 27, 26, 25, 27, 33, 36, 17, 24, 47, 34, 25, 54, 68, 31, 54, 33]				 }]			  });			  			   chart3 = new Highcharts.Chart({				 chart: {					renderTo: '3dayavg',					type: 'line'				 },				 legend: { 					enabled: false				 },				 title: {					text: '3 Day average'				 },				 xAxis: {					categories: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61],					labels: {						enabled: false					}				 },				 yAxis: {					title: {					   enabled: false,					   text: ''					}				 },				 				 series: [{					name: 'Averages',					data: [24.67, 28, 37, 44, 53, 55.33, 52.67, 57.67, 61.33, 61, 54.33, 48, 42.67, 38.33, 40.67, 40, 43, 43.67, 47.67, 45.33, 43, 42, 42, 31, 31, 32, 43, 42, 39.67, 35, 36, 38.33, 38, 36, 34.67, 40, 36.33, 34.67, 31, 33.33, 33.33, 36, 42, 40, 34, 27, 26, 26, 28.33, 32, 28.67, 25.67, 29.33, 35, 35.33, 37.67, 49, 51, 51, 32.33]				 }]			   });			   		   });		</script>
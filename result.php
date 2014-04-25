<?
session_start();
include('config.php');
if (!isLogin()){
	include('notlogin.php');
	die();
}
if(!inTime()){
	include('timeout.php');
	die();
}
?>

<script type="text/javascript">
	function compiler_message(id){
		$('#compiler_message-'+id).slideToggle('fast');
	}
	function code_watch(task,user){
		$('#code_watch').load("/addon/code_watcher.php?task="+task+"&user="+user);
		$(window).scrollTop($('#code_watch').offset().top);
		// $('#code_watch').load("/addon/code_watcher.php?file="+task+"-"+user+".cpp");
		// msg = $('#code').text();
		// $('#code').html(msg);
	}
</script>
<div style="height: 20px;"></div>
<div id="result" class="container_12">	
	<style>
	.table {
		display: table;
		width: 100%;
	}
	.row {
		display: table-row;
	}
	.row:hover:not(:nth-child(1)){
		background: rgb(253,255,203) !important;
	}
	.row:nth-child(2n){
		background: rgb(240,240,240);
	}
	.cell {
		display: table-cell;
		vertical-align: middle;
		padding: 5px;
	}
	.compiler_message{
		display: none;
		text-align: left;
	}
	</style>
	<div id="code_watch" style="text-align:left"></div>
	<div class="grid_12">
		<div class="table">
			<div class="row" style="text-align: center; font-weight: bold;">
				<div class="cell" style="width: 50px;">
					#
				</div>
				<div class="cell" style="width: 150px;">
					เวลา
				</div>
				<div class="cell" style="width: 100px;">
					ผู้ส่ง
				</div>
				<div class="cell" style="width: 150px;">
					ข้อ
				</div>
				<div class="cell">
					ผลตรวจ
				</div>
				<div class="cell" style="width: 100px;">
					ได้คะแนน
				</div>
				<div class="cell" style="width: 100px;">
					เวลารวม
				</div>
			</div>
			<?

			$addition = ' where `user_id` = ? ';
			if(isAdmin()) $addition = ''; 

			$query = 'select `user_id`, `task_id`, `time` from `queue` '.$addition.' order by `queue_id` desc;';
			$sql->prepare($query);
			if(!isAdmin()) $sql->bind_param('d', $_SESSION[$config['name_short']]['user_id']);
			$sql->execute();
			@$sql->bind_result($user_id, $task_id, $time);

			while($sql->fetch()){
				$user = user($user_id);
				$task = task($task_id);
				echo '
				<div class="row" style="text-align: center;" onclick="code_watch(\''.$task["task_id"].'\',\''.$user["user"].'\')">
					<div class="cell">
						-
					</div>
					<div class="cell">
						' . D('d m y H:M:S', $time). '
					</div>
					<div class="cell">
						' . $user['display'] . '
					</div>
					<div class="cell">
						' . $task['name'] . '
					</div>
					<div class="cell">
						รอตรวจ..
					</div>
					<div class="cell">
						-
					</div>
					<div class="cell">
						-
					</div>
				</div>
				';
			}
			
			$query = 'select `user_id`, `task_id`, `time` from `grading` '.$addition.' order by `grading_id` desc;'; 
			$sql->prepare($query);
			if(!isAdmin()) $sql->bind_param('d', $_SESSION[$config['name_short']]['user_id']);
			$sql->execute();
			$sql->bind_result($user_id, $task_id, $time);

			while($sql->fetch()){
				$user = user($user_id);
				$task = task($task_id);
				echo '
				<div class="row" style="text-align: center;" onclick="code_watch(\''.$task["task_id"].'\',\''.$user["user"].'\')">
					<div class="cell">
						-
					</div>
					<div class="cell">
						' . D('d m y H:M:S', $time) . '
					</div>
					<div class="cell">
						' . $user['display'] . '
					</div>
					<div class="cell">
						' . $task['name'] . '
					</div>
					<div class="cell">
						กำลังตรวจ..
					</div>
					<div class="cell">
						-
					</div>
					<div class="cell">
						-
					</div>
				</div>
				';
			}
	
			$query = 'select `result_id`, `user_id`, `task_id`, `time`, `text`, `score`, `timeused`, `message` from `result` '.$addition.' order by `result_id` desc limit 100;';
			$sql->prepare($query);
			if(!isAdmin()) $sql->bind_param('d', $_SESSION[$config['name_short']]['user_id']);
			$sql->execute();
			$sql->bind_result($result_id, $user_id, $task_id, $time, $text, $score, $timeused, $message);

			while($sql->fetch()){
				$user = user($user_id);
				$task = task($task_id);
				echo '
				<div class="row" style="text-align: center;" onclick="code_watch(\''.$task["task_id"].'\',\''.$user["user"].'\')">
					<div class="cell">
						' . $result_id . '
					</div>
					<div class="cell">
						' . D('d m y H:M:S', $time) . '
					</div>
					<div class="cell">
						' . $user['display'] . ' '. ($my['level'] == 0 ? '('.$user['user'].')' : ''). '
					</div>
					<div class="cell">
						<a href="doc/'. $task['name_short'] .'.pdf" target="_blank">' . $task['name'] . '</a>
					</div>
					<div class="cell">
						';
				if($text == 'cmperr'){
					echo '<a href="javascript:compiler_message('.$result_id.');">คอมไฟล์เออเร่อ</a>';
				}
				else if($text == 'err'){
					echo '<a href="javascript:compiler_message('.$result_id.');">มีปัญหาในการตรวจ</a>';
				}
				else {
					if(isBlind()) $text = $singlecase[substr($text, 0, 1)];  
					echo $text;
				}
				$compiler_message = str_replace('<', '&lt;', $compiler_message);
				$compiler_message = str_replace('>', '&gt;', $compiler_message);

				echo '
						<div class="compiler_message" id="compiler_message-'. $result_id. '">'.$message.'</div>
					</div>
					<div class="cell">';
				if(isBlind()) echo '-';
				else printf("%.2lf", $score); 
				echo '
					</div>
					<div class="cell">
				';
				if(isBlind()) echo '-';
				else printf("%.2lf",$timeused);		
				echo '
					</div>
				</div>
				';
			}
			?>
		</div>
	</div>
</div>
<div style="height: 20px;"></div>
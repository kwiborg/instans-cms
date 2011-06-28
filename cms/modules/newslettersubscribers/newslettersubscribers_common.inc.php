<?php
	function niceTableListing2($sql, $db_columns, $display_columns, $functions_per_column, $additional_columns = array(), $no_results_html = ""){
		$sql_result = mysql_query($sql);
		if ($numrows = mysql_num_rows($sql_result)){
			$row = mysql_fetch_array($sql_result);
			$html .= "<table border='1' class='oversigt'>";
			$html .= "<tr class='trtop'>";
			foreach ($display_columns as $keyname=>$title){
				$getvarurl = "";
				foreach ($_GET as $getvarkey => $getvarval){
					if ($getvarkey != "orderby" && $getvarkey != "dir" && $getvarkey != "content_identifier"){
						$getvarurl .= "&amp;".$getvarkey."=".$_GET[$getvarkey];
					}
				}
				$html .= "
					<td class='kolonnetitel'>
						<a href='index.php?content_identifier=".$_GET["content_identifier"]."&amp;orderby=".$keyname."&amp;dir=".($_GET["dir"]=="asc"?"desc":"asc")."$getvarurl'>".$title."</a>
						".($keyname == $_GET["orderby"] ? ($_GET["dir"] == "desc" ? "<img src='images/pilned.gif' />" : "<img src='images/pilop.gif' />") : "")."
					</td>
				";
			}
			foreach ($additional_columns as $title=>$content){
				$html .= "<td class='kolonnetitel'>".$title."</td>";
			}
			mysql_data_seek($sql_result, 0);
			while($row = mysql_fetch_array($sql_result)){
				$pagerow_id++;
				$html .= "<tr class='oversigt2' onmouseover='IEColorShift(this.id)' onmouseout='IEColorUnShift(this.id, 2)' id='pagerow_$pagerow_id'>";
				foreach ($row as $key=>$value){
					if (is_string($key) && in_array($key, array_keys($display_columns))){
						if ($function = $functions_per_column[$key]){
							foreach ($db_columns as $colkey => $colname){
								if (strstr($function, $needle = "__".$colkey."__")){
									$function = str_replace($needle, $row[$colkey], $function);
								}
								$output_value = eval($function);
							}
						} else {
							$output_value = $value;
						}
						$html .= "<td>".$output_value."</td>";
					}
				}
				foreach ($additional_columns as $add_html){
					foreach ($db_columns as $colkey=>$colname){
						if (strstr($add_html, $needle = "__".$colkey."__")){
							$add_html = str_replace($needle, $row[$colkey], $add_html);
						}
					}
					$html .= "<td>".$add_html."</td>";
				}
				$html .= "</tr>";
			}	
			$html .= "</table>";
		} else {
			$html .= $no_results_html;
		}
		return array($numrows, $html);
	}		
	
	function email_verified($email, $v){
		if ($v == 1){
			return $email;
		} 
		if ($v == 0){
			return "<span style='color:#888'>$email [?]</span>";
		} 			
	}
?>
<?php
	// REMEMBER TO EXIT HERE WHEN SCRIPT IS NOT IN USE 
	exit;
	/*
		Fix interest group optouts
		MAP, 9/1/2001
		Script to remove interest group optouts if subscriber is opted out of all interestgroups.
		See cms_history.txt entry from 2008-01-09 for more info 
	*/

	include_once($_SERVER[DOCUMENT_ROOT]."/cms_config.inc.php");
  	include_once($cmsAbsoluteServerPath."/common.inc.php");
  	connect_to_db();
	$ok = 0;
	$error = 0;

// 1. Find all templates with interest-groups
$sql = "
			SELECT
				NT.ID as TEMPLATE_ID,
				COUNT(NC.ID) as CATEGORIES_ON_TEMPLATE
			FROM
				NEWSLETTER_TEMPLATES NT,
				NEWSLETTER_TEMPLATES_CATEGORYGROUPS NTC,
				NEWSLETTER_CATEGORIES NC
			WHERE
				NT.ID = NTC.TEMPLATE_ID and
				NTC.CATEGORYGROUP_ID = NC.GROUP_ID and
				NC.DELETED = 0 and
				NT.DELETED = 0
			GROUP BY NT.ID
";

$res = mysql_query($sql);

if (mysql_numrows($res) > 0) {
	while ($row = mysql_fetch_assoc($res)) {
		$arr_templates_with_interestgroups[] = array("TEMPLATE_ID" => $row[TEMPLATE_ID], "CATEGORIES_ON_TEMPLATE" => $row[CATEGORIES_ON_TEMPLATE]);
	}
	echo "<h1>Templates med interessegrupper</h1>";
	echo "<pre>";
	print_r($arr_templates_with_interestgroups);
	echo "</pre>";

} else {
	echo "Ingen nyhedsbreve med interessekategorier, afslutter!";
	exit;
}

// 2. Find all subscribers of these templates
// 3. Count the interest-group optouts 
	foreach($arr_templates_with_interestgroups as $k => $v) {
		echo "<h1>Finding subscribers of template '".$v[TEMPLATE_ID]."' with all categories as optout</h1>";
		$sql = "
					SELECT 
						U.EMAIL,
						NS.TEMPLATE_ID, 
						NS.USER_ID,
						COUNT(NCO.ID) AS USER_OPTOUT_COUNT
					FROM
						USERS U,
						NEWSLETTER_SUBSCRIPTIONS NS,
						NEWSLETTER_CATEGORIES_OPTOUT NCO
					WHERE
						U.ID = NS.USER_ID and
						NS.TEMPLATE_ID = $v[TEMPLATE_ID] and
						NS.TEMPLATE_ID = NCO.TEMPLATE_ID and
						NS.USER_ID = NCO.USER_ID 
					GROUP BY NS.USER_ID, NS.TEMPLATE_ID
		";

		$ures = mysql_query($sql);

		if (mysql_numrows($ures) > 0) {
			while ($urow = mysql_fetch_assoc($ures)) {
				// 4. Remove subscriber optouts if they have opted out of all interest-groups
				if ($urow[USER_OPTOUT_COUNT] >= $v[CATEGORIES_ON_TEMPLATE]) {
					echo "Remove all optouts for user/template $urow[USER_ID]/$urow[TEMPLATE_ID] ($urow[EMAIL])...";
					$removesql = "DELETE FROM NEWSLETTER_CATEGORIES_OPTOUT WHERE USER_ID = $urow[USER_ID] and TEMPLATE_ID = $urow[TEMPLATE_ID]";
					if (mysql_query($removesql)) {
						echo "ok!<br/>";
						$ok++;
					} else {
						echo "<strong>error!</strong><br/>";
						$error++;
					}
				}
			}
		}

	}

echo "<strong>Operation complete optouts removed for $ok users. $error errors encountered!</strong>";
?>
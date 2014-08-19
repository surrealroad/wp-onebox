<?php
// XIVDB Parser for OneBox

if(isset($onebox)) {

	$match = preg_match('/^https?:\/\/(?:www\.)?xivdb\.com/', $onebox->data['url']);

	if($match) {
		$onebox->addClass("onebox-xivdb");
		$data = get_xivdb_data($onebox);
		$onebox->update($data);
	}
}


function get_xivdb_data($onebox) {

	$data=array();

	$data['sitename'] = "XIVDB";
	$data['favicon']='http://xivdb.com/favicon.ico';

	$url_parts = parse_url($onebox->data['url']);
	$query = rtrim(isset($url_parts['query'])?$url_parts['query']:'');
	$params = explode("/", $query);

	$version = "1.6";
	$lang= "en";
	$type = $params[0];
	$ID = $params[1];

	if($version && $lang && $type && $ID) {
		// get api result
		$info = json_decode($onebox->getSource("http://xivdb.com/modules/fpop/fpop.php?id=".$ID."&lang=".$lang."&type=".$type."&version=".$version), true);

		if(isset($info['name'])) $data['title']= $info['name'];
		if(isset($info['icon'])) $data['image']= $info['icon'];
		if(isset($info['html'])) {
			phpQuery::newDocumentHTML($info['html']);

			$desc = array();

			$onebox->data['readmore'] = false;

			$subtitle = phpQuery::trim(pq(".xivdb-tooltip-content-header-data-subtitle")->text());
			if($subtitle) $desc[] = "<em>".$subtitle."</em>";
			$info = pq(".xivdb-tooltip-content-info")->text();
			if($info) {
				$desc[] = $info;
				$onebox->data['readmore'] = true;
			}

			$additional = array();

			$statTable = array();
			$corestats = pq(".xivdb-tooltip-content-corestats-block");
			if($corestats) {
				foreach ($corestats as $corestat) {
					$statTable[] = array(
						'<strong>'.phpQuery::trim(pq(".xivdb-tooltip-content-corestats-block-title", $corestat)->text()).'</strong>',
						'<strong>'.phpQuery::trim(pq(".xivdb-tooltip-content-corestats-block-number", $corestat)->text()).'</strong>',
						'',
						'',
					);
				}
				pq(".xivdb-tooltip-content-corestats-block")->remove();
			}
			$substats = pq(".xivdb-tooltip-content-substats-block");
			if($substats) {
				foreach ($substats as $substat) {
					$statTable[] = array(
						phpQuery::trim(pq(".xivdb-tooltip-content-substats-block-title", $substat)->text()),
						phpQuery::trim(pq(".xivdb-tooltip-content-substats-block-number", $substat)->text()),
						phpQuery::trim(pq(".xivdb-tooltip-content-substats-block-number2", $substat)->text()),
						phpQuery::trim(pq(".xivdb-tooltip-content-substats-block-number3", $substat)->text()),
					);
				}
				pq(".xivdb-tooltip-content-substats-block")->remove();
			}
			if(count($statTable)) {
				$table = '<table>';
				foreach ($statTable as $row) {
					$table .= '<tr>';
					foreach($row as $col) {
						$table .= '<td>'.$col.'</td>';
					}
					$table .= '</tr>';
				}
				$table .= '</table>';
				$desc[] = $table;
			}

			$recipeItemTitles = pq(".xivdb-tooltip-content-recipe-item-title");
			if($recipeItemTitles) {
				foreach($recipeItemTitles as $recipeItemTitle) {
					$desc[] = "<strong>".phpQuery::trim(pq($recipeItemTitle)->text())."</strong>";
					$recipeItem = pq($recipeItemTitle)->parent();
					pq(".xivdb-tooltip-content-recipe-item-title", $recipeItem)->remove();
					$recipeItemDesc = pq($recipeItem)->children(":not(:empty)");
					if($recipeItemDesc) {
						foreach($recipeItemDesc as $recipeItemDescLine) {
							$desc[] = phpQuery::trim(pq($recipeItemDescLine)->text());
						}
					}
				}
			}

			$stats = pq(".xivdb-tooltip-content-statsbox, .xivdb-tooltip-content-statsbox2");
			if($stats) {
				$labels = pq(".xivdb-tooltip-content-label", $stats);
				if($labels) {
					foreach($labels as $label) {
						$additional[] = pq($label)->text();
					}
					pq(".xivdb-tooltip-content-label", $stats)->parent()->remove();
				}
				$titles = pq(".xivdb-tooltip-content-title:first", $stats)->parent()->parent()->children();
				if($titles) {
					foreach($titles as $title) {
						$additional[] = pq($title)->text();
					}
					pq(".xivdb-tooltip-content-title:first", $stats)->parent()->parent()->children()->remove();
				}
				$icons = pq("img", $stats);
				if($icons) {
					foreach($icons as $icon) {
						$additional[] = pq('<div>')->append(pq($icon)->clone())->html() . pq($icon)->parent()->text();
					}
					pq("img", $stats)->parent()->remove();
				}
				$remaining = pq("div:not(:has(>div))", $stats);
				if($remaining) {
					foreach($remaining as $remainingItem) {
						if(phpQuery::trim(pq($remainingItem)->text())) $desc[] = phpQuery::trim(pq($remainingItem)->text());
					}
				}
			}


			if(count($desc)) {
				$data['description'] = implode("<br/>", $desc);
			}

			if(count($additional)) {
				$data['additional'] = implode("<br/>", $additional);
			}
		}
	}

	return $data;
}

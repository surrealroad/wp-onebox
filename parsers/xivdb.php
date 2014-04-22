<?php

// XIVDB Parser for OneBox

error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
ini_set('display_errors',1);

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
		$info = json_decode(file_get_contents("http://xivdb.com/modules/fpop/fpop.php?id=".$ID."&lang=".$lang."&type=".$type."&version=".$version), true);

		if(isset($info['name'])) $data['title']= $info['name'];
		if(isset($info['icon'])) $data['image']= $info['icon'];
		if(isset($info['html'])) {
			phpQuery::newDocumentHTML($info['html']);

			$desc = array();

			$onebox->data['readmore'] = false;

			$subtitle = pq(".xivdb-tooltip-content-header-data-subtitle")->text();
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
						'<strong>'.pq(".xivdb-tooltip-content-corestats-block-title", $corestat)->text().'</strong>',
						'<strong>'.pq(".xivdb-tooltip-content-corestats-block-number", $corestat)->text().'</strong>',
						'',
						'',
					);
				}
			}

			$substats = pq(".xivdb-tooltip-content-substats-block");
			if($substats) {
				foreach ($substats as $substat) {
					$statTable[] = array(
						pq(".xivdb-tooltip-content-substats-block-title", $substat)->text(),
						pq(".xivdb-tooltip-content-substats-block-number", $substat)->text(),
						pq(".xivdb-tooltip-content-substats-block-number2", $substat)->text(),
						pq(".xivdb-tooltip-content-substats-block-number3", $substat)->text(),
					);
				}
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

			if(count($desc)) {
				$data['description'] = implode("<br/>", $desc);
			}

			$stats = pq(".xivdb-tooltip-content-statsbox, .xivdb-tooltip-content-statsbox2");
			if($stats) {
				$labels = pq(".xivdb-tooltip-content-label", $stats);
				if($labels) {
					foreach($labels as $label) {
						$additional[] = pq($label)->text();
					}
				}
				pq(".xivdb-tooltip-content-label", $stats)->parent()->remove();
				$titles = pq(".xivdb-tooltip-content-title:first", $stats)->parent()->parent()->children();
				if($titles) {
					foreach($titles as $title) {
						$additional[] = pq($title)->text();
					}
				}
				$icons = pq("img", $stats);
				if($icons) {
					foreach($icons as $icon) {
						$additional[] = pq('<div>')->append(pq($icon)->clone())->html() . pq($icon)->parent()->text();
					}
				}
			}

			if(count($additional)) {
				$data['additional'] = implode("<br/>", $additional);
			}
		}
	}

	/*phpQuery::newDocument($onebox->getHTML());

	$data['favicon']=pq("link[rel=apple-touch-icon-precomposed]:first")->attr("href");
	$data['image']=pq("meta[property=og:image]")->attr("content");

	$title = pq("#title")->text();
	if($title) $data['title'] = $title;

	$desc = pq(".short_blurb")->text();
	if($desc) {
		//if(strlen($desc)>300) $desc=substr($desc,0,300);
		$data['description'] = $desc;
	}

	$additional = array();
	$target = pq("#stats .money:last")->text();
	if($target) $additional[]= __('Target: ', "onebox").$target;
	$current = pq("#pledged")->text();
	if($current) $additional[]= __('Raised: ', "onebox").$current;
	$time = pq("#stats .poll")->text();
	if($current) $additional[]= $time;

	$footer = array();
	$statusEl = pq(".NS_projects__funding_bar p");
	$status = $statusEl->find("b")->remove()->text();
	$statusText = $statusEl->text();
	if($status) $footer[]= '<strong>'.$status.'</strong>';
	if($statusText) $footer[]= $statusText;

	$titlebutton = array();
	$updateCount = pq("#updates_nav span.count")->text();
	if($updateCount) $titlebutton[]='<a href="'.$baseURL.'/posts" title="'.$updateCount.' '.__('updates', "onebox").'"><i class="onebox-icon onebox-note-icon"></i> '.$updateCount.'</a>';
	$backerCount = pq("#backers_nav span.count")->text();
	if($backerCount) $titlebutton[]='<a href="'.$baseURL.'/backers" title="'.$backerCount.' '.__('backers', "onebox").'"><i class="onebox-icon onebox-thumbsup-icon"></i> '.$backerCount.'</a>';
	$commentsCount = pq("#comments_nav span.count")->text();
	if($commentsCount) $titlebutton[]='<a href="'.$baseURL.'/comments" title="'.$commentsCount.' '.__('comments', "onebox").'"><i class="onebox-icon onebox-comment-icon"></i> '.$commentsCount.'</a>';

	if(count($additional)) {
		$data['additional'] = implode("<br/>", $additional);
	}
	if(count($footer)) {
		$data['footer'] = implode(" &middot; ", $footer);
	}
	if(count($titlebutton)) {
		$data['titlebutton'] = implode(" ", $titlebutton);
	}*/

	return $data;
}

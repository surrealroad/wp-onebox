<?php

// Github Parser for OneBox

if(isset($onebox)) {

	$match = preg_match('/^https?:\/\/(?:www\.)?github\.com/', $onebox->data['url']);

	if($match) {
		$onebox->addClass("onebox-github");
		$data = get_github_data($onebox->data['url']);
		$onebox->update($data);
	}
}


function get_github_data($url) {

	$data=array();

	preg_match('#\/\/[-a-zA-Z0-9_.]+\/([-a-zA-Z0-9_]+)/([-a-zA-Z0-9_]+)#', $url, $regex);

	if(isset($regex[1]) && isset($regex[2])) {
		$vendor = $regex[1];
		$repoName = $regex[2];
		$repo = $vendor."/".$repoName;
		$repoURL = "https://github.com/".$repo;

		$options  = array('http' => array('user_agent' => 'Surreal Road Onebox'));
		$context  = stream_context_create($options);

		$infoURL = "https://api.github.com/repos/".$repo;
		$perpage = 100;
		$commitsURL = $infoURL."/commits?per_page=".$perpage;
		if(get_option('onebox_github_apikey')) {
			$infoURL .= "?access_token=".get_option('onebox_github_apikey');
			$commitsURL .= "&access_token=".get_option('onebox_github_apikey');
		}
		@$info = json_decode(file_get_contents($infoURL, false, $context), true);
		$commits = array();
		$commitCount = -1;
		$lastsha ="";

		while ($commitCount<0 || (count($commits)-$commitCount)==$perpage) {
			$commitCount = count($commits);
			@$json = json_decode(file_get_contents($commitsURL."&last_sha=".$lastsha, false, $context), true);
			if(!$json) break;
			$commits = array_merge($commits, $json);
			$lastsha = $commits[count($commits)-1]['sha'];
		}

		//print_r($commits[0]['commit']);

		if(isset($info['full_name'])) $data['title']= $info['full_name'];
		if(isset($info['description'])) $data['description']= $info['description'];

		$additional = array();
		if(isset($commits[0]['commit']['message'])) $additional[]= __('Latest commit: ', "onebox").$commits[0]['commit']['message'];

		$footer = array();
		if(count($commits)) $footer[]= '<strong>'.count($commits).'</strong> commits';
		if(isset($commits[0]['commit']['committer']['date'])) $footer[]= __('Latest commit: ', "onebox").'<strong>'.date('F jS Y', strtotime($commits[0]['commit']['committer']['date'])).'</strong> ';

		$titlebutton = array();
		if(isset($info['watchers_count'])) $titlebutton[]='<a href="'.$repoURL.'/watchers" title="'.__('See watchers', "onebox").'"><i class="onebox-icon onebox-eye-icon"></i> '.$info['watchers_count'].'</a>';
		if(isset($info['stargazers_count'])) $titlebutton[]='<a href="'.$repoURL.'/stargazers" title="'.__('See stargazers', "onebox").'"><i class="onebox-icon onebox-star-icon"></i> '.$info['stargazers_count'].'</a>';
		if(isset($info['forks_count'])) $titlebutton[]='<a href="'.$repoURL.'/network/members" title="'.__('See forkers', "onebox").'"><i class="onebox-icon onebox-fork-icon"></i> '.$info['forks_count'].'</a>';

		if(isset($info['archive_url'])) $data['footerbutton']= '<a href="'.$repoURL.'/zipball/'.$info['default_branch'].'" title="'.__('Get an archive of this repository', "onebox").'">'.__('Download as zip', "onebox").'</a>';

		if(count($additional)) {
			$data['additional'] = implode("<br/>", $additional);
		}
		if(count($footer)) {
			$data['footer'] = implode(" &middot; ", $footer);
		}
		if(count($titlebutton)) {
			$data['titlebutton'] = implode(" ", $titlebutton);
		}
	}
	return $data;
}

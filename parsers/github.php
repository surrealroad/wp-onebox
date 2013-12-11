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
		@$info = json_decode(file_get_contents("https://api.github.com/repos/".$repo, false, $context), true);
		@$commits = json_decode(file_get_contents("https://api.github.com/repos/".$repo."/commits", false, $context), true);

		//print_r($commits[0]['commit']);

		if(isset($info['full_name'])) $data['title']= $info['full_name'];
		if(isset($info['description'])) $data['description']= $info['description'];

		$additional = array();

		$footer = array();
		if(count($commits)) $footer[]= count($commits). ' commits';
		if(isset($commits[0]['commit']['committer']['date'])) $footer[]= 'Latest commit: <strong>'.date('F jS Y', strtotime($commits[0]['commit']['committer']['date'])).'</strong> ';

		$titlebutton = array();
		if(isset($info['watchers_count'])) $titlebutton[]='<a href="'.$repoURL.'/watchers" title="See watchers">'.$info['watchers_count'].'</a>';
		if(isset($info['stargazers_count'])) $titlebutton[]='<a href="'.$repoURL.'/stargazers" title="See stargazers">'.$info['stargazers_count'].'</a>';
		if(isset($info['forks_count'])) $titlebutton[]='<a href="'.$repoURL.'/netowrk/members" title="See forkers">'.$info['forks_count'].'</a>';

		if(isset($info['archive_url'])) $data['footerbutton']= '<a href="'.$repoURL.'/zipball/master" title="Get an archive of this repository">Download as zip</a>';

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

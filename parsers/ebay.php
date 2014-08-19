<?php

// eBay Parser for OneBox

if(isset($onebox)) {

	$match = preg_match('/^https?:\/\/(?:www\.)?ebay\.*/', $onebox->data['url']);

	if($match) {
		$onebox->addClass("onebox-ebay");
		$onebox->shouldCacheLocation = false;  //for now
		$data = get_ebay_data($onebox);
		$onebox->update($data);
	}
}


function get_ebay_data($onebox) {

	$url = $onebox->data['url'];

	$data=array();

	$data['favicon']='http://ebay.com/apple-touch-icon.png';
	$data['sitename'] = "eBay";

	preg_match('/(?!.*\/)\d+/', $url, $regex);
	@$ID = $regex[0];

	if($ID) {
		$data['displayurl'] = 'http://rover.ebay.com/rover/1/711-53200-19255-0/1?icep_ff3=2&pub=5575083783&toolid=10001&campid=5337473281&customid=&icep_item='.$ID.'&ipn=psmain&icep_vectorid=229466&kwid=902099&mtid=824&kw=lg';
		$impressionImg = '<img style="text-decoration:none;border:0;padding:0;margin:0;" src="http://rover.ebay.com/roverimp/1/711-53200-19255-0/1?ff3=2&pub=5575083783&toolid=10001&campid=5337473281&customid=&item='.$ID.'&mpt=[CACHEBUSTER]"/>';

		if($onebox->affiliateLinks && $data['displayurl']) $displayurl = $data['displayurl'];
		else $displayurl = $url;

		$info = json_decode($onebox->getSource('http://open.api.ebay.com/shopping?callname=GetSingleItem&responseencoding=JSON&appid=SurrealR-b1bf-47ab-973c-1768082ac2c8&siteid=0&version=515&ItemID='.$ID.'&IncludeSelector=Description,ItemSpecifics'), true);

		$additional = array();
		$footer = array();
		$titlebutton = array();

		if(isset($info['Ack'])) {
			if($info['Ack'] == "Success") {
				if(isset($info['Item']['Title'])) $data['title'] = $info['Item']['Title'];
				if(isset($info['Item']['Description'])) $data['description'] = substr(strip_tags($info['Item']['Description']), 0, 300).$impressionImg;
				if(isset($info['Item']['GalleryURL'])) $data['image'] = $info['Item']['GalleryURL'];

				if(isset($info['Item']['PrimaryCategoryName'])) $additional[] = __('Category: ', "onebox").$info['Item']['PrimaryCategoryName'];
				if(isset($info['Item']['Location'])) $additional[] = __('Location: ', "onebox").$info['Item']['Location'];

				if(isset($info['Item']['ItemSpecifics']['NameValueList'])) {
					foreach($info['Item']['ItemSpecifics']['NameValueList'] as $itemSpecific) {
						$additional[] = $itemSpecific['Name'].": ".implode(", ", $itemSpecific['Value']);
					}
				}

				if(isset($info['Item']['BidCount']) && !$info['Item']['AutoPay']) $footer[] = '<strong>'.$info['Item']['BidCount'].'</strong> '.__('bids', "onebox");
				elseif(isset($info['Item']['QuantityAvailableHint'])) {
					if($info['Item']['QuantityAvailableHint'] == "Limited") $footer[] = '<strong>'.__('Limited quantity available', "onebox").'</strong>';
					elseif($info['Item']['QuantityAvailableHint'] == "MoreThan") $footer[] = '<strong>'.__('More than 10 available', "onebox").'</strong>';
				}

				if(isset($info['Item']['ListingStatus']) && $info['Item']['ListingStatus']!="Active") {
					if(isset($info['Item']['EndTime'])) $footer[]= $info['Item']['ListingStatus'].': <strong>'.$onebox->oneboxdate(strtotime($info['Item']['EndTime'])).'</strong>';
					else $footer[] = '<strong>'.$info['Item']['ListingStatus'].'</strong>';
				} else {
					if(isset($info['Item']['ConvertedCurrentPrice'])) $data['footerbutton']= '<a href="'.$displayurl.'">'.$info['Item']['ConvertedCurrentPrice']['CurrencyID'].' '.number_format($info['Item']['ConvertedCurrentPrice']['Value'], 2).'</a>';
					if(isset($info['Item']['EndTime'])) $footer[]= __('Ends: ', "onebox").'<strong>'.$onebox->oneboxdate(strtotime($info['Item']['EndTime'])).'</strong>';
				}

			} elseif(isset($info['Errors'])) {
				if(isset($info['Errors'][0]['ShortMessage'])) $data['title'] = $info['Errors'][0]['ShortMessage'];
				if(isset($info['Errors'][0]['LongMessage'])) $data['description'] = $info['Errors'][0]['LongMessage'];
			}
		}

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

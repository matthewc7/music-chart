<?php

class Manager_Controller extends Base_Controller {

	public $restful = true;



	/**
	 * Process data into tracks, albms and links for storage purposes. 
	 * @return void. 
	 */
	function process_data()
	{
		$new_albums = DB::query("SELECT DISTINCT feeds.album_id AS id, feeds.album_name AS name, feeds.artist_id, feeds.image, feeds.thumbnail FROM feeds WHERE feeds.album_id NOT IN (SELECT albums.id FROM albums) GROUP BY feeds.album_id ORDER BY feeds.id");
		$new_tracks = DB::query("SELECT DISTINCT feeds.track_id AS id, feeds.track_name AS track_name, feeds.track_artist AS track_artist, feeds.artist_id, feeds.link  FROM feeds WHERE feeds.track_id NOT IN (SELECT tracks.id FROM tracks) GROUP BY feeds.track_id ORDER BY feeds.id");

		if(COUNT($new_albums) > 0)
		{
			foreach ($new_albums as $album)
			{
				$albums = new Albums;
				$albums->id = $album->id;
				$albums->name = $album->name;
				$albums->artist_id = $album->artist_id;
				$albums->image = $album->image;
				$albums->thumbnail = $album->thumbnail;
				$albums->save();
			}
		}

		
		if(COUNT($new_tracks) > 0)
		{
			foreach ($new_tracks as $track)
			{
				$tracks = new Tracks;
				$tracks->id = $track->id;
				$tracks->name = $track->track_name;
				$tracks->artist_id = $track->artist_id;
				$artists->name = $artist->track_artist;
				$tracks->link = $track->link;
				$tracks->save();
			}
		}

	}	





	/**
	 * Get the feed from iTunes.
	 * @return void.
	 */
	function get_feed()
	{
		Logs::write('feed', 'info','Getting the feed data from iTunes.');
		$stores_count = 0;

		DB::query("TRUNCATE TABLE feeds");
		Logs::write('feed', 'info','Truncate feeds table.');
		$stores = Stores::all();
		Logs::write('feed', 'info','Select country list from stores table.');

		foreach ($stores as $store)
		{
			Logs::write('feed', 'info','Extract feed for '.$store->iso.'.');
			
		    $type = "topsongs";
			$url = "http://itunes.apple.com/". Str::lower($store->iso) ."/rss/". $type. "/limit=". Config::get('settings.feed_limit') ."/json";
			Logs::write('feed', 'info','Get feed using URL - '.$url.'.');	

			$data = XFile::curl_content($url);

			if(count($data) > 0)
			{
				$music_type = ($type == "topsongs")? "T" : "A";
				Logs::write('feed', 'info','Received data from feed. Begin parser.');
				$this->parse_data($store->iso, $music_type, $data);
			}
			else
			{
				Logs::write('feed', 'error','No data from feed.');
			}

			$stores_count++;
		} 

		Logs::write('feed', 'info','Completed the feed entry for '.$stores_count.' stores.');
		return $stores_count;
	}


	/**
	 * Parse the feed from iTunes.
	 * @param  text 	$iso  [country ISO]
	 * @param  text 	$type [feed Type]
	 * @param  array 	$data [feed]
	 * @return void.
	 */
	function parse_data($iso, $type, $data)
	{
		Logs::write('parser', 'info','Parser began for the feed from iTunes '.$iso.' for '.$type.' type.');
		$position = 1;

		foreach($data['feed']['entry'] as $entry)
		{
			$id = $this->trim_albumtrack_id($entry['link'][0]['attributes']['href']);

			/* Pull new data from feed */
			$feeds = new Feeds;
			$feeds->iso = $iso;
			$feeds->type = $type;
			$feeds->position = $position;

			/* Update data from feed */
			//$feeds = Feeds::where('position', '=', $position)->where('iso', '=', $iso)->where('type', '=', $music_type)->first();

			$feeds->datetime = Config::get('settings.datetime');

			$feeds->track_id = $id['track'];
			$feeds->track_name = $entry['im:name']['label'];
			
			$track_artist = $entry['im:artist']['label'];
			$feeds->track_artist = $this->validate($track_artist);

			if(Format::check($feeds->track_artist, "Various Artists"))
			{
				$feeds->artist_id = 0;
			}
			else
			{
				$feeds->artist_id = $this->trim_artist_id($entry['im:artist']['attributes']['href']);
			}

			if($feeds->artist_id  == 0)
			{
				$feeds->album_artist = "Various Artists";
			}
			else
			{
				$artists = $this->lookup($feeds->artist_id);
				$feeds->album_artist = ($artists == '')? $artists : $feeds->track_artist;
			}

			$feeds->album_id = $id['album'];
			$feeds->album_name = $entry['im:collection']['im:name']['label'];
			$feeds->thumbnail = $entry['im:image'][1]['label'];
			$feeds->image = $entry['im:image'][2]['label'];

			switch($type)
			{
				case 'T':
					$link = Format::replace($entry['im:collection']['link']['attributes']['href'], '/'.Str::lower($iso).'/' , "/{iso}/");
					$feeds->link = Format::trim($link, strpos($link, '?'));

					break;
				case 'A':
					$link = Format::replace($entry['link']['attributes']['href'], '/'.Str::lower($iso).'/' , "/{iso}/");
					$feeds->link = Format::trim($link, strpos($link, '?'));
					break;
			}

			$feeds->save();
			$position++;
		}

		Logs::write('parser', 'info','Parser completed for the feed from iTunes '.$iso.' for '.$type.' type.');
		
	}


	function lookup($artist_id)
	{
		$url = "https://itunes.apple.com/lookup?id=".$artist_id;
		$data = XFile::curl_content($url);

		foreach($data['results'] as $result)
		{
			return $result['artistName'];
		}
	}


	/**
	 * Trim the string for album ID and track ID
	 * @param  string $string [hyperlink containing album ID and track ID]
	 * @return array         	[array containing album ID and track ID]
	 */
	function trim_albumtrack_id($string)
	{
		$pos1 = strpos($string, '/id');

		if(strpos($string, '/id', $pos1 + 1) !== FALSE)
		{
			$pos1 = strpos($string, '/id', $pos1 + 1);
		}

		$pos2 = strlen($string) - strpos($string, '?i=');
		$id['album'] = substr($string, $pos1 + 3, -$pos2);

		$pos3 = strpos($string, '?i=');
		$id['track'] = substr($string, $pos3 + 3, -5);

		return $id;
	}



	/**
	 * Trim the string for artist ID
	 * @param  string $string [hyperlink containing artist ID]
	 * @return string         [artist ID]
	 */
	function trim_artist_id($string)
	{
		$pos1 = strpos($string, '/id');

		if(strpos($string, '/id', $pos1 + 1) !== FALSE)
		{
			$pos1 = strpos($string, '/id', $pos1 + 1);
		}

		$pos2 = strlen($string) - strpos($string, '?');
		return substr($string, $pos1 + 3, -$pos2);

	}



	function validate($artists)
	{
		if(Format::check($artists, ','))
		{
			$array = explode(",", $artists);

			if(COUNT($array) > 4)
			{
				return "Various Artists";
			}
		}
		
		return $artists;
	}





}
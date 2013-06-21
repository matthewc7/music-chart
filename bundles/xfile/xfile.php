<?php

class XFile {


	/**
	 * Get content from URL.
	 * @param  string $file 	[file path]
	 * @param  string $url   	[the URL link]
	 * @return void.
	 */
	public static function content($file, $url)
	{
		file_put_contents($file, file_get_contents($url));
	}



	/**
	 * Get content from URL using CURL.
	 * @param  string $url   	[the URL link]
	 * @return void.
	 */
	public static function curl_content($url)
	{
		$channel = curl_init($url);
		curl_setopt($channel, CURLOPT_RETURNTRANSFER, TRUE);
		$data = json_decode(curl_exec($channel), TRUE);
		curl_close($channel);

		return $data;
	}



	/**
	 * Scan a directory and extract the file names from the directory. 
	 * @param  string $dir 	[directory file path]
	 * @param  array  $ext 	[file extension to search for certain files]
	 * @return array      	[array containing filenames]
	 */
	public static function scandir($dir, $ext = array())
	{
		$files = array_diff(scandir($dir), array('..', '.'));

		if(COUNT($ext) > 0)
		{
			$files_ext = array();

			foreach ($files as $file) 
			{
				for ($i = 0; $i < COUNT($ext); $i++)
				{
					if(File::is($ext[$i], $dir.$file))
					{
						array_push($files_ext, $file);
					}
				}
			}	
			return $files_ext;
		}

		return $files;

	}




}
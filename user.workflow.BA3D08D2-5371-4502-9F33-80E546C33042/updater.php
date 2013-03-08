<?php

	include( 'extension_utils.php' );
	
	$d = posix_getpwuid( posix_getuid() );
	$cache = $d['dir'] . "/Library/Caches/com.runningwithcrayons.Alfred-2/Workflow Data/co.uk.tom-hunt.alfpt/";
	
	function checkforbundleid( $bid, $dir )
	{
		$dirhnd = opendir( $dir );
		while( $d = readdir( $dirhnd ) )
		{
			if( substr( $d, 0, 1 ) == '.' ) continue;
			
			// check that a workflow actually exists in the dir
			$plist = realpath( "../$d/info.plist" );
			if( file_exists( $plist ) )
			{				
				$fs = addslashes( $plist );					
				$xml = simplexml_load_file( $fs );

				// get bundleid from plist				
				$bundleid = $xml->dict->string[0];				
				
				if( preg_match( "/$bid/", $bundleid ) )
					{ return TRUE; }
			}
			
		}
		closedir( $dirhnd );		
		return FALSE;
	}

	function get_data( $url ) {
		$ch = curl_init();
		$timeout = 5;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}

	function update_icons()
	{
		$utils = new ExtensionUtils();

		date_default_timezone_set( 'GMT' );

		$list = json_decode( get_data( "http://alfredrepo.tom-hunt.co.uk/repo/list.php" ), true );
		
		foreach( $list as $l )
		{
			$url = "http://alfredrepo.tom-hunt.co.uk/wficons/$l[0].png";			
			$dst = "_icons/$l[0].png";
			//$dst = $cache . "/icons/$l[0].png";
			
			$ok = FALSE;			
			
			if( !file_exists( $dst ) )
			{
				$ok = TRUE;				
			}
			else
			{
				$loc_lm = $loc_lm = filemtime( $dst );
				if( $l[1] > $loc_lm )
					{ $ok = TRUE; }
			}

			if( $ok )				
			{
				$headers = get_headers( $url );
				if( $headers[0] == 'HTTP/1.1 200 OK' )
					{ file_put_contents( $dst, file_get_contents( $url ) ); }
			}
			
		}	
	}

	function list_installed( $dir, $concat = FALSE )
	{
		
		$dirhnd = opendir( realpath( $dir ) );
		
		$list = array();
		$locallist = array();
		$retarray = array();

		while( $d = readdir( $dirhnd ) )
		{
			if( substr( $d, 0, 1 ) == '.' ) continue;	

			// check that a workflow actually exists in the dir
			$plist = realpath( "../$d/info.plist" );
			if( file_exists( $plist ) )
			{
				$fs = addslashes( $plist );					
				$xml = simplexml_load_file( $fs );

				// get name and bundleid from plist
				// a better solution than hardcoding the indices is needed here
				$name = $xml->dict->string[3];
				$bundleid = $xml->dict->string[0];
				if( $bundleid != '' )
					{ array_push( $list, "'$bundleid'" ); }

				// we'll store the workflow dir name here for later
				$locallist["$bundleid"] = $d;				
			}
		}
		closedir( $dirhnd );
		
		array_push( $retarray, $list );
		array_push( $retarray, $locallist );
		
		if( $concat ) return implode( ',', $retarray[0] );
		else return $retarray;
	}

	function flags_from_query( $q )
	{
		$ql = preg_split( '/\-(.)\ /', $q, 0, PREG_SPLIT_DELIM_CAPTURE );				
		$fa = array( 'a' => '', 'b' => '', 'p' => '' );
		$f = '';
		array_shift( $ql );
		for( $i = 0, $s = sizeof( $ql ); $i < $s; $i += 2 )
		{
			$fa[ $ql[$i] ] = trim( $ql[$i + 1 ] ) ;
		}
		foreach ( $fa as $i=>$v ) {
			$f .= "$i=$v&";
		}

		return $f;			
	}

	function term_from_query( $q )
	{
		$ql = preg_split( '/\-./', $q );		
		return trim( $ql[0] );
	}

	/////////////////////////////////////////////////////////////////////////////////


	function ext_list( $force = FALSE )
	{		
		
		update_icons();

		$utils = new ExtensionUtils();	

		date_default_timezone_set( 'GMT' );
		$retarray = array();
		
		$installed = list_installed( '../' );

		$list = $installed[0];
		$locallist = $installed[1];

		$get = implode( ',', $list );
		$list = json_decode( get_data( "http://alfredrepo.tom-hunt.co.uk/repo/check.php?bids=$get" ), true );
		
		foreach( $list as $l )
		{
			
			$name = $l['workflow_name'];
			$creator = $l['workflow_creator'];
			$url = $l['workflow_url'];
			$bundleid = $l['workflow_bundleid'];			

			// check local modified date
			$loc_lm = filemtime( realpath( "../$locallist[$bundleid]" ) );

			// check url modified date
			$rem_lm = get_headers($url);
			$rem_lm = str_replace( 'Last-Modified: ', '', $rem_lm[3] );
			$rem_lm = strtotime( $rem_lm );

			// list the workflow if...
			$ok = FALSE;
			if( $force == TRUE ) $ok = TRUE;				
			if( $force == FALSE && ( $rem_lm > $loc_lm ) ) $ok = TRUE;				

			if( $ok )
			{
				$item = array(
					'uid' => 'alfpt_' . $bundleid,					
					'arg' => $url . '||||' . $bundleid,
					'title' => "Update $name",
					'subtitle' => "$url",
					'icon' => realpath( "../$locallist[$bundleid]/icon.png" ),
					'valid' => 'yes'
				);
				array_push( $retarray, $item );
			}

		}

		// error listing
		if( sizeof( $retarray ) < 1 )
		{
			$item = array(
				'uid' => "alfpt_error",
				'arg' => 'error',
				'title' => 'No workflows need updating',
				'subtitle' => "Use alfpt update -f to force an update",
				'icon' => 'icon.png',
				'valid' => 'no'
			);
			array_push( $retarray, $item );
		}

		echo $utils->arrayToXml( $retarray );
		
	}			


	function ext_update( $q )
	{		

		$q_split = explode( '||||', $q );		
		$url = addslashes( $q_split[0] );
		$bid = $q_split[1];		
		$extdir;
		$name;

		// find the directory of the workflow using bundleid as an identifier		
		$dirhnd = opendir( realpath( '../' ) );
		while( $d = readdir( $dirhnd ) )
		{
			$plist = realpath( "../$d/info.plist" );			
			if( file_exists( $plist ) )
			{								
				$xml = simplexml_load_file( $plist );								
				if( $xml->dict->string[0] == $bid )
				{
					$extdir = realpath( "../$d" );
					$name = $xml->dict->string[3];
					break;
				}
			}
		}
		closedir( $dirhnd );

		// if we've found a directory that matches the bundleid
		if( isset( $extdir ) )
		{
			$file = $bid;
			$tmpdir = "/tmp/$file";
			$bn = basename( $url );

			if( !is_dir( $tmpdir ) )
				mkdir( $tmpdir );			

			// download and extract workflow
			$wgetcmd = "cd \"$tmpdir\"; curl $url -o \"/tmp/$bn\"; cat \"/tmp/$bn\" | cpio -idmv; rm \"/tmp/$bn\"";		
			shell_exec( $wgetcmd );
			
			// before we start updating we'll make a copy of the current workflow so we can restore in the case of an error
			$dbn = basename( $extdir );
			shell_exec( "cp -R \"$extdir\" \"/tmp/$dbn\"" );

			// copy files to workflow folder
			$errored = array();
			$moved = array();
			$dirhnd = opendir( $tmpdir );

			while( $f = readdir( $dirhnd ) )
			{				
				if( preg_match( '/^[\.\_]/', substr( $f, 0, 1 ) ) ) continue;					
				try {
					copy( "$tmpdir/$f", "$extdir/$f" );					
				} catch( Exception $e ){
					array_push( $errored, $f );
					break;
				}
				array_push( $moved, $f );
			}
			closedir( $dirhnd );

			// output
			if( sizeof( $errored ) > 0 || sizeof( $moved ) < 1 )
			{				
				shell_exec( "mv \"/tmp/$dbs\" \"$extdir\"" );
				echo "Error moving files. Old $name workflow has been restored.";
			} else {				
				shell_exec( "touch \"$extdir\"" );
				echo "$bid successfully updated.";				
				shell_exec( "rm -R \"/tmp/$dbn\"; rm -R \"$tmpdir\"" );
			}			
		}
	}


	function ext_install( $q )
	{
		
		$url = $q;
		$bn = rawurldecode( basename( $q ) );
		$tmpdir = str_replace( '.alfredworkflow', '', $bn );
		$tmpdir = "/tmp/$tmpdir";

		if( !is_dir( $tmpdir ) )
			mkdir( $tmpdir );

		// download and extract workflow
		$wgetcmd = "cd \"$tmpdir\"; curl $url -o \"/tmp/$bn\"; cat \"/tmp/$bn\" | cpio -idmv;";		
		shell_exec( $wgetcmd );


		// get the bundleid of the newly downloaded workflow
		$plist = "$tmpdir/info.plist";
		$bid;
		if( file_exists( $plist ) )
		{
			$xml = simplexml_load_file( $plist );
			$bid = $xml->dict->string[0];			

			if( checkforbundleid( $bid, realpath( '../' ) ) )
			{
				echo "$bn is already installed. Please use 'alfpt update' or 'alfpt update -f'";
				shell_exec( "rm \"/tmp/$bn\"" );
			} else {
				echo "installing $bn...";
				shell_exec( "open \"/tmp/$bn\";");// rm \"/tmp/$bn\"" );				
			}
		}		
	}	

?>
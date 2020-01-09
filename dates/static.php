<?php
defined( '_JEXEC' ) or die;

/**
 * A helper class for various date functions
 *
 * @package default
 * @author Ted Lowe
 */
class LibcsDatesStatic
{
	public static function getDateNewYear( $yeardiff = 0, $curdate = "" )
	{
		if ( empty( $curdate ) )
			$now = getdate();
		else
			$now = getdate(self::getUnixTimestampFromMysql($curdate));

		return sprintf( "%04d-%02d-%02d",
			$now["year"]+$yeardiff,
			$now["mon"],
			$now["mday"] );
	}
	public static function getUnixTimestampFromMysql( $date )
	{
		// handle both yyyy-mm-dd and yyyy-mm-dd hh:mm:ss input formats
		$b = explode( ' ', $date );
		$a = explode( '-', $b[0] );
		if ( count( $b ) > 1 )
		{
			// has time component
			$c = explode( ':', $b[1] );
			return mktime( $c[0],$c[1],$c[2],$a[1],$a[2],$a[0]);
		}

		// no time component
		return mktime( 0,0,0,$a[1],$a[2],$a[0]);
	}
	public static function getDaysApart( $t1, $t2 )
	{
		// compare two timestamps and return # of days apart
		// + means t1 is in the future of t2
	
		return floor(($t1-$t2)/86400);
	}
	public static function getTimeStampNow( $unixts = "" )	// mysql type datetime (iso)
	{
		// outputs: 2006-07-05 09:40:04 (yyyy-mm-dd hh:mm:ss)
		if ( empty( $unixts ) )
			$unixts = time();
		return date('Y-m-d H:i:s', $unixts );
	}
	public static function getTimeStampNowHuman()
	{
		// November 6, 1927 3:12am
		return date( "F j, Y g:ia", time() );
	}
	function __construct()
	{
	}
}

<?php
defined( '_JEXEC' ) or die;

/**
 * A helper class for displaying data in a simple table
 *
 * @package default
 * @author Ted Lowe
 */
class LibcsTableShowresults
{
	function __construct()
	{
	}
	public static function showResultsNoCounts( $rows, $color_dark, $color_light, $caption = "", $width = "300" ) //{{{1
	{
		if ( ! empty( $caption ) )
			echo "<p style='font-weight: bold;'>$caption</p>";
	
		$tableAttrs = array('border' => '5', 'bordercolor' => $color_dark, 'width' => "$width", 'cellpadding' => '2', 'cellspacing' => '1' );
	
		require_once 'HTML/Table.php';
	
		$table = new HTML_Table($tableAttrs);
		$table->setAutoGrow(true);
		$table->setAutoFill('&nbsp');
		//if ( ! empty( $caption ) )
		//$table->setCaption( $caption );
	
		$flds = array_keys($rows[0]);
		$nflds = count($flds);
		$nrows = count($rows );
	
		// header column labels
		for ( $c = 0 ; $c < $nflds ; $c++ )
			$table->setHeaderContents(0, $c, $flds[$c]);
	
	
		// data
		for ( $r = 0 ; $r < $nrows ; $r++ )
			for ( $c = 0 ; $c < $nflds ; $c++ )
				$table->setCellContents($r+1, $c, $rows[$r][$flds[$c]]);
	
			$altRow = array('bgcolor' => $color_light );
			$table->altRowAttributes(0, null, $altRow,TRUE);
			$hrAttrs = array('bgcolor' => $color_dark );
			$table->setRowAttributes(0, $hrAttrs, true);
	
			echo $table->toHtml();
	}
	public static function showResults( $rows, $color_dark, $color_light, $caption = "" )
	{
		if ( ! empty( $caption ) )
			echo "<p style='font-weight: bold;'>$caption</p>";
	
		$tableAttrs = array('border' => '5', 'bordercolor' => $color_dark, 'width' => '300', 'cellpadding' => '2', 'cellspacing' => '1' );
	
		require_once 'HTML/Table.php';
	
		$table = new HTML_Table($tableAttrs);
		$table->setAutoGrow(true);
		$table->setAutoFill('&nbsp');
		//if ( ! empty( $caption ) )
		//$table->setCaption( $caption );
	
	
		$flds = array_keys($rows[0]);
		$nflds = count($flds);
		$nrows = count($rows );
	
		// header column labels
		for ( $c = 0 ; $c < $nflds ; $c++ )
			$table->setHeaderContents(0, $c, $flds[$c]);
	
		// data
		for ( $r = 0 ; $r < $nrows ; $r++ )
			for ( $c = 0 ; $c < $nflds ; $c++ )
				$table->setCellContents($r+1, $c, $rows[$r][$flds[$c]]);
	
			$altRow = array('bgcolor' => $color_light );
			$table->altRowAttributes(0, null, $altRow,TRUE);
			$hrAttrs = array('bgcolor' => $color_dark );
			$table->setRowAttributes(0, $hrAttrs, true);
			//$table->setColAttributes(0, $hrAttrs);
	
			echo $table->toHtml();
	}
}
